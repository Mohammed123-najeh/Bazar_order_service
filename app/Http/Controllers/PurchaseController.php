<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PurchaseController
{
    private $catalogServiceUrl;

    public function __construct()
    {
        // Before replication: just talk to the catalog service directly
        $this->catalogServiceUrl = env('CATALOG_SERVICE_URL', 'http://catalog-service:8080');
    }

    public function purchase(Request $request, int $itemNumber): JsonResponse
    {
        try {
            $client = new Client();

            // Ask the catalog service for book info
            $infoResponse = $client->get(
                "{$this->catalogServiceUrl}/books/info/{$itemNumber}",
                ['timeout' => 10]
            );

            if ($infoResponse->getStatusCode() !== 200) {
                $this->logFailedOrder($itemNumber, "Book with ID {$itemNumber} not found in catalog");

                return response()->json([
                    'message' => "Book with ID {$itemNumber} not found in catalog"
                ], 404);
            }

            $book = json_decode($infoResponse->getBody()->getContents(), true);

            if (!$book) {
                return response()->json([
                    'message' => 'Invalid response from catalog service'
                ], 400);
            }

            $numberOfItems = $book['numberOfItems'] ?? $book['NumberOfItems'] ?? 0;
            $bookName = $book['bookName'] ?? $book['BookName'] ?? "Book ID {$itemNumber}";

            if ($numberOfItems <= 0) {
                $this->logFailedOrder(
                    $itemNumber,
                    "Book '{$bookName}' is out of stock",
                    $bookName
                );

                Log::info("Purchase failed: Book '{$bookName}' (ID: {$itemNumber}) is out of stock");

                return response()->json([
                    'message' => "Book '{$bookName}' is out of stock"
                ], 400);
            }

            // Ask catalog service to decrease stock
            $decreaseStock = ['decrease' => 1];

            $updateResponse = $client->patch(
                "{$this->catalogServiceUrl}/books/stock/{$itemNumber}",
                [
                    'json' => $decreaseStock,
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ]
            );

            if ($updateResponse->getStatusCode() !== 200) {
                $bookName = $book['bookName'] ?? $book['BookName'] ?? "Book ID {$itemNumber}";
                $this->logFailedOrder(
                    $itemNumber,
                    'Failed to update stock in catalog service',
                    $bookName
                );

                return response()->json([
                    'message' => 'Failed to update stock in catalog service'
                ], 400);
            }

            // Save order locally
            $order = new Order();
            $order->book_id = $itemNumber;
            $order->book_name = $bookName;
            $order->order_date = Carbon::now();
            $order->status = 'Completed';
            $order->save();

            Log::info("bought book {$bookName}");

            return response()->json([
                'message' => "Successfully purchased book '{$bookName}'",
                'orderId' => $order->id,
                'bookName' => $bookName,
                'orderDate' => $order->order_date
            ], 200);

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $message = $e->getResponse()->getBody()->getContents();

            $this->logFailedOrder($itemNumber, "Catalog service error: {$message}");

            return response()->json([
                'message' => "Error communicating with catalog service: {$message}"
            ], $statusCode);

        } catch (\Exception $e) {
            Log::error("Error processing purchase: {$e->getMessage()}");

            return response()->json([
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function logFailedOrder(int $itemNumber, string $reason, ?string $bookName = null): void
    {
        try {
            $order = new Order();
            $order->book_id = $itemNumber;
            $order->book_name = $bookName ?? "Book ID {$itemNumber}";
            $order->order_date = Carbon::now();
            $order->status = 'Failed';
            $order->save();
        } catch (\Exception $e) {
            Log::error("Failed to log failed order: {$e->getMessage()}");
        }
    }
}
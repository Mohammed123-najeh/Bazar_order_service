<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ReplicationService
{
    private $client;
    private $replicaUrls;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 5]);
        $replicaList = env('REPLICA_URLS', '');
        $this->replicaUrls = array_filter(
            array_map('trim', explode(',', $replicaList)),
            function($url) {
                return !empty($url);
            }
        );
    }

    public function propagateWrite($endpoint, $data)
    {
        foreach ($this->replicaUrls as $replicaUrl) {
            $this->propagateToReplica($replicaUrl, $endpoint, $data);
        }
    }

    private function propagateToReplica($replicaUrl, $endpoint, $data)
    {
        try {
            $fullUrl = rtrim($replicaUrl, '/') . $endpoint;
            
            $this->client->post($fullUrl, [
                'json' => $data,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Replication' => 'true'
                ],
                'timeout' => 5
            ]);
        } catch (\Exception $e) {
            Log::warning("Failed to replicate to {$replicaUrl}: " . $e->getMessage());
        }
    }
}


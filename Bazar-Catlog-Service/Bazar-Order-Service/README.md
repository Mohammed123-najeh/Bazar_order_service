# Bazar Order Service (PHP Lumen)

This is the Order Service microservice for the Bazar bookstore system, built with PHP Lumen. It handles purchase requests by communicating with the Catalog Service (C# .NET).

## Features

- **Purchase Endpoint**: Handles purchase requests for books
  - Queries the catalog service to check book availability
  - Decrements stock in the catalog service
  - Logs all orders (completed and failed) to a persistent SQLite database

## Requirements

- PHP 8.1+
- Composer
- SQLite
- Docker (for containerized deployment)

## API Endpoints

### POST /purchase/{itemNumber}
Purchases a book by item number.

**Request:**
```
POST http://localhost:5001/purchase/1
```

**Success Response (200 OK):**
```json
{
  "message": "Successfully purchased book 'How to get a good grade in DOS in 40 minutes a day'",
  "orderId": 1,
  "bookName": "How to get a good grade in DOS in 40 minutes a day",
  "orderDate": "2024-01-15T10:30:00"
}
```

**Error Responses:**
- `404 Not Found`: Book not found in catalog
- `400 Bad Request`: Book is out of stock or other validation errors
- `500 Internal Server Error`: Server error

## Configuration

The service is configured via environment variables or `.env` file:

```
APP_NAME=Bazar-Order-Service
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=sqlite
DB_DATABASE=/app/orders.db

CATALOG_SERVICE_URL=http://catalog-service:8080
```

## Running the Service

### Using Docker Compose (Recommended)

From the root directory (`Bazar-Catlog-Service`):
```bash
docker-compose up --build
```

This will start both the catalog service (port 5000) and order service (port 5001).

### Running Manually (Local Development)

1. Install dependencies:
```bash
composer install
```

2. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

3. Generate application key (if needed):
```bash
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start the server:
```bash
php -S localhost:8080 -t public public/index.php
```

The service will run on `http://localhost:8080`.

## Database

The service uses SQLite to persist orders. All orders (both successful and failed) are stored in the `orders` table with the following fields:
- `id`: Order ID (auto-increment)
- `book_id`: The book's item number
- `book_name`: Name of the book
- `order_date`: Timestamp of the order
- `status`: "Completed" or "Failed"

Database migrations are automatically run when the container starts.

## Testing

Example purchase request using curl:
```bash
curl -X POST http://localhost:5001/purchase/1
```

Example using PowerShell:
```powershell
Invoke-WebRequest -Uri "http://localhost:5001/purchase/1" -Method POST
```

## How It Works

1. Client sends `POST /purchase/{itemNumber}` to Order Service
2. Order Service queries Catalog Service: `GET /books/info/{itemNumber}`
3. Order Service checks if book is in stock
4. If in stock:
   - Order Service decrements stock: `PATCH /books/stock/{itemNumber}` with `{"decrease": 1}`
   - Order Service creates order record in its database
   - Order Service prints "bought book {bookName}" to console
   - Order Service returns success message
5. If out of stock or error:
   - Order Service logs failed order
   - Order Service returns error message

All orders (successful and failed) are logged for tracking purposes.

## Project Structure

```
Bazar-Order-Service/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── PurchaseController.php  # Purchase logic
│   ├── Models/
│   │   └── Order.php                    # Order model
│   └── ...
├── bootstrap/
│   └── app.php                          # Application bootstrap
├── config/
│   └── database.php                     # Database configuration
├── database/
│   └── migrations/                     # Database migrations
├── public/
│   └── index.php                        # Entry point
├── routes/
│   └── web.php                          # Route definitions
├── composer.json                         # PHP dependencies
├── Dockerfile                           # Docker configuration
└── .env                                 # Environment variables
```

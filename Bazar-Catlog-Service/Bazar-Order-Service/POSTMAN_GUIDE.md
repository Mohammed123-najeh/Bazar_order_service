# Postman Guide - Purchase Endpoint Testing

## Purchase Endpoint

### Endpoint Details
- **Method:** `POST`
- **URL:** `http://localhost:5001/purchase/{itemNumber}`
- **Description:** Purchases a book by its item number

---

## How to Use in Postman

### Step 1: Create a New Request

1. Open Postman
2. Click **New** → **HTTP Request**
3. Or click the **+** button to create a new tab

### Step 2: Configure the Request

1. **Select Method:** Choose `POST` from the dropdown (default is GET)

2. **Enter URL:**
   ```
   http://localhost:5001/purchase/1
   ```
   Replace `1` with the book ID you want to purchase (1, 2, 3, or 4)

3. **Headers:** 
   - No special headers required
   - Postman will automatically add `Content-Type` if needed

4. **Body:**
   - **Select:** None (this endpoint doesn't require a request body)
   - The item number is passed in the URL path

### Step 3: Send the Request

Click the **Send** button

---

## Example Requests

### Example 1: Purchase Book ID 1
```
POST http://localhost:5001/purchase/1
```

### Example 2: Purchase Book ID 2
```
POST http://localhost:5001/purchase/2
```

### Example 3: Purchase Book ID 3
```
POST http://localhost:5001/purchase/3
```

### Example 4: Purchase Book ID 4
```
POST http://localhost:5001/purchase/4
```

---

## Expected Responses

### ✅ Success Response (200 OK)
**When:** Book is in stock and purchase is successful

```json
{
  "message": "Successfully purchased book 'How to get a good grade in DOS in 40 minutes a day'",
  "orderId": 1,
  "bookName": "How to get a good grade in DOS in 40 minutes a day",
  "orderDate": "2024-01-15T10:30:00.000000Z"
}
```

### ❌ Book Not Found (404 Not Found)
**When:** Book ID doesn't exist in catalog

```json
{
  "message": "Book with ID 999 not found in catalog"
}
```

### ❌ Out of Stock (400 Bad Request)
**When:** Book exists but has 0 items in stock

```json
{
  "message": "Book 'How to get a good grade in DOS in 40 minutes a day' is out of stock"
}
```

### ❌ Server Error (500 Internal Server Error)
**When:** Service can't reach catalog service or other errors

```json
{
  "message": "Internal server error",
  "error": "Error details here"
}
```

---

## Testing Scenarios

### Scenario 1: Successful Purchase
1. Make sure both services are running:
   ```bash
   docker-compose up -d
   ```

2. Send request:
   ```
   POST http://localhost:5001/purchase/1
   ```

3. **Expected:** Success response with order details

### Scenario 2: Purchase Out of Stock Book
1. First, purchase all stock by sending multiple requests:
   ```
   POST http://localhost:5001/purchase/1
   ```
   (Repeat until stock is 0)

2. Send another purchase request:
   ```
   POST http://localhost:5001/purchase/1
   ```

3. **Expected:** "Book is out of stock" error

### Scenario 3: Invalid Book ID
1. Send request with non-existent ID:
   ```
   POST http://localhost:5001/purchase/999
   ```

2. **Expected:** "Book not found" error

---

## Postman Collection Setup (Optional)

### Create a Collection

1. Click **New** → **Collection**
2. Name it: `Bazar Order Service`
3. Add the purchase request to the collection

### Environment Variables

Create an environment for easy testing:

1. Click **Environments** → **+** (Create)
2. Name: `Local Development`
3. Add variables:
   - `base_url`: `http://localhost:5001`
   - `book_id`: `1`
4. Use in URL: `{{base_url}}/purchase/{{book_id}}`

---

## Quick Test Checklist

- [ ] Order service is running (port 5001)
- [ ] Catalog service is running (port 5000)
- [ ] Method is set to **POST**
- [ ] URL includes the book ID (1-4)
- [ ] No request body needed
- [ ] Click **Send**

---

## Troubleshooting

### "Could not get any response"
- **Check:** Is the order service running?
- **Solution:** Run `docker-compose up -d` or `docker ps` to verify

### "Connection refused"
- **Check:** Is the service running on port 5001?
- **Solution:** Check `docker logs order-service` for errors

### "Book not found"
- **Check:** Is the catalog service running and accessible?
- **Solution:** Test catalog service first: `GET http://localhost:5000/books/info/1`

### "Service can't reach catalog service"
- **Check:** Are both services on the same Docker network?
- **Solution:** Use `docker-compose up` to ensure proper networking

---

## Additional Endpoints to Test

### Check Order Service Status
```
GET http://localhost:5001/
```
**Expected:** "Order Service is running!"

### Check Book Info (Catalog Service)
```
GET http://localhost:5000/books/info/1
```
**Expected:** Book details with stock count

---

## Screenshot Guide

### Postman Setup:
1. **Method:** POST
2. **URL:** `http://localhost:5001/purchase/1`
3. **Body:** None (leave empty)
4. **Headers:** Default (no changes needed)
5. **Click:** Send

That's it! The purchase will be processed and you'll see the response.


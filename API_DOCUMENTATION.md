# E-Commerce API Documentation

## Base URL
```
http://localhost:8000/api
```

## Authentication
The API uses JWT Bearer token authentication for protected endpoints.

### Login
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "admin@ecommerce.com",
  "password": "admin123"
}
```

**Response:**
```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### Using the Token
Add the token to all protected requests:
```http
Authorization: Bearer <your_access_token>
```

---

## API Endpoints

### Health Check
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/health` | Check API status | No |

---

### Authentication
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/v1/auth/mobile/send-otp` | Send OTP to mobile | No |
| POST | `/api/v1/auth/mobile/verify` | Verify OTP | No |
| POST | `/api/v1/auth/login` | Login with email/password | No |
| POST | `/api/v1/auth/refresh` | Refresh access token | No* |
| POST | `/api/v1/auth/logout` | Logout user | Yes |
| GET | `/api/v1/auth/me` | Get current user | Yes |
| GET | `/api/v1/auth/check` | Check token validity | Yes |

**Login Request:**
```json
{
  "email": "admin@ecommerce.com",
  "password": "admin123"
}
```

#### Send OTP
```http
POST /api/v1/auth/mobile/send-otp
Content-Type: application/json
```

**Request:**
```json
{
  "mobile": "01730586226",
  "purpose": "registration"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent successfully",
  "data": {
    "reference": "abc123xyz789",
    "expires_in": 300,
    "masked_mobile": "0173******26"
  }
}
```

> **Note:** SMS is sent via Muthobarta Gateway. The mobile number is normalized to `88017XXXXXXXX` format automatically.

#### Verify OTP (Registration)
```http
POST /api/v1/auth/mobile/verify
Content-Type: application/json
```

**Request:**
```json
{
  "mobile": "01730586226",
  "otp": "123456",
  "reference": "abc123xyz789",
  "is_registration": true,
  "password": "secret123",
  "password_confirmation": "secret123",
  "full_name": "John Doe",
  "email": "john@example.com"
}
```

#### Verify OTP (Login)
```http
POST /api/v1/auth/mobile/verify
Content-Type: application/json
```

**Request:**
```json
{
  "mobile": "01730586226",
  "otp": "123456",
  "reference": "abc123xyz789",
  "is_registration": false
}
```

**Verify Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "mobile": "8801730586226",
      "role": "customer",
      "status": true,
      "email_verified": false,
      "mobile_verified": true,
      "created_at": "2025-04-13T10:00:00.000000Z"
    },
    "tokens": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "token_type": "bearer",
      "expires_in": 3600
    }
  }
}
```

---

### Site Info (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/site/info` | Site information & config | No |
| GET | `/api/site/navigation` | Navigation menus | No |
| GET | `/api/homepage` | Homepage data (sliders, featured products, etc.) | No |
| GET | `/api/categories` | List all categories (legacy) | No |
| GET | `/api/categories/{slug}` | Get category by slug (legacy) | No |

---

### Sliders (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/sliders` | List active sliders / banners | No |

---

### Categories (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/categories` | List all categories | No |
| GET | `/api/v1/categories/{slug}` | Get category by slug | No |

**Query Parameters:**
- `page` - Page number
- `per_page` - Items per page

---

### Products (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/products` | List all products | No |
| GET | `/api/v1/products/search` | Search products | No |
| GET | `/api/v1/products/slug/{slug}` | Get product by slug | No |
| GET | `/api/v1/products/{product}` | Get product by ID | No |
| POST | `/api/v1/products/check-availability` | Check stock availability | No |
| GET | `/api/v1/products/{product}/price` | Get product price | No |
| POST | `/api/v1/products/{product}/find-variant` | Find variant by attributes | No |
| GET | `/api/products/{productId}/reviews` | Get product reviews | No |
| POST | `/api/reviews/{reviewId}/helpful` | Mark review as helpful | No |
| GET | `/api/products/{productId}/related` | Related products | No |
| GET | `/api/products/{productId}/frequently-bought` | Frequently bought together | No |

**Search Parameters:**
- `q` - Search query
- `category_id` - Filter by category
- `min_price` - Minimum price
- `max_price` - Maximum price

#### Check Availability
```http
POST /api/v1/products/check-availability
Content-Type: application/json
```

```json
{
  "product_id": 1,
  "variant_id": 2,
  "quantity": 5
}
```

#### Find Variant
```http
POST /api/v1/products/1/find-variant
Content-Type: application/json
```

```json
{
  "attributes": {
    "color": "Red",
    "size": "L"
  }
}
```

---

### User Profile (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/users/profile` | Get user profile |
| PUT | `/api/v1/users/profile` | Update user profile |

**Update Profile Request:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "phone": "01711111111"
}
```

---

### Addresses (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/users/addresses` | List addresses |
| POST | `/api/v1/users/addresses` | Create address |
| PUT | `/api/v1/users/addresses/{id}` | Update address |
| DELETE | `/api/v1/users/addresses/{id}` | Delete address |
| PATCH | `/api/v1/users/addresses/{id}/default` | Set as default |

**Create Address Request:**
```json
{
  "address_line_1": "123 Main Street",
  "address_line_2": "Apt 4B",
  "city": "Dhaka",
  "state": "Dhaka",
  "postal_code": "1200",
  "country": "Bangladesh",
  "is_default": true
}
```

---

### Cart (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/cart` | Get cart |
| POST | `/api/v1/cart/items` | Add item to cart |
| PUT | `/api/v1/cart/items/{id}` | Update cart item |
| DELETE | `/api/v1/cart/items/{id}` | Remove item from cart |
| POST | `/api/v1/cart/apply-coupon` | Apply coupon |
| DELETE | `/api/v1/cart/coupon` | Remove coupon |

**Add to Cart Request:**
```json
{
  "variant_id": 1,
  "quantity": 2
}
```

---

### Orders (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/orders` | List orders |
| POST | `/api/v1/orders` | Create order |
| GET | `/api/v1/orders/{id}` | Get order details |
| GET | `/api/v1/orders/{id}/track` | Track order |
| POST | `/api/v1/orders/{id}/cancel` | Cancel order |
| POST | `/api/v1/orders/{orderId}/payment/initiate` | Initiate payment |
| GET | `/api/v1/orders/{orderId}/payment/status` | Check payment status |

**Create Order Request:**
```json
{
  "address_id": 1,
  "payment_method": "cod",
  "notes": "Please deliver after 5 PM"
}
```

---

### Wishlist (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/wishlist` | Get wishlist |
| POST | `/api/v1/wishlist` | Add to wishlist |
| DELETE | `/api/v1/wishlist/{productId}` | Remove from wishlist |

**Add to Wishlist Request:**
```json
{
  "product_id": 1
}
```

---

### Notifications (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/notifications` | List notifications |
| PATCH | `/api/v1/notifications/{id}/read` | Mark as read |
| GET | `/api/v1/notifications/unread-count` | Get unread count |

---

### Shipping (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/shipping/calculate` | Calculate shipping cost | No |
| GET | `/api/shipping/methods` | List shipping methods | No |

**Calculate Shipping Request:**
```json
{
  "address_id": 1,
  "items": [
    { "product_id": 1, "quantity": 2 }
  ]
}
```

---

### Coupons (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/coupons/validate` | Validate a coupon code | No |

---

### Payment Callbacks (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/payment/aamarpay/success` | aamarPay success callback | No |
| POST | `/api/payment/aamarpay/fail` | aamarPay fail callback | No |
| POST | `/api/payment/aamarpay/cancel` | aamarPay cancel callback | No |

---

### Inventory (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/inventory/summary` | Inventory summary | No |
| GET | `/api/v1/inventory/low-stock` | Low stock alerts | No |
| GET | `/api/v1/inventory/out-of-stock` | Out of stock products | No |
| GET | `/api/v1/inventory/movements` | Stock movements | No |

---

### Tracking (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/tracking?order_number=ORD-123456` | Track order | No |

---

### Coupons (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/coupons/available` | List available coupons | Yes |

---

### Product Reviews (Authenticated)
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/v1/products/{productId}/reviews` | Write a review | Yes |

---

### Admin Routes (Admin Only)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/admin/dashboard` | Dashboard stats |
| GET | `/api/v1/admin/reports/sales` | Sales report |
| GET | `/api/v1/admin/reports/inventory` | Inventory report |
| GET | `/api/v1/admin/reports/customers` | Customers report |
| GET | `/api/v1/admin/reports/coupons` | Coupons report |
| POST | `/api/v1/admin/reports/export` | Export report |
| GET | `/api/v1/admin/inventory/valuation` | Inventory valuation |
| POST | `/api/v1/admin/inventory/variants/{variant}/stock` | Update variant stock |
| POST | `/api/v1/admin/inventory/bulk-update` | Bulk inventory update |
| GET | `/api/v1/admin/inventory/variants/{variant}/history` | Variant stock history |

**Export Report Request:**
```json
{
  "type": "sales",
  "format": "csv",
  "from": "2024-01-01",
  "to": "2024-12-31"
}
```

---

### POS System (Admin Only)
Point of Sale API for in-store sales with retail and wholesale pricing support.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/admin/pos` | POS main interface (HTML) |
| GET | `/admin/pos/session/open` | Open session form |
| POST | `/admin/pos/session/open` | Start new POS session |
| POST | `/admin/pos/session/close` | Close current POS session |
| GET | `/admin/pos/products/search` | Search products for POS |
| GET | `/admin/pos/products/barcode/{barcode}` | Find product by barcode/SKU |
| POST | `/admin/pos/cart/hold` | Hold current cart |
| GET | `/admin/pos/cart/held` | List held carts |
| POST | `/admin/pos/cart/retrieve/{id}` | Retrieve a held cart |
| DELETE | `/admin/pos/cart/held/{id}` | Delete a held cart |
| POST | `/admin/pos/order` | Create POS order |
| GET | `/admin/pos/order/{id}` | View order details (HTML) |
| GET | `/admin/pos/order/{id}/receipt` | Get receipt (HTML) |
| GET | `/admin/pos/order/{id}/print` | Print-friendly receipt (HTML) |
| POST | `/admin/pos/order/{id}/delivery-status` | Update delivery status |
| GET | `/admin/pos/order/{id}/tracking` | Get tracking timeline |
| GET | `/admin/pos/reports/daily` | Daily POS sales report |

#### Search Products for POS
```http
GET /admin/pos/products/search?search=laptop&category_id=1
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Laptop Dell XPS 15",
      "sku": "ELE-20250401-1234",
      "price": 120000.00,
      "wholesale_price": 96000.00,
      "stock": 15,
      "image": "http://localhost:8000/storage/products/thumb_1.jpg",
      "has_variants": false,
      "variants": []
    }
  ]
}
```

> **Note:** `wholesale_price` is calculated automatically as `base_price - (base_price * wholesale_percentage / 100)`. When the POS is in wholesale mode, this price is used instead of `price`.

#### Find Product by Barcode
```http
GET /admin/pos/products/barcode/2001234567890
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Laptop Dell XPS 15",
    "sku": "ELE-20250401-1234",
    "price": 120000.00,
    "wholesale_price": 96000.00,
    "stock": 15,
    "has_variants": false,
    "type": "product"
  }
}
```

#### Create POS Order
```http
POST /admin/pos/order
Content-Type: application/json
```

**Retail Order Request:**
```json
{
  "items": [
    {
      "product_id": 1,
      "variant_id": null,
      "quantity": 2,
      "price": 120000.00
    }
  ],
  "subtotal": 240000.00,
  "discount_amount": 0,
  "tax_amount": 12000.00,
  "total_amount": 252000.00,
  "is_wholesale": false,
  "payments": [
    {
      "method": "cash",
      "amount": 252000.00,
      "received_amount": 260000.00,
      "change_amount": 8000.00,
      "reference": ""
    }
  ],
  "customer_name": "John Doe",
  "customer_phone": "01711111111"
}
```

**Wholesale Order Request:**
```json
{
  "items": [
    {
      "product_id": 1,
      "variant_id": null,
      "quantity": 10,
      "price": 96000.00
    }
  ],
  "subtotal": 960000.00,
  "discount_amount": 0,
  "tax_amount": 48000.00,
  "total_amount": 1008000.00,
  "is_wholesale": true,
  "payments": [
    {
      "method": "cash",
      "amount": 1008000.00,
      "received_amount": 1008000.00,
      "change_amount": 0,
      "reference": ""
    }
  ],
  "customer_name": "Tech Wholesale Ltd",
  "customer_phone": "01722222222"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "order_id": 42,
    "order_number": "POS-20250413-ABC123"
  }
}
```

#### Hold Cart
```http
POST /admin/pos/cart/hold
Content-Type: application/json

{
  "cart_data": {
    "items": [...],
    "total": 252000.00,
    "item_count": 2,
    "customer": {
      "name": "John Doe",
      "phone": "01711111111",
      "id": null
    },
    "is_wholesale": false
  },
  "customer_name": "John Doe",
  "customer_phone": "01711111111",
  "note": "Will pick up tomorrow"
}
```

#### Daily Report
```http
GET /admin/pos/reports/daily?date=2025-04-13
```

**Response:**
```json
{
  "success": true,
  "data": {
    "date": "2025-04-13",
    "summary": {
      "total_orders": 15,
      "total_sales": 1250000.00,
      "cash_sales": 800000.00,
      "card_sales": 300000.00,
      "mobile_sales": 150000.00,
      "total_items": 45
    },
    "orders": [
      {
        "order_number": "POS-20250413-ABC123",
        "total": 252000.00,
        "items": 2,
        "time": "14:30"
      }
    ]
  }
}
```

#### Wholesale Pricing Configuration
Products and variants support automatic wholesale pricing via a `wholesale_percentage` field:

- **Product level:** `wholesale_percentage` applies to the product's `base_price`
- **Variant level:** `wholesale_percentage` applies to the variant's `price`. If not set, it inherits the product's `wholesale_percentage`
- **Formula:** `wholesale_price = price * (1 - wholesale_percentage / 100)`

Example:
- Regular price: ৳600
- Wholesale percentage: 20%
- **Calculated wholesale price: ৳480**

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Operation successful"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... }
}
```

---

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Postman Collection

Import the file `POSTMAN_COLLECTION.json` into Postman for easy testing.

### Environment Variables
Set these in Postman:
- `base_url`: `http://localhost:8000`
- `access_token`: (Auto-filled after login)
- `refresh_token`: (Auto-filled after login)

---

## Test Credentials

**Admin User:**
- Email: `admin@ecommerce.com`
- Password: `admin123`

---

## Notes

1. All timestamps are in ISO 8601 format
2. Currency is in USD ($)
3. Prices are in decimal format (e.g., 99.99)
4. Pagination returns 20 items per page by default
5. API version is v1 (prefix all endpoints with `/api/v1`)

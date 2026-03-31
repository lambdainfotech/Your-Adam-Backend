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

**Login Request:**
```json
{
  "email": "admin@ecommerce.com",
  "password": "admin123"
}
```

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
| GET | `/api/v1/products/{slug}` | Get product by slug | No |

**Search Parameters:**
- `q` - Search query
- `category_id` - Filter by category
- `min_price` - Minimum price
- `max_price` - Maximum price

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

### Tracking (Public)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/v1/tracking?order_number=ORD-123456` | Track order | No |

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

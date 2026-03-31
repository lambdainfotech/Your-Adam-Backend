# Sales Module

This module provides comprehensive e-commerce sales functionality including Cart, Order, Coupon, and Wishlist management.

## Features

- **Cart Management**: Add, update, remove items; apply/remove coupons
- **Order Processing**: Create orders, track status, cancel orders
- **Coupon System**: Percentage and fixed amount discounts with usage limits
- **Wishlist**: Save products for later

## Installation

1. Register the service provider in `config/app.php`:
```php
App\Modules\Sales\Providers\SalesServiceProvider::class,
```

2. Run migrations:
```bash
php artisan migrate --path=app/Modules/Sales/database/migrations
```

3. Publish config (optional):
```bash
php artisan vendor:publish --provider="App\Modules\Sales\Providers\SalesServiceProvider" --tag=config
```

## Configuration

See `config/sales.php` for available options:

- `order_prefix` - Prefix for order numbers (default: 'ORD')
- `default_delivery_days` - Default estimated delivery time
- `tax_rate` - Tax rate as decimal (e.g., 0.1 for 10%)
- `base_shipping` - Base shipping cost
- `free_shipping_threshold` - Minimum order amount for free shipping

## API Endpoints

### Cart
- `GET /api/v1/cart` - Get cart contents
- `GET /api/v1/cart/summary` - Get cart summary
- `POST /api/v1/cart/items` - Add item to cart
- `PUT /api/v1/cart/items/{id}` - Update item quantity
- `DELETE /api/v1/cart/items/{id}` - Remove item from cart
- `POST /api/v1/cart/apply-coupon` - Apply coupon code
- `DELETE /api/v1/cart/coupon` - Remove coupon
- `DELETE /api/v1/cart` - Clear cart

### Orders
- `GET /api/v1/orders` - List user orders
- `POST /api/v1/orders` - Create order from cart
- `GET /api/v1/orders/{id}` - Get order details
- `GET /api/v1/orders/{id}/track` - Track order status
- `POST /api/v1/orders/{id}/cancel` - Cancel order

### Wishlist
- `GET /api/v1/wishlist` - Get wishlist items
- `POST /api/v1/wishlist` - Add product to wishlist
- `DELETE /api/v1/wishlist/{productId}` - Remove from wishlist
- `GET /api/v1/wishlist/check/{productId}` - Check if product is in wishlist
- `POST /api/v1/wishlist/toggle` - Toggle product in wishlist

## Events

- `OrderCreated` - Dispatched when a new order is created
- `OrderStatusChanged` - Dispatched when order status changes

## Dependencies

This module depends on:
- Catalog Module (Product, Variant models)
- Address Module (Address model)
- Core Module (User model)

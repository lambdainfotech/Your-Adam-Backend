# Enterprise eCommerce API

A production-ready, scalable eCommerce system built with Laravel 11, featuring a modular architecture optimized for 100K+ products.

## 🏗️ Architecture

- **Backend Framework**: Laravel 11.x
- **PHP Version**: 8.3+
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis
- **Authentication**: JWT (JSON Web Tokens)
- **API Documentation**: Swagger/OpenAPI
- **Containerization**: Docker & Docker Compose

## 📦 Modules

| Module | Description |
|--------|-------------|
| **Core** | Base classes, contracts, traits, and helpers |
| **Auth** | JWT authentication, OTP verification, RBAC |
| **User** | Profile management, addresses |
| **Catalog** | Categories, products, variants, attributes, size charts |
| **Inventory** | Stock management, movement tracking |
| **Sales** | Cart, orders, coupons, wishlist |
| **Marketing** | Campaigns, discounts |
| **Courier** | Shipping providers, order tracking |
| **Notification** | Email, SMS, in-app notifications |
| **Report** | Analytics, Excel exports |
| **Audit** | Activity logging |

## 🚀 Installation

### Prerequisites

- Docker & Docker Compose
- PHP 8.3+ (for local development)
- Composer

### Setup with Docker

1. Clone the repository:
```bash
git clone <repository-url>
cd ecommerce-api
```

2. Copy environment file:
```bash
cp .env.example .env
```

3. Generate JWT secret:
```bash
php artisan jwt:secret
```

4. Start Docker containers:
```bash
docker-compose up -d
```

5. Install dependencies:
```bash
docker-compose exec app composer install
```

6. Generate application key:
```bash
docker-compose exec app php artisan key:generate
```

7. Run migrations:
```bash
docker-compose exec app php artisan migrate --seed
```

8. Access the application:
- API: http://localhost:8000
- Swagger Docs: http://localhost:8000/api/documentation

### Manual Setup (without Docker)

1. Install dependencies:
```bash
composer install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

3. Update `.env` with your database credentials

4. Run migrations:
```bash
php artisan migrate --seed
```

5. Start the server:
```bash
php artisan serve
```

## 📚 API Documentation

API documentation is available via Swagger UI at `/api/documentation`.

### Authentication Flow

1. **Send OTP**:
```http
POST /api/v1/auth/mobile/send-otp
{
    "mobile": "+8801XXXXXXXXX"
}
```

2. **Verify OTP & Register/Login**:
```http
POST /api/v1/auth/mobile/verify
{
    "mobile": "+8801XXXXXXXXX",
    "otp": "123456",
    "reference": "OTP-...",
    "is_registration": true,
    "password": "securePass",
    "full_name": "John Doe",
    "email": "john@example.com"
}
```

3. **Use Access Token**:
```http
Authorization: Bearer <access_token>
```

## 🔑 Key Features

### Authentication & Authorization
- JWT-based authentication with refresh tokens
- OTP-based mobile verification
- Role-based access control (RBAC)
- 4 user levels: Super Admin, Admin, Accounts, Customer

### Product Catalog
- Nested categories (infinite depth)
- Product variants (Color, Size, etc.)
- Dynamic attributes system
- Size charts with inch/cm support
- Product images with main/thumbnail
- Full-text search with MySQL

### Inventory Management
- Real-time stock tracking
- Stock movement logging
- Low stock alerts
- Inventory history

### Order System
- Complete order lifecycle (7 statuses)
- COD payment support
- Coupon system (percentage/fixed)
- Campaign-based discounts
- Order tracking

### Courier Integration
- Multi-courier support
- Tracking ID generation
- Customer tracking via web

### Notifications
- Email notifications (queued)
- SMS notifications (queued)
- In-app notification center
- Order status updates

### Reports
- Sales reports with date filtering
- Inventory reports
- Customer analytics
- Coupon usage reports
- Excel exports

## 🗄️ Database Schema

- 42+ tables with optimized indexing
- Soft deletes for data integrity
- Audit logging for all changes
- Partition-ready for large tables

## 🔒 Security

- API rate limiting
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection
- CORS configuration
- Input validation

## ⚡ Performance

- Redis caching (products, cart, search)
- Database query optimization
- Eager loading for relationships
- Pagination on all listings
- Queue system for background jobs

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

## 📁 Project Structure

```
app/
├── Modules/              # All business modules
│   ├── Core/            # Base classes
│   ├── Auth/            # Authentication
│   ├── User/            # User management
│   ├── Catalog/         # Products, categories
│   ├── Inventory/       # Stock management
│   ├── Sales/           # Cart, orders, coupons
│   ├── Marketing/       # Campaigns
│   ├── Courier/         # Shipping
│   ├── Notification/    # Email, SMS
│   ├── Report/          # Analytics
│   └── Audit/           # Activity logs
├── Http/                # Global HTTP layer
└── Providers/           # Service providers

database/
├── migrations/          # All migrations
└── seeders/            # Demo data

docker/                 # Docker configuration
├── nginx/
├── php/
├── mysql/
└── docker-compose.yml
```

## 🛠️ Development

### Running Queue Workers

```bash
docker-compose exec app php artisan queue:work --sleep=3 --tries=3
```

### Running Scheduler

```bash
docker-compose exec app php artisan schedule:work
```

### Clearing Cache

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

## 📝 License

This project is proprietary software.

## 👨‍💻 Author

Enterprise eCommerce Platform Development Team

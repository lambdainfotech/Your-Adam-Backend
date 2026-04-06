# JWT Authentication System - Complete Overview

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [Authentication Flow](#authentication-flow)
3. [Components](#components)
4. [Configuration](#configuration)
5. [Usage Examples](#usage-examples)
6. [Security Features](#security-features)
7. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           CLIENT LAYER                                       │
│  ┌──────────────────────┐              ┌──────────────────────┐              │
│  │   Web Browser        │              │   Mobile/API Client  │              │
│  │   (Admin Panel)      │              │   (iOS/Android/App)  │              │
│  └──────────┬───────────┘              └──────────┬───────────┘              │
│             │                                      │                          │
│             │ HTTP-Only Cookie                     │ Authorization Header     │
│             │ (jwt_token)                          │ (Bearer Token)           │
└─────────────┼──────────────────────────────────────┼──────────────────────────┘
              │                                      │
              └──────────────────┬───────────────────┘
                                 │
┌────────────────────────────────▼─────────────────────────────────────────────┐
│                           APPLICATION LAYER                                  │
│                                                                              │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │                      MIDDLEWARE STACK                                │   │
│   │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌────────────┐ │   │
│   │  │   Encrypt   │→│   Start     │→│  JWT Auth   │→│   Route    │ │   │
│   │  │   Cookies   ││   Session   ││ Middleware  ││  Handler   │ │   │
│   │  └─────────────┘  └─────────────┘  └─────────────┘  └────────────┘ │   │
│   └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │                      JWT AUTH CONTROLLER                             │   │
│   │  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐            │   │
│   │  │  login() │  │ logout() │  │refresh() │  │   me()   │            │   │
│   │  └──────────┘  └──────────┘  └──────────┘  └──────────┘            │   │
│   └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
│   ┌─────────────────────────────────────────────────────────────────────┐   │
│   │                         JWT TRAITS                                   │   │
│   │  ┌──────────────────────────────────────────────────────────────┐   │   │
│   │  │  JWTAuthTrait                                                 │   │   │
│   │  │  ├── attemptLogin()                                          │   │   │
│   │  │  ├── generateRefreshToken()                                  │   │   │
│   │  │  ├── createLoginResponse()                                   │   │   │
│   │  │  ├── createLogoutResponse()                                  │   │   │
│   │  │  ├── createTokenCookie()                                     │   │   │
│   │  │  └── refreshAccessToken()                                    │   │   │
│   │  └──────────────────────────────────────────────────────────────┘   │   │
│   └─────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
                                 │
┌────────────────────────────────▼─────────────────────────────────────────────┐
│                              DATA LAYER                                      │
│                                                                              │
│   ┌─────────────┐  ┌─────────────┐  ┌─────────────────────────────────────┐  │
│   │   Users     │  │    JWT      │  │         Token Storage               │  │
│   │   Table     │  │   Package   │  │  (Stateless - No DB Required)       │  │
│   │             │  │             │  │                                     │  │
│   │ - id        │  │ - Sign      │  │  Tokens are self-contained JWTs     │  │
│   │ - email     │  │ - Verify    │  │  containing all user info           │  │
│   │ - password  │  │ - Refresh   │  │                                     │  │
│   │ - role_id   │  │ - Blacklist │  │  Blacklist stored in cache/Redis    │  │
│   │ - status    │  │             │  │  for logout functionality           │  │
│   └─────────────┘  └─────────────┘  └─────────────────────────────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Authentication Flow

### Web Authentication Flow

```
┌─────────┐                                    ┌─────────────────────────────────────────┐
│  USER   │                                    │           LARAVEL APPLICATION            │
└────┬────┘                                    └─────────────────────────────────────────┘
     │                                                              │
     │ 1. GET /admin/login                                          │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │ 2. Show login form (CSRF token generated)                    │
     │ <──────────────────────────────────────────────────────────────│
     │                                                              │
     │ 3. POST /admin/login                                         │
     │    {email, password, _token}                                 │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │  Validate Input  │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ JWTAuth::attempt()│ │
     │                                         │ Verify credentials │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ Generate Token   │ │
     │                                         │ - Access Token   │ │
     │                                         │ - Refresh Token  │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ Create HTTP-Only │ │
     │                                         │ Cookie (encrypted│ │
     │                                         │ by Laravel)      │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │ 4. Set-Cookie: jwt_token=encrypted_token                     │
     │    Location: /admin/dashboard                                │
     │ <──────────────────────────────────────────────────────────────│
     │                                                              │
     │ 5. GET /admin/dashboard                                      │
     │    Cookie: jwt_token=...                                     │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │ Decrypt Cookie   │ │
     │                                         │ (Laravel auto)   │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ JWTAuthMiddleware │ │
     │                                         │ Validate Token   │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ Set Auth User    │ │
     │                                         └────────┬─────────┘ │
     │                                                              │
     │ 6. Dashboard HTML                                            │
     │ <──────────────────────────────────────────────────────────────│
     │                                                              │
     │ 7. GET /admin/products                                       │
     │    Cookie: jwt_token=...                                     │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │ 8. Products HTML                                             │
     │ <──────────────────────────────────────────────────────────────│
     │                                                              │
     │                    [TOKEN EXPIRES OR LOGOUT]                 │
     │                                                              │
     │ 9. POST /admin/logout                                        │
     │    Cookie: jwt_token=...                                     │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │ Invalidate Token │ │
     │                                         │ (Blacklist)      │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ Clear Cookie     │ │
     │                                         └────────┬─────────┘ │
     │                                                              │
     │ 10. Set-Cookie: jwt_token=deleted                            │
     │     Location: /admin/login                                   │
     │ <──────────────────────────────────────────────────────────────│
```

### API Authentication Flow

```
┌─────────┐                                    ┌─────────────────────────────────────────┐
│  APP    │                                    │           LARAVEL APPLICATION            │
└────┬────┘                                    └─────────────────────────────────────────┘
     │                                                              │
     │ 1. POST /api/v1/auth/login                                   │
     │    {email, password}                                         │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │ Validate Input   │ │
     │                                         │ JWTAuth::attempt()│ │
     │                                         │ Generate Tokens  │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │ 2. JSON Response:                                │           │
     │    {                                             │           │
     │      "access_token": "eyJ0eXAi...",              │           │
     │      "refresh_token": "eyJ0eXAi...",             │           │
     │      "token_type": "bearer",                     │           │
     │      "expires_in": 7200                          │           │
     │    }                                             │           │
     │ <──────────────────────────────────────────────────────────────│
     │                                                              │
     │ 3. Store tokens securely in app                              │
     │                                                              │
     │ 4. GET /api/v1/users/profile                                 │
     │    Authorization: Bearer eyJ0eXAi...                         │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │ JWTAuthMiddleware │ │
     │                                         │ Extract token    │ │
     │                                         │ from header      │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ Validate Token   │ │
     │                                         └────────┬─────────┘ │
     │                                                  │           │
     │                                         ┌────────▼─────────┐ │
     │                                         │ Set Auth User    │ │
     │                                         └────────┬─────────┘ │
     │                                                              │
     │ 5. JSON Response:                              │           │
     │    {                                           │           │
     │      "id": 1,                                  │           │
     │      "name": "Admin",                          │           │
     │      "email": "admin@ecom.com"                 │           │
     │    }                                           │           │
     │ <────────────────────────────────────────────────────────────│
     │                                                              │
     │                    [TOKEN EXPIRES]                           │
     │                                                              │
     │ 6. POST /api/v1/auth/refresh                                 │
     │    {refresh_token: "eyJ0eXAi..."}                            │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │ Validate Refresh │ │
     │                                         │ Token            │ │
     │                                         │ Generate New     │ │
     │                                         │ Tokens           │ │
     │                                         └────────┬─────────┘ │
     │                                                              │
     │ 7. New tokens returned                                       │
     │ <────────────────────────────────────────────────────────────│
     │                                                              │
     │                    [LOGOUT]                                  │
     │                                                              │
     │ 8. POST /api/v1/auth/logout                                  │
     │    Authorization: Bearer eyJ0eXAi...                         │
     │ ──────────────────────────────────────────────────────────────>│
     │                                                              │
     │                                         ┌──────────────────┐ │
     │                                         │ Invalidate Token │ │
     │                                         │ (Blacklist)      │ │
     │                                         └────────┬─────────┘ │
     │                                                              │
     │ 9. Success response                        │           │
     │ <────────────────────────────────────────────────────────────│
```

---

## Components

### 1. JWTAuthController

**Location:** `app/Http/Controllers/Auth/JWTAuthController.php`

**Methods:**

| Method | Route | Description | Auth Required |
|--------|-------|-------------|---------------|
| `showLoginForm()` | GET /admin/login | Show login form | No |
| `login()` | POST /admin/login, POST /api/v1/auth/login | Authenticate user | No |
| `logout()` | POST /admin/logout, POST /api/v1/auth/logout | Logout user | Yes |
| `refresh()` | POST /api/v1/auth/refresh | Refresh access token | No |
| `me()` | GET /api/v1/auth/me | Get current user | Yes |
| `check()` | GET /api/v1/auth/check | Check auth status | Yes |

### 2. JWTAuthMiddleware

**Location:** `app/Http/Middleware/JWTAuthMiddleware.php`

**Purpose:** Validates JWT tokens for protected routes

**Process:**
1. Extract token from cookie (web) or Authorization header (API)
2. Validate JWT signature
3. Check token expiration
4. Authenticate user
5. Set user in request context

**Exclusions:** Login routes are excluded to prevent redirect loops

### 3. JWTAuthTrait

**Location:** `app/Traits/JWTAuthTrait.php`

**Methods:**

| Method | Description |
|--------|-------------|
| `attemptLogin($credentials)` | Attempt login with credentials, return tokens |
| `generateRefreshToken()` | Generate a new refresh token from authenticated user |
| `createLoginResponse($tokenData)` | Create web redirect or API JSON response |
| `createLogoutResponse()` | Handle logout for web or API |
| `createTokenCookie($token)` | Create HTTP-Only cookie with token |
| `refreshAccessToken($refreshToken)` | Refresh access token using refresh token |

### 4. EncryptCookies Middleware

**Location:** `app/Http/Middleware/EncryptCookies.php`

**Purpose:** Laravel's default cookie encryption - all cookies including jwt_token are automatically encrypted/decrypted

### 5. User Model

**Location:** `app/Models/User.php`

**Implementation:**
```php
class User extends Authenticatable implements JWTSubject
{
    public function getJWTIdentifier() { return $this->id; }
    
    public function getJWTCustomClaims(): array {
        return [
            'role' => $this->role?->slug,
            'is_admin' => $this->isAdmin(),
        ];
    }
}
```

---

## Configuration

### Environment Variables

```env
# JWT Authentication Configuration
AUTH_GUARD=jwt
JWT_SECRET=your-random-secret-key-here
JWT_ALGO=HS256
JWT_TTL=120                    # Access token lifetime (minutes)
JWT_REFRESH_TTL=10080         # Refresh token lifetime (minutes)
JWT_COOKIE_NAME=jwt_token
JWT_COOKIE_SECURE=false       # true for HTTPS
JWT_COOKIE_SAME_SITE=lax

# Session (for CSRF protection only)
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Routes

**Web Routes** (`routes/admin.php`):
```php
// Public
Route::middleware(['web'])->group(function () {
    Route::get('/login', [JWTAuthController::class, 'showLoginForm']);
    Route::post('/login', [JWTAuthController::class, 'login']);
});

// Protected
Route::middleware(['web', 'jwt.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // ... all other admin routes
});
```

**API Routes** (`routes/api.php`):
```php
// Public
Route::post('/auth/login', [JWTAuthController::class, 'login']);
Route::post('/auth/refresh', [JWTAuthController::class, 'refresh']);

// Protected
Route::middleware('jwt.auth')->group(function () {
    Route::post('/auth/logout', [JWTAuthController::class, 'logout']);
    Route::get('/auth/me', [JWTAuthController::class, 'me']);
    // ... all other API routes
});
```

---

## Usage Examples

### Web Login (Browser)

```php
// User submits form to /admin/login
$validated = $request->validate([
    'email' => 'required|email',
    'password' => 'required|string',
]);

$tokenData = $this->attemptLogin($validated);

// Response: Redirect to dashboard with cookie
return redirect()
    ->route('admin.dashboard')
    ->cookie($this->createTokenCookie($tokenData['access_token']));
```

### API Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@ecom.com","password":"admin123"}'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "Admin",
      "email": "admin@ecom.com"
    },
    "tokens": {
      "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "token_type": "bearer",
      "expires_in": 7200
    }
  }
}
```

### API Request with Token

```bash
curl http://localhost:8000/api/v1/users/profile \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### JavaScript (Web)

```javascript
// Automatic token handling via jwt-auth.js
JWTAuth.logout();  // Programmatic logout

// Show notification
Toast.show('Operation successful', 'success', 3000);
```

---

## Security Features

### Token Security
- ✅ **Short-lived tokens** (2 hours default)
- ✅ **Encrypted cookies** (Laravel automatic encryption)
- ✅ **HTTP-Only cookies** (JavaScript cannot access)
- ✅ **CSRF protection** for web forms
- ✅ **Token blacklisting** on logout

### Transport Security
- ✅ **SameSite cookies** (CSRF protection)
- ✅ **Secure flag** support (for HTTPS)

### User Security
- ✅ **Password hashing** (bcrypt)
- ✅ **Account status checking** (active/inactive)
- ✅ **Role-based access** via JWT claims

---

## Troubleshooting

### Common Issues

| Issue | Cause | Solution |
|-------|-------|----------|
| 401 Unauthorized | Token expired or invalid | Re-login or refresh token |
| 419 CSRF Error | CSRF token mismatch | Clear cookies and retry |
| Token not found | Cookie not set | Check cookie domain/path settings |
| "Too many redirects" | Middleware configuration | Clear caches and retry |
| Decrypt error | Cookie encryption mismatch | Ensure same APP_KEY |

### Debug Commands

```bash
# Check JWT configuration
php artisan tinker --execute="echo config('jwt.ttl');"

# Test token generation
php artisan tinker --execute="
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
$user = User::first();
echo JWTAuth::fromUser($user);
"

# Clear all caches
php artisan optimize:clear

# Check routes
php artisan route:list --path=admin
php artisan route:list --path=api

# View logs
tail -f storage/logs/laravel.log | grep JWT
```

### Cookie Testing

```bash
# Login and save cookies
curl -c cookies.txt -X POST http://localhost:8000/admin/login \
  -d "email=admin@ecom.com&password=admin123"

# Access protected route
curl -b cookies.txt http://localhost:8000/admin/dashboard
```

---

## Token Structure

### JWT Token Payload

```json
{
  "iss": "http://localhost:8000",
  "iat": 1712390400,
  "exp": 1712397600,
  "nbf": 1712390400,
  "sub": "1",
  "jti": "unique-token-id",
  "prv": "user-hash",
  "role": "admin",
  "is_admin": true
}
```

### Cookie Structure

```
Name: jwt_token
Value: [Encrypted JWT token]
Domain: 127.0.0.1
Path: /
Expires: [2 hours from creation]
HttpOnly: true
Secure: false (true in production with HTTPS)
SameSite: lax
```

---

## Performance Considerations

### Stateless Authentication
- No database queries for session validation
- Tokens are self-validating
- Better horizontal scalability

### Caching
- User data can be cached to reduce DB queries
- Token blacklist stored in Redis for faster lookups

### Token Refresh
- Currently manual (refresh endpoint)
- Can implement automatic background refresh if needed

---

## API vs Web Comparison

| Feature | Web (Browser) | API (Mobile/App) |
|---------|---------------|------------------|
| Token Storage | HTTP-Only Cookie | Secure Storage (Keychain/Keystore) |
| Token Transport | Automatic (Cookie header) | Manual (Authorization header) |
| CSRF Protection | Yes (Laravel built-in) | No (stateless) |
| Token Refresh | Via web interface | Via API endpoint |
| Session Expiry | Redirect to login | 401 error response |

---

## Migration from Session Auth

If migrating from Laravel's default session authentication:

1. **User passwords remain the same**
2. **User sessions will be invalidated** (need to re-login)
3. **Database structure unchanged**
4. **Routes updated to use `jwt.auth` middleware**
5. **Controllers updated to return JWT tokens**

---

## Future Enhancements

Potential improvements:

1. **Automatic token refresh** before expiry
2. **Multi-device support** with device tracking
3. **Remember me** functionality with longer-lived tokens
4. **Rate limiting** on login attempts
5. **Two-factor authentication** integration
6. **Token versioning** for instant invalidation

---

**Document Version:** 1.0  
**Last Updated:** 2026-04-06  
**Status:** Production Ready ✅

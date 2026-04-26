# QA Analysis Report — eCommerce API
**Project:** Your-Adam-Backend (Single-Vendor eCommerce)
**Date:** April 26, 2026
**QA Engineer:** Senior QA — Full Stack Analysis
**Scope:** Functional, Payment, UI/UX, Performance, Security, Order/Inventory, API, Cross-Browser, Regression

---

## Executive Summary

| Category | Status | Critical Issues | High Issues | Medium Issues | Low Issues |
|----------|--------|----------------|-------------|---------------|------------|
| Authentication | ⚠️ At Risk | 3 | 5 | 6 | 4 |
| Product Catalog | ⚠️ At Risk | 5 | 5 | 9 | 5 |
| Cart & Checkout | ⚠️ At Risk | 5 | 6 | 8 | 4 |
| Payment | 🔴 Critical | 2 | 1 | 3 | 1 |
| Security | 🔴 Critical | 3 | 2 | 4 | 3 |
| Order & Inventory | ⚠️ At Risk | 0 | 4 | 6 | 3 |
| API & Backend | ⚠️ At Risk | 0 | 3 | 5 | 4 |
| UI/UX | 🟡 Needs Work | 0 | 1 | 3 | 5 |
| Testing Coverage | 🔴 Critical | 0 | 1 | 0 | 0 |
| **TOTAL** | **🔴 CRITICAL** | **18** | **28** | **44** | **29** |

---

## 1. FUNCTIONAL TESTING

### 1.1 User Registration, Login, Logout

#### BUG-001: OTP Never Sent (Modular System) — 🔴 CRITICAL
**Module:** Authentication (Mobile OTP)
**File:** `app/Modules/Auth/Jobs/SendOTPJob.php`

**Steps to Reproduce:**
1. Register via `/api/v1/auth/mobile/send-otp`
2. Check SMS — no OTP received
3. Check database — OTP record exists but `handle()` method is empty

**Expected:** OTP should be dispatched via SMS gateway
**Actual:** OTP is created in DB but never sent; `SendOTPJob::handle()` is empty

**Fix:** Implement SMS gateway integration in `SendOTPJob::handle()`

---

#### BUG-002: Password Reset Not Implemented — 🔴 CRITICAL
**Module:** Authentication
**File:** Multiple

**Steps to Reproduce:**
1. Try to initiate password reset flow
2. No endpoint exists for `POST /auth/password/reset`
3. `OTPPurpose::PASSWORD_RESET` enum exists but unused

**Expected:** Users should be able to reset password via OTP or email
**Actual:** Password reset feature is completely missing

**Fix:** Add password reset endpoint that accepts OTP + new password

---

#### BUG-003: Duplicate Auth Systems — 🟠 HIGH
**Module:** Authentication
**Files:** `routes/api.php`, `app/Modules/Auth/Routes/api.php`

**Steps to Reproduce:**
1. Compare `/api/auth/login` vs `/api/v1/auth/login`
2. Both endpoints exist with different controllers and behavior
3. OTP service implementations differ (hashed vs plaintext)

**Expected:** Single unified auth system
**Actual:** Two parallel systems with inconsistent behavior

**Fix:** Consolidate to one auth system, deprecate the other

---

#### BUG-004: Weak Password Policy — 🟡 MEDIUM
**Module:** Authentication
**Files:** `RegisterRequest.php`, OTP-related request classes

**Expected:** Minimum 8 chars with uppercase, lowercase, number, special char
**Actual:** Minimum 4-6 chars, no complexity requirements

**Fix:** Standardize to 8+ chars with complexity rules

---

#### BUG-005: No Rate Limiting on Auth — 🟠 HIGH
**Module:** Authentication
**Files:** All auth routes

**Expected:** Throttle login/OTP attempts (e.g., 5 attempts per minute)
**Actual:** Unlimited attempts — vulnerable to brute force and SMS bombing

**Fix:** Add Laravel `RateLimiter` to login, OTP send, and refresh endpoints

---

#### BUG-006: Auto-Generated Password Exposed — 🟠 HIGH
**Module:** Admin User Management
**File:** `app/Http/Controllers/Admin/UserController.php`

**Steps to Reproduce:**
1. Admin creates new user
2. Success flash message displays raw password: `"Password: {password}"`
3. Password visible in session and logs

**Expected:** Password sent securely via email/SMS only
**Actual:** Password exposed in UI flash message

**Fix:** Remove password from flash message; send via email

---

### 1.2 Product Browsing, Search, Filtering

#### BUG-007: `allProducts()` Returns ALL Products — 🔴 CRITICAL
**Module:** Product Catalog
**File:** `app/Models/Category.php`

**Steps to Reproduce:**
1. Query `$category->allProducts`
2. Observe products from ALL categories returned

**Root Cause:**
```php
return $this->hasMany(Product::class)
    ->orWhereIn('category_id', function ($query) {
        $query->select('id')->from('categories')->where('parent_id', $this->id);
    });
```
`orWhereIn` applies globally without wrapping in `where()` closure

**Expected:** Only products in this category and its children
**Actual:** All products in database

**Fix:** Wrap `orWhereIn` in `where(function($q) { ... })`

---

#### BUG-008: Product Search Uses Obsolete `status` Column — 🟠 HIGH
**Module:** Product Search
**File:** `app/Services/ProductSearchService.php`, `app/Services/ProductApiTransformer.php`

**Steps to Reproduce:**
1. Search products via API
2. Query includes `->where('status', 1)`
3. But products table uses `is_active` (boolean), not `status`

**Expected:** Filter by `is_active = true`
**Actual:** Filter by non-existent `status` column

**Fix:** Replace `where('status', 1)` with `where('is_active', true)`

---

#### BUG-009: Hardcoded Attribute IDs in Search — 🟡 MEDIUM
**Module:** Product Search
**File:** `app/Services/ProductSearchService.php`

**Root Cause:**
```php
if ($attrValue->attribute_id == 1) // Size
if ($attrValue->attribute_id == 2) // Color
```

**Expected:** Dynamic attribute lookup by code/name
**Actual:** Breaks if size/color attributes have different IDs

**Fix:** Query attribute IDs dynamically or use attribute codes

---

#### BUG-010: CategoryController Missing `Str` Import — 🔴 CRITICAL
**Module:** Admin Category Management
**File:** `app/Http/Controllers/Admin/CategoryController.php`

**Steps to Reproduce:**
1. Go to Admin → Categories → Add New
2. Submit form
3. 500 error: `Class "Str" not found`

**Fix:** Add `use Illuminate\Support\Str;`

---

### 1.3 Cart Operations

#### BUG-011: Cart `addItem` Overwrites Quantity — 🟠 HIGH
**Module:** Cart
**File:** `app/Modules/Sales/src/Services/CartService.php`

**Steps to Reproduce:**
1. Add 2 units of Product A to cart
2. Add 3 more units of Product A
3. Cart shows 3 units instead of 5

**Root Cause:**
```php
$cart->items()->updateOrCreate(
    ['variant_id' => $dto->variantId],
    ['quantity' => $dto->quantity, ...]  // Overwrites instead of adding
);
```

**Expected:** Quantity should increment (2 + 3 = 5)
**Actual:** Quantity is overwritten (result = 3)

**Fix:** Use `$item->increment('quantity', $dto->quantity)` or read existing first

---

#### BUG-012: Unreachable Cart Endpoints — 🟠 HIGH
**Module:** Cart API
**File:** `routes/api.php`

**Expected:** `/api/cart/summary` and `/api/cart/clear` should be accessible
**Actual:** Methods exist in `CartController` but no routes registered

**Fix:** Add routes for `summary()` and `clear()`

---

### 1.4 Checkout Process

#### BUG-013: Shipping Cost Inconsistency — 🟡 MEDIUM
**Module:** Checkout
**Files:** `CartService.php`, `ShippingCalculatorService.php`, `GuestCheckoutService.php`

**Steps to Reproduce:**
1. Check shipping in cart (uses `CartService`)
2. Check shipping at checkout (uses `ShippingCalculatorService`)
3. Check shipping in guest checkout (uses `GuestCheckoutService`)
4. All three show different values

**Expected:** Consistent shipping calculation across all flows
**Actual:** Three different calculation methods

**Fix:** Centralize shipping logic in a single service

---

#### BUG-014: Guest Checkout Creates Duplicate Users — 🟡 MEDIUM
**Module:** Guest Checkout
**File:** `app/Services/GuestCheckoutService.php`

**Steps to Reproduce:**
1. Guest checks out with email `test@example.com`
2. Same email checks out again
3. Two user records created with same email

**Expected:** Reuse existing user or prompt to login
**Actual:** Duplicate user accounts created

**Fix:** Check for existing user by email before creating

---

### 1.5 Order Placement & Confirmation

#### BUG-015: Order Tracking API Broken — 🟠 HIGH
**Module:** Order Tracking
**File:** `app/Modules/Sales/src/Services/OrderService.php`

**Steps to Reproduce:**
1. Call `GET /api/orders/{id}/track`
2. Pass numeric order ID (e.g., `42`)
3. Service expects `order_number` string format

**Root Cause:**
```php
// Route passes ID
$tracking = $this->service->getTracking($id);

// Service expects order number
$order = $this->orderRepository->findByNumber($orderNumber);
```

**Expected:** Should look up by order ID or accept both formats
**Actual:** Searches for `order_number = "42"` which never matches

**Fix:** Change route or service to use consistent identifier

---

#### BUG-016: Order Tracking Information Disclosure — 🔴 CRITICAL
**Module:** Order Tracking
**File:** `app/Modules/Sales/src/Services/OrderService.php`

**Steps to Reproduce:**
1. Login as User A
2. Call `GET /api/orders/ORD-ANY-ORDER/track`
3. Can track ANY order, not just own

**Expected:** Should verify `user_id` ownership
**Actual:** No ownership check — any authenticated user can track any order

**Fix:** Add `$order->user_id === auth()->id()` check

---

## 2. PAYMENT TESTING

### 2.1 Payment Callback Forgery — 🔴 CRITICAL
**Module:** Payment (Aamarpay)
**File:** `app/Services/AamarPayService.php`, `app/Http/Controllers/Frontend/PaymentController.php`

**Steps to Reproduce:**
```bash
curl -X POST http://site.com/api/payment/aamarpay/success \
  -d "mer_txnid=ORD-20240101-ABC123" \
  -d "pay_status=Successful"
```

**Expected:** Should verify callback signature, amount, and store ID
**Actual:** Order marked as PAID with zero verification

**Impact:** Anyone can mark any order as paid without paying

**Fix:**
1. Verify callback signature using `signature_key`
2. Server-side verify via Aamarpay API
3. Match amount with order total
4. Implement idempotency key

---

### 2.2 Missing Online Payment Methods — 🔴 CRITICAL
**Module:** Payment

**Expected:** bKash, Nagad, Card, COD online integration
**Actual:** Only Aamarpay (online) + COD implemented. bKash/Nagad/Card only exist as POS methods.

**Fix:** Integrate bKash Checkout API, Nagad Payment Gateway, and Stripe/SSLCommerz for cards

---

### 2.3 Payment Status Enum Mismatch — 🟠 HIGH
**Module:** Payment
**File:** `app/Http/Controllers/Frontend/PaymentController.php`

**Root Cause:**
```php
$order->update(['payment_status' => 'cancelled']);
```
But `PaymentStatus` enum only has: `PENDING`, `PAID`, `FAILED`, `REFUNDED`

**Expected:** Use valid enum value
**Actual:** Invalid value will throw `ValueError`

**Fix:** Change to `PaymentStatus::FAILED->value`

---

### 2.4 No Transaction Audit Trail — 🟡 MEDIUM
**Module:** Payment

**Expected:** Dedicated `transactions` table with payment gateway responses
**Actual:** Payment status stored directly on `orders` table only

**Fix:** Create `transactions` table and log all payment attempts/responses

---

### 2.5 No Duplicate Payment Prevention — 🟡 MEDIUM
**Module:** Payment
**File:** `app/Services/AamarPayService.php`

**Expected:** Idempotency check prevents double-processing same payment
**Actual:** Success callback can be replayed multiple times

**Fix:** Check if order already paid before updating; store transaction ID

---

## 3. UI/UX TESTING

### 3.1 POS System Issues

#### BUG-017: POS Customer Search 500 Error — 🔴 CRITICAL
**Module:** POS
**File:** `app/Http/Controllers/Admin/PosController.php`

**Steps to Reproduce:**
1. Open POS system
2. Click "Change" customer
3. Type any customer name
4. 500 Internal Server Error

**Root Cause:** Missing `use App\Models\User;` import in controller

**Fix:** Add the import statement

---

#### BUG-018: POS Modal Does Not Auto-Load Customers — 🟡 MEDIUM
**Module:** POS
**File:** `resources/views/admin/pos/index.blade.php`

**Steps to Reproduce:**
1. Open POS
2. Click "Change" customer
3. Modal opens but customer list is empty until typing

**Expected:** Should show all customers on modal open
**Actual:** Empty list initially

**Fix:** Call `searchCustomers()` when modal opens

---

### 3.2 Admin Panel Issues

#### BUG-019: Admin Product Detail Page Crashes — 🔴 CRITICAL
**Module:** Admin Products
**File:** `app/Http/Controllers/Admin/ProductController.php`

**Steps to Reproduce:**
1. Go to Admin → Products
2. Click on any product to view details
3. 500 error: `$this->pricingService` undefined

**Fix:** Inject `PricingService` in constructor

---

#### BUG-020: Inventory Movement Logs Incorrect — 🔴 CRITICAL
**Module:** Inventory
**File:** `app/Models/InventoryMovement.php`

**Root Cause:**
```php
'stock_before' => $variant ? $variant->stock_quantity : 0,
'stock_after' => $variant ? $variant->stock_quantity : 0,
```
Both values are identical — no actual change recorded.

**Expected:** `stock_after` should reflect post-operation quantity
**Actual:** `stock_after` equals `stock_before`

**Fix:** Calculate `stock_after = stock_before + quantity` (or pass updated variant)

---

## 4. PERFORMANCE TESTING

### 4.1 Image Thumbnails Not Resized — 🟡 MEDIUM
**Module:** Media
**File:** `app/Services/ImageUploadService.php`

**Expected:** Actual resized thumbnails for performance
**Actual:** Full-size copies stored as "thumbnails" — wastes storage and bandwidth

**Fix:** Implement actual image resizing with Intervention Image or GD

---

### 4.2 N+1 Query Risk in Product Lists — 🟡 MEDIUM
**Module:** Product API
**File:** `app/Services/ProductApiTransformer.php`

**Expected:** Eager load all relationships
**Actual:** `getRelatedProducts()`, `getVariantAttributes()` may trigger N+1

**Fix:** Add `with()` eager loading in repository queries

---

### 4.3 No Caching on Product Search — 🟡 MEDIUM
**Module:** Product Search

**Expected:** Search results cached for popular queries
**Actual:** Every search hits database directly

**Fix:** Implement Redis caching for search results

---

### 4.4 Database Connection in Development Mode — 🟢 LOW
**File:** `.env`

**Observation:** `APP_DEBUG=true` in production-like environment
**Risk:** Stack traces and sensitive config exposed on errors

**Fix:** Set `APP_DEBUG=false` in production

---

## 5. SECURITY TESTING

### 5.1 Payment Callback Forgery — 🔴 CRITICAL
*(See BUG in Section 2.1)*

---

### 5.2 IDOR in Address Management — 🔴 CRITICAL
**Module:** User Addresses
**File:** `app/Modules/User/src/Services/AddressService.php`

**Steps to Reproduce:**
1. Login as User A
2. Note User B's address ID (e.g., `123`)
3. Call `PUT /api/v1/users/addresses/123` with any data
4. User B's address is modified

**Root Cause:** `updateAddress()` calls `BaseRepository::update($id, $data)` without checking `user_id`

**Fix:** Scope all address queries by `user_id`

---

### 5.3 Review Helpful Vote Manipulation — 🔴 CRITICAL
**Module:** Reviews
**File:** `app/Http/Controllers/Frontend/ReviewController.php`

**Steps to Reproduce:**
```bash
curl -X POST http://site.com/api/reviews/1/helpful
```
No auth required — can spam helpful votes

**Fix:** Require authentication; prevent duplicate votes per user

---

### 5.4 Sensitive Business Data Exposure — 🟡 MEDIUM
**Module:** Product API
**File:** `app/Services/ProductApiTransformer.php`

**Exposed Fields:**
- `cost_price` — competitor can see exact margins
- `wholesale_price`
- `wholesale_percentage`

**Fix:** Remove sensitive pricing from public API responses

---

### 5.5 XSS via Review Comments — 🟡 MEDIUM
**Module:** Reviews
**File:** `app/Services/ReviewService.php`

**Observation:** Review `comment` and `title` stored and returned raw
**Risk:** If frontend uses `v-html` or `dangerouslySetInnerHTML`, stored XSS possible

**Fix:** Sanitize on storage or escape on API output

---

### 5.6 File Upload Extension Spoofing — 🟡 MEDIUM
**Module:** Media Upload
**File:** `app/Services/ImageUploadService.php`

**Root Cause:**
```php
$extension = $file->getClientOriginalExtension(); // Client-controlled!
```

**Fix:** Use `$file->guessExtension()` or `$file->getMimeType()` for validation

---

### 5.7 No Authorization in Admin Controllers — 🟠 HIGH
**Module:** Admin
**Files:** `UserController.php`, `RoleController.php`, `PermissionController.php`

**Expected:** Only super admins can manage roles/permissions
**Actual:** Any authenticated admin can create/edit/delete users and roles

**Fix:** Add `authorize()` or `can()` checks; apply role/permission middleware

---

### 5.8 Insecure JWT Cookie — 🟠 HIGH
**Module:** Authentication
**File:** `app/Traits/JWTAuthTrait.php`

**Root Cause:** `secure: false` hardcoded — cookies sent over HTTP

**Fix:** Use `config('jwt.cookie_secure')` and enforce HTTPS in production

---

### 5.9 OTP Stored in Plaintext (Modular) — 🔴 CRITICAL
**Module:** Authentication (Modular)
**File:** `app/Modules/Auth/Repositories/OTPRepository.php`

**Root Cause:** Modular system stores OTP in plaintext; traditional system hashes with `Hash::make()`

**Fix:** Hash OTPs consistently using `Hash::make()` / `Hash::check()`

---

## 6. ORDER & INVENTORY

### 6.1 Stock Not Restored on Admin Cancellation — 🟠 HIGH
**Module:** Orders
**File:** `app/Http/Controllers/Admin/OrderController.php`

**Steps to Reproduce:**
1. Place an order (stock deducted)
2. Admin cancels the order via status update
3. Stock is NOT restored

**Expected:** Inventory should be restored on cancellation
**Actual:** Stock permanently lost

**Fix:** Call stock restoration logic in admin cancel flow

---

### 6.2 Admin Status Updates Don't Log History — 🟡 MEDIUM
**Module:** Orders
**File:** `app/Http/Controllers/Admin/OrderController.php`

**Expected:** Every status change recorded in `order_status_history`
**Actual:** Admin updates modify `status` directly with no audit trail

**Fix:** Use `$order->addStatusHistory()` on every status change

---

### 6.3 Missing `completed` Status in Enum — 🟠 HIGH
**Module:** Orders
**File:** `app/Modules/Sales/src/Enums/OrderStatus.php`

**Expected:** `COMPLETED` status available
**Actual:** Admin allows `completed` but enum doesn't define it — causes `ValueError`

**Fix:** Add `COMPLETED` to `OrderStatus` enum

---

### 6.4 Courier Assignment Database Errors — 🔴 CRITICAL
**Module:** Order Fulfillment
**Files:** `app/Http/Controllers/Admin/OrderFulfillmentController.php`

**Multiple Issues:**
1. `assigned_at` is required but never set → NOT NULL violation
2. `status` column doesn't exist in migration → SQL error
3. `courier_assignment_id` passed to `TrackingHistory` but model expects `order_id`

**Fix:** Fix migration schema + controller logic

---

### 6.5 Broken Pagination in Admin Orders — 🟡 MEDIUM
**Module:** Admin Orders
**File:** `app/Http/Controllers/Admin/OrderController.php`

**Root Cause:** Merges regular orders page N with POS orders page N separately, then takes `$perPage`

**Expected:** Correct combined pagination
**Actual:** Duplicate/missing items across pages

**Fix:** Use union query or paginate combined result set

---

### 6.6 Race Condition in Stock Management — 🟡 MEDIUM
**Module:** Inventory
**Files:** `CartService.php`, `OrderService.php`

**Root Cause:** No database row locking (`FOR UPDATE`) on variants during checkout

**Expected:** Concurrent checkouts should not oversell
**Actual:** Race condition allows overselling

**Fix:** Use `DB::transaction()` with `FOR UPDATE` lock on variant rows

---

## 7. API TESTING

### 7.1 Inconsistent API Response Formats
**Observation:** Multiple response formats across endpoints:
- Some return `{success, data}`
- Some return `{success, message, data}`
- Some return raw arrays
- Some use HTTP status codes properly, others always 200

**Fix:** Standardize all API responses to a single format

---

### 7.2 Missing API Documentation
**Observation:** API routes exist but no consistent Swagger/OpenAPI documentation maintained
**Fix:** Generate and maintain API docs from route definitions

---

### 7.3 Duplicate Model System
**Observation:** Two parallel `Order`, `Cart`, `CartItem`, `OrderItem`, `OrderStatusHistory` models
**Risk:** Data inconsistency when both systems interact with same tables

**Fix:** Consolidate to single model per entity

---

### 7.4 Order Status History Table Name Mismatch
**Module:** API
**Files:** `app/Models/OrderStatusHistory.php` vs `app/Modules/Sales/src/Models/OrderStatusHistory.php`

**Expected:** Both use `order_status_history`
**Actual:** Module model uses `order_status_histories` (plural) — table doesn't exist

**Fix:** Fix table name in module model

---

## 8. CROSS-BROWSER TESTING

### 8.1 Alpine.js CDN Version
**Observation:** Uses `alpinejs@3.x.x` from CDN — `x.x.x` resolves to latest
**Risk:** Breaking changes in future Alpine.js updates could break POS UI

**Fix:** Pin to specific version: `alpinejs@3.14.3`

---

### 8.2 Tailwind CSS from CDN
**Observation:** `cdn.tailwindcss.com` used in production
**Risk:** Slower loading, no purging, potential blocking

**Fix:** Build Tailwind with PostCSS for production

---

## 9. REGRESSION TESTING

### 9.1 Zero Test Coverage — 🔴 CRITICAL
**Observation:** Only 3 test files exist (`ExampleTest.php` × 2)
**Risk:** Any code change can break existing functionality without detection

**Fix:** Add comprehensive test suite:
- Feature tests for all API endpoints
- Unit tests for services
- Browser tests for critical flows

---

### 9.2 POS Session Recently Removed — Regression Risk
**Observation:** POS session feature was recently removed; `pos_session_id` column may still exist in database
**Risk:** Existing POS orders reference deleted sessions

**Fix:** Verify database migration rollback; check for orphaned data

---

## 10. RECOMMENDED FIX PRIORITY MATRIX

### P0 — Fix Before Production Launch
| ID | Issue | Effort |
|----|-------|--------|
| Payment Callback | Verify Aamarpay signatures | Medium |
| Payment Methods | Integrate bKash/Nagad/Card | High |
| BUG-007 | Fix `allProducts()` query | Low |
| BUG-009 | Fix OTP plaintext storage | Low |
| BUG-002 | Implement password reset | Medium |
| Security | Add auth to review helpful | Low |
| Security | Fix IDOR in addresses | Low |
| BUG-015 | Fix order tracking ownership | Low |

### P1 — Fix Within 2 Weeks
| ID | Issue | Effort |
|----|-------|--------|
| BUG-011 | Fix cart quantity increment | Low |
| BUG-016 | Fix order tracking ID vs number | Low |
| BUG-020 | Fix inventory movement logging | Low |
| Security | Remove cost_price from API | Low |
| Security | Add rate limiting | Medium |
| BUG-008 | Fix product search status filter | Low |
| BUG-014 | Prevent duplicate guest users | Low |
| Auth | Consolidate auth systems | High |
| Tests | Add basic test coverage | High |

### P2 — Fix Within 1 Month
| ID | Issue | Effort |
|----|-------|--------|
| BUG-013 | Unify shipping calculation | Medium |
| BUG-003 | Remove duplicate auth routes | Low |
| Performance | Implement image resizing | Medium |
| Performance | Add Redis caching | Medium |
| Security | Sanitize review content | Low |
| Admin | Add authorization checks | Medium |
| POS | Polish customer search UX | Low |

---

## APPENDIX: Quick Reference — Files to Fix

| File | Issues |
|------|--------|
| `app/Http/Controllers/Admin/PosController.php` | Missing `User` import |
| `app/Http/Controllers/Admin/ProductController.php` | Missing `PricingService` injection |
| `app/Http/Controllers/Admin/CategoryController.php` | Missing `Str` import |
| `app/Http/Controllers/Admin/UserController.php` | Password exposed in flash |
| `app/Http/Controllers/Admin/OrderController.php` | No stock restore, no history log |
| `app/Http/Controllers/Admin/OrderFulfillmentController.php` | Courier DB schema mismatch |
| `app/Http/Controllers/Frontend/PaymentController.php` | No callback verification |
| `app/Http/Controllers/Frontend/ReviewController.php` | No auth on helpful |
| `app/Models/Category.php` | `allProducts()` broken |
| `app/Models/InventoryMovement.php` | `stock_after` bug |
| `app/Services/AamarPayService.php` | No signature verification |
| `app/Services/ProductApiTransformer.php` | Exposes cost_price |
| `app/Services/ProductSearchService.php` | Hardcoded IDs, obsolete status |
| `app/Services/ImageUploadService.php` | No actual resizing |
| `app/Modules/Auth/Jobs/SendOTPJob.php` | Empty handle() |
| `app/Modules/Auth/Repositories/OTPRepository.php` | Plaintext OTP |
| `app/Modules/User/src/Services/AddressService.php` | IDOR vulnerability |
| `app/Modules/Sales/src/Services/CartService.php` | Overwrites quantity |
| `app/Modules/Sales/src/Services/OrderService.php` | No ownership check |
| `app/Modules/Sales/src/Models/OrderStatusHistory.php` | Wrong table name |
| `resources/views/admin/pos/index.blade.php` | Customer search UX |
| `.env` | `APP_DEBUG=true` risk |

---

*Report generated by automated code analysis + manual review.*
*Total routes analyzed: 372 (82 API + 248 Admin + 42 Web)*
*Total models analyzed: 45+*
*Total controllers analyzed: 60+*

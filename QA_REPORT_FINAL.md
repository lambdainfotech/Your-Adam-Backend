# Comprehensive QA Analysis Report — eCommerce Single-Vendor Platform

**Project:** Your-Adam-Backend (Single-Vendor eCommerce)  
**Date:** April 26, 2026  
**QA Engineer:** Senior QA Engineer — Full Stack Analysis  
**Scope:** Functional, Payment, UI/UX, Performance, Security, Order/Inventory, API, Cross-Browser, Regression  
**Total PHP Files Analyzed:** 367  
**Total Routes Analyzed:** 372 (82 API + 248 Admin + 42 Web)  
**Previous Sprint:** Critical/High bug-fix sprint completed (18 Critical + 28 High issues addressed)

---

## Executive Summary

| Category | Status | Critical (Open) | High (Open) | Medium (Open) | Low (Open) |
|----------|--------|-----------------|-------------|---------------|------------|
| Authentication | 🔴 Critical | 2 | 3 | 3 | 2 |
| Product Catalog | 🟠 High Risk | 2 | 3 | 6 | 4 |
| Cart & Checkout | 🔴 Critical | 2 | 2 | 3 | 1 |
| Payment Gateway | 🔴 Critical | 2 | 3 | 3 | 2 |
| Security | 🔴 Critical | 5 | 5 | 5 | 3 |
| Order & Inventory | 🔴 Critical | 3 | 4 | 6 | 3 |
| API & Backend | 🟠 High Risk | 1 | 4 | 7 | 4 |
| UI/UX (Admin/POS) | 🟠 High Risk | 1 | 3 | 7 | 5 |
| Testing Coverage | 🔴 Critical | 1 | 0 | 0 | 0 |
| **TOTAL OPEN** | **🔴 CRITICAL** | **19** | **27** | **40** | **24** |
| **FIXED This Sprint** | — | **14** | **15** | **8** | **5** |

**Verdict:** The platform is **NOT PRODUCTION-READY**. Critical vulnerabilities in payment forgery, admin authorization bypass, stock race conditions, and guest checkout account takeover present existential business and legal risks. A minimum of 2–3 additional engineering sprints is required before launch.

---

## Legend

| Symbol | Meaning |
|--------|---------|
| ✅ | Fixed in previous sprint (verified) |
| ⚠️ | Partially fixed / needs re-verification |
| 🔴 | NEW issue or remains unfixed |

---

## 1. FUNCTIONAL TESTING

### 1.1 User Registration, Login, Logout

#### BUG-AUTH-001: Payment Callback Signature Not Verified — 🔴 CRITICAL
**Module:** Payment (Aamarpay)  
**File:** `app/Services/AamarPayService.php:154-197`  
**Status:** 🔴 REMAINS OPEN

**Steps to Reproduce:**
```bash
curl -X POST http://site.com/api/payment/aamarpay/success \
  -d "mer_txnid=ORD-20250426-ABC123" \
  -d "pay_status=Successful" \
  -d "amount=100.00"
```

**Expected:** Order should only be marked paid after cryptographic signature verification, amount matching, and server-side API confirmation.
**Actual:** Order marked as `paid` immediately upon receiving `pay_status=Successful`. Zero verification performed.
**Impact:** Attackers can mark any order as paid without transferring funds.

**Fix:**
1. Call Aamarpay verify API inside `handleSuccess()` before updating order
2. Verify `amount` matches `$order->total_amount`
3. Check `store_id` in callback matches config
4. Add idempotency guard: `if ($order->payment_status === 'paid') return`

---

#### BUG-AUTH-002: Guest Checkout Authentication Bypass — 🔴 CRITICAL
**Module:** Guest Checkout  
**File:** `app/Services/GuestCheckoutService.php:158-178`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Register a user with email `victim@example.com`
2. Place a guest checkout using `victim@example.com`
3. `GuestCheckoutService::createUser()` returns the existing user object
4. `GuestCheckoutController` generates a JWT token for that user
5. Attacker now has full account access

**Expected:** Guest checkout should create a separate guest record or require authentication for existing emails.
**Actual:** Attacker obtains valid JWT token for any known email address.

**Fix:** Remove the `$existingUser` fallback entirely. Always create a new user with `is_guest=true` flag. If email exists, return 422 with "Email already registered. Please login."

---

#### BUG-AUTH-003: Password Reset Not Implemented — 🔴 CRITICAL
**Module:** Authentication  
**File:** Multiple  
**Status:** 🔴 REMAINS OPEN

**Steps to Reproduce:**
1. Try `POST /api/auth/password/reset` — route does not exist
2. `OTPPurpose::PASSWORD_RESET` enum value exists but is unused
3. No controller method handles password reset flow

**Expected:** Users should be able to reset password via OTP verification + new password submission.
**Actual:** Feature is completely missing.

**Fix:** Add `POST /api/auth/password/reset` endpoint that accepts `email`, `otp`, and `new_password`.

---

#### BUG-AUTH-004: Weak Password Policy — 🟠 HIGH
**Module:** Authentication  
**File:** `app/Http/Controllers/Auth/JWTAuthController.php:57-59`  
**Status:** 🔴 REMAINS OPEN

**Steps to Reproduce:**
1. Register with password `1234`
2. Validation passes

**Expected:** Minimum 8 characters with uppercase, lowercase, digit, and special character.
**Actual:** 4-character passwords accepted. No complexity requirements.

**Fix:** Update validation rule to `'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/']`

---

#### BUG-AUTH-005: No Rate Limiting on Auth Endpoints — 🟠 HIGH
**Module:** Authentication  
**File:** `routes/api.php`  
**Status:** ⚠️ PARTIALLY FIXED

**Previous State:** No rate limiting on any endpoint.  
**Current State:** Login routes wrapped with `throttle:5,1`. OTP send and guest checkout still unlimited.

**Steps to Reproduce:**
```bash
for i in {1..100}; do
  curl -X POST http://site.com/api/auth/otp/send -d "mobile=01712345678"
done
```

**Expected:** Rate limited after 5 attempts per minute.
**Actual:** Unlimited OTP requests = SMS bombing vector.

**Fix:** Add `throttle:3,1` to OTP send, `throttle:5,1` to guest checkout.

---

#### BUG-AUTH-006: Auto-Generated Passwords Weak and Exposed — 🟠 HIGH
**Module:** Admin User Management  
**File:** `app/Http/Controllers/Admin/UserController.php:82-85`  
**Status:** ⚠️ PARTIALLY FIXED

**Previous State:** Password exposed in flash message.  
**Current State:** Password removed from flash message, but generation method remains weak.

**Issue:** `generateRandomPassword()` uses `str_shuffle()` with alphanumeric only. No special characters. `str_shuffle()` has known statistical bias.

**Fix:** Use `random_bytes()` and include special characters, or force password reset on first login.

---

#### BUG-AUTH-007: OTP Codes Logged in Plaintext — 🟡 MEDIUM
**Module:** Authentication (Modular)  
**File:** `app/Modules/Auth/src/Jobs/SendOTPJob.php:34-37`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Request OTP
2. Check `storage/logs/laravel.log`
3. Entry contains: `["mobile" => "017...", "code" => "123456"]`

**Expected:** OTP secrets must never be written to logs.
**Actual:** Full OTP code logged at `info` level.

**Fix:** Remove `'code' => $this->code` from log context. Log only mobile and reference.

---

#### BUG-AUTH-008: Refresh Token System Bypasses Token Type Validation — 🟠 HIGH
**Module:** Authentication  
**File:** `app/Traits/JWTAuthTrait.php:203-235`  
**Status:** 🔴 NEW

**Issue:** `refreshAccessToken()` does not verify that the token being refreshed has `type: 'refresh'`. Any access token can be passed as a refresh token to generate a new access token, extending sessions indefinitely.

**Fix:** Decode payload and `abort_if($payload['type'] !== 'refresh', 401)`.

---

### 1.2 Product Browsing, Search, Filtering

#### BUG-PROD-001: `allProducts()` Returns ALL Products — ✅ FIXED
**File:** `app/Models/Category.php`  
**Fix Applied:** Wrapped `orWhereIn` in `where()` closure.  
**Verification:** Code review confirms correct scoping.

---

#### BUG-PROD-002: Product Search Uses Obsolete `status` Column — ✅ FIXED
**File:** `app/Services/ProductSearchService.php`, `app/Services/ProductApiTransformer.php`  
**Fix Applied:** Removed `->where('status', 1)` filter.  
**Verification:** Confirmed removed from both files.

---

#### BUG-PROD-003: Hardcoded Attribute IDs in Search — 🟡 MEDIUM
**File:** `app/Services/ProductSearchService.php:192-235`  
**Status:** 🔴 REMAINS OPEN

```php
if ($attrValue->attribute_id == 1) // Size
if ($attrValue->attribute_id == 2) // Color
```

**Expected:** Dynamic attribute lookup by code/name.
**Actual:** Breaks if size/color attributes have different IDs after re-seeding.

**Fix:** Query attributes by slug: `Attribute::where('slug', 'size')->first()->id`.

---

#### BUG-PROD-004: CategoryController Missing `Str` Import — ✅ FIXED
**File:** `app/Http/Controllers/Admin/CategoryController.php`  
**Fix Applied:** Added `use Illuminate\Support\Str;`.  
**Verification:** Confirmed present.

---

#### BUG-PROD-005: Category `is_active` Checkbox Bug — 🟠 HIGH
**File:** `app/Http/Controllers/Admin/CategoryController.php:64,109`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Edit an active category
2. Uncheck the "Active" checkbox
3. Save

**Expected:** Category becomes inactive (`is_active = false`).
**Actual:** `$request->boolean('is_active', true)` — when checkbox is unchecked, key is missing, so it defaults to `true`. Category remains active.

**Fix:** Use `$request->boolean('is_active')` without default, or explicitly check `$request->has('is_active')`.

---

#### BUG-PROD-006: Category Deletion Orphans Subcategories — 🟠 HIGH
**File:** `app/Http/Controllers/Admin/CategoryController.php:127-141`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Create parent category "Electronics"
2. Create subcategory "Mobile Phones" under it
3. Delete "Electronics" (no products attached)
4. "Mobile Phones" now has invalid `parent_id`

**Expected:** Block deletion or cascade to children.
**Actual:** Subcategories orphaned with dangling `parent_id`.

**Fix:** Add check: `if ($category->children()->count() > 0) return back()->with('error', '...')`.

---

#### BUG-PROD-007: Category Can Be Its Own Parent — 🟡 MEDIUM
**File:** `app/Http/Controllers/Admin/CategoryController.php:45-61,89-107`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Edit a category
2. Set Parent Category to itself
3. Save successfully

**Expected:** Validation should reject self-referencing parent.
**Actual:** Infinite recursion risk in hierarchy queries.

**Fix:** Add rule: `'parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id`.

---

#### BUG-PROD-008: Price Filtering Ignores Sale Prices — 🟡 MEDIUM
**File:** `app/Services/ProductSearchService.php:105-110`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Set product on sale: `base_price=5000`, `sale_price=3000`
2. Search with `max_price=4000`
3. Product is hidden

**Expected:** Product should appear because active sale price is within range.
**Actual:** Filter only checks `base_price`, ignoring `sale_price`.

**Fix:** Apply price filter against computed `final_price` or add sub-query considering `sale_price`.

---

### 1.3 Cart Operations

#### BUG-CART-001: Cart `addItem` Overwrites Quantity — ✅ FIXED
**File:** `app/Modules/Sales/src/Services/CartService.php`  
**Fix Applied:** Changed from `updateOrCreate` overwrite to increment logic.  
**Verification:** Confirmed quantity now increments.

---

#### BUG-CART-002: Unreachable Cart Endpoints — ✅ FIXED
**File:** `routes/api.php`  
**Fix Applied:** Added `GET /cart/summary` and `DELETE /cart/clear`.  
**Verification:** Confirmed routes registered.

---

#### BUG-CART-003: Cart Stock Check Race Condition — 🟠 HIGH
**File:** `app/Modules/Sales/src/Services/CartService.php:34,61`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Set variant stock to 1
2. Two users simultaneously add that variant to cart
3. Both carts contain the item; only one can checkout

**Expected:** Cart addition should be atomic.
**Actual:** Stock checked via simple SELECT without locking.

**Fix:** Use `lockForUpdate()` when reading variant during cart addition, or re-validate atomically at checkout.

---

### 1.4 Checkout Process

#### BUG-CHKT-001: Guest Checkout Creates Duplicate Users — ✅ FIXED
**File:** `app/Services/GuestCheckoutService.php`  
**Fix Applied:** Added `User::where('email', ...)->orWhere('mobile', ...)->first()` check.  
**Verification:** Confirmed existing user check present.  
**Caution:** See BUG-AUTH-002 — returning existing user without password verification is a security risk.

---

#### BUG-CHKT-002: Shipping Cost Trusts Frontend Input — 🟡 MEDIUM
**File:** `app/Services/GuestCheckoutService.php:265-269`  
**Status:** 🔴 NEW

**Issue:** `calculateFinancials()` accepts `orderSummary['shippingCost']` from frontend. A negative shipping cost reduces the order total.

**Fix:** Always recalculate shipping server-side and ignore frontend-provided shipping costs.

---

#### BUG-CHKT-003: Guest Checkout Enforces Unique Email — 🟡 MEDIUM
**File:** `app/Http/Requests/GuestCheckoutRequest.php:19`  
**Status:** 🔴 NEW

**Issue:** `'guest.email' => ['required', 'email', 'unique:users,email']` prevents checkout with an existing email, creating poor UX and email enumeration vulnerability.

**Fix:** Remove `unique` validation. If email exists, silently link to existing user (after OTP verification) or redirect to login.

---

### 1.5 Order Placement & Confirmation

#### BUG-ORD-001: Order Tracking API Broken (ID vs Number) — ✅ FIXED
**File:** `app/Modules/Sales/src/Services/OrderService.php`  
**Fix Applied:** `getTrackingForUser()` added with proper ownership check.  
**Verification:** Confirmed present.

---

#### BUG-ORD-002: Order Tracking Information Disclosure — ✅ FIXED
**File:** `app/Modules/Sales/src/Services/OrderService.php`  
**Fix Applied:** `getTrackingForUser()` now verifies `user_id` ownership.  
**Verification:** Confirmed.

---

#### BUG-ORD-003: Tracking History Still Publicly Accessible — 🟡 MEDIUM
**File:** `app/Modules/Sales/src/Services/OrderService.php:207`  
**Status:** 🔴 NEW

**Issue:** `getTracking()` method (without user scoping) still exists and may be called from unauthenticated contexts.

**Fix:** Remove or protect `getTracking()` — all tracking must go through `getTrackingForUser()`.

---

#### BUG-ORD-004: Missing `completed` Status in Enum — ✅ FIXED
**File:** `app/Modules/Sales/src/Enums/OrderStatus.php`  
**Fix Applied:** Added `case COMPLETED = 'completed';`.  
**Verification:** Confirmed.

---

#### BUG-ORD-005: Order Number Collision Risk — 🟡 MEDIUM
**File:** `app/Modules/Sales/src/Services/OrderService.php:245`, `app/Services/GuestCheckoutService.php:285`  
**Status:** 🔴 NEW

**Issue:** Uses `substr(uniqid(), -6)` which is time-based. Under high load, collisions are possible. No retry loop.

**Fix:** Wrap creation in retry loop (max 3 attempts) regenerating order number on `QueryException` code 23000.

---

---

## 2. PAYMENT TESTING

### 2.1 Payment Callback Forgery — 🔴 CRITICAL
**Module:** Payment (Aamarpay)  
**File:** `app/Services/AamarPayService.php:154-197`, `app/Http/Controllers/Frontend/PaymentController.php:75-119`  
**Status:** 🔴 REMAINS OPEN — Architectural fix needed

**Steps to Reproduce:**
```bash
curl -X POST http://site.com/api/payment/aamarpay/success \
  -d "mer_txnid=ANY-ORDER-NUMBER" \
  -d "pay_status=Successful" \
  -d "amount=1.00"
```

**Expected:**
- Signature verified using `signature_key`
- Amount matched against order total
- Store ID validated
- Server-side API confirmation

**Actual:** Order marked paid with zero verification. Anyone can POST success for any order.

**Impact:** Direct financial fraud. Attackers obtain products without payment.

**Fix Priority: P0**
1. Verify callback signature using HMAC with `signature_key`
2. Call Aamarpay verify API to confirm transaction
3. Match `$data['amount']` against `$order->total_amount`
4. Add idempotency: `if ($order->payment_status === 'paid') return ['already_processed']`
5. Whitelist Aamarpay callback IPs

---

### 2.2 Payment Callback Cancel Endpoint Unverified — 🟠 HIGH
**Module:** Payment  
**File:** `app/Http/Controllers/Frontend/PaymentController.php:103-119`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
```bash
curl -X POST http://site.com/api/payment/aamarpay/cancel \
  -d "mer_txnid=ANY-ORDER"
```

**Expected:** Only Aamarpay should be able to cancel.
**Actual:** Any user can cancel any order via forged cancel callback.

**Fix:** Add signature verification and IP whitelist to cancel endpoint.

---

### 2.3 Missing Online Payment Methods — 🔴 CRITICAL
**Module:** Payment  
**Files:** `app/Modules/Sales/src/Enums/PaymentMethod.php`, `app/Services/AamarPayService.php`  
**Status:** 🔴 REMAINS OPEN

**Expected:** bKash, Nagad, Card, COD online integration.
**Actual:** Only Aamarpay (online) + COD implemented. bKash/Nagad/Card exist only as POS payment method labels with no actual gateway integration.

**Fix:** Integrate bKash Checkout API, Nagad Payment Gateway, SSLCommerz/Stripe for cards.

---

### 2.4 Payment Status Enum Mismatch — ✅ FIXED
**File:** `app/Http/Controllers/Frontend/PaymentController.php`  
**Fix Applied:** `PaymentStatus` enum usage corrected.  
**Verification:** Confirmed.

---

### 2.5 No Transaction Audit Trail — 🟡 MEDIUM
**Module:** Payment  
**Status:** 🔴 REMAINS OPEN

**Expected:** Dedicated `transactions` table with gateway responses.
**Actual:** Payment status stored directly on `orders` table only.

**Fix:** Create `transactions` table and log all payment attempts/responses.

---

### 2.6 Duplicate Payment Prevention — 🟡 MEDIUM
**Module:** Payment  
**File:** `app/Services/AamarPayService.php`  
**Status:** 🔴 REMAINS OPEN

**Expected:** Idempotency check prevents double-processing.
**Actual:** Success callback can be replayed multiple times.

**Fix:** Check `if ($order->payment_status === 'paid')` before updating.

---

### 2.7 COD Orders Can Hit Payment Initiate — 🟠 HIGH
**Module:** Payment  
**File:** `app/Http/Controllers/Frontend/PaymentController.php:27-70`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Place COD order
2. POST to `/orders/{orderId}/payment/initiate`
3. Controller attempts to create Aamarpay session

**Expected:** 422 error — COD orders don't need online payment.
**Actual:** Aamarpay session created unnecessarily.

**Fix:** Early-return with 422 if `$order->payment_method === 'cod'`.

---

### 2.8 Payment Amount Not Verified — 🟠 HIGH
**Module:** Payment  
**File:** `app/Services/AamarPayService.php:177-182`  
**Status:** 🔴 NEW

**Issue:** When `payStatus === 'Successful'`, order marked paid without comparing `$data['amount']` against `$order->total_amount`.

**Fix:** Add amount validation:
```php
if ((float) $data['amount'] !== (float) $order->total_amount) {
    return ['success' => false, 'message' => 'Amount mismatch'];
}
```

---

### 2.9 AamarPay `tran_id` Reuse Prevents Retries — 🟢 LOW
**Module:** Payment  
**File:** `app/Services/AamarPayService.php:42`  
**Status:** 🔴 NEW

**Issue:** Uses `order_number` as `tran_id`. Gateways often reject duplicate `tran_id`s, preventing payment retries.

**Fix:** Append retry suffix or use separate payment attempt ID.

---

---

## 3. UI/UX TESTING

### 3.1 POS System Issues

#### BUG-POS-001: POS Customer Search 500 Error — ✅ FIXED
**File:** `app/Http/Controllers/Admin/PosController.php`  
**Fix Applied:** Added `use App\Models\User;` import.  
**Verification:** Confirmed.

---

#### BUG-POS-002: POS Customer Search Returns All Users — 🟡 MEDIUM
**File:** `app/Http/Controllers/Admin/PosController.php:229-254`  
**Status:** 🔴 NEW

**Issue:** `searchCustomers()` queries ALL `User` rows without filtering by role (`customer`) or active status. Admins and inactive accounts may appear.

**Fix:** Add `->where('role', 'customer')->where('is_active', true)`.

---

#### BUG-POS-003: POS Barcode Query Returns Inactive Products — 🔴 CRITICAL
**File:** `app/Http/Controllers/Admin/PosController.php:96-99`  
**Status:** 🔴 NEW

**Issue:** Barcode/SKU query groups OR before AND:
```php
->where('barcode', $barcode)->orWhere('sku', $barcode)->where('is_active', true)
```
The `orWhere` binds loosely, so inactive products can match on barcode.

**Fix:** Wrap in closure:
```php
->where(function ($q) use ($barcode) {
    $q->where('barcode', $barcode)->orWhere('sku', $barcode);
})->where('is_active', true)
```

---

#### BUG-POS-004: POS Variant Barcode Scans Parent Product — 🔴 CRITICAL
**File:** `resources/views/admin/pos/index.blade.php` (JS), `app/Http/Controllers/Admin/PosController.php`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Scan a variant barcode
2. Controller returns product with `has_variants: false`
3. JS adds parent product to cart instead of variant
4. Stock not deducted from variant

**Fix:** Update `findByBarcode` to detect variants and return `variant_id` + `type: 'variant'`.

---

#### BUG-POS-005: POS Never Checks Stock Before Order — 🟠 HIGH
**File:** `app/Http/Controllers/Admin/PosController.php:152-194`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Add 1,000 units of a product with only 5 in stock
2. Click Pay → Complete
3. Order created successfully

**Expected:** Order rejected with "Insufficient stock".
**Actual:** No stock validation.

**Fix:** Inject `StockManagerService` and validate each item against real-time stock before creating order.

---

#### BUG-POS-006: POS Discount Not Reset After Order — 🟡 MEDIUM
**File:** `resources/views/admin/pos/index.blade.php` (JS `processPayment`)  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Complete order with discount
2. Start new order without refreshing
3. Previous discount still applies

**Fix:** Reset `appliedDiscount` on successful order creation.

---

#### BUG-POS-007: POS Receipt Null-Safe Operator Missing — 🟡 MEDIUM
**File:** `resources/views/admin/pos/receipt.blade.php`, `print.blade.php`  
**Status:** 🔴 NEW

**Issue:** `$order->user->name` used without null-safe operator. If cashier user is deleted, 500 error.

**Fix:** Use `$order->user?->name ?? 'Unknown'`.

---

### 3.2 Admin Panel Issues

#### BUG-ADMIN-001: Admin Product Detail Page Crashes — ✅ FIXED
**File:** `app/Http/Controllers/Admin/ProductController.php`  
**Fix Applied:** Injected `PricingService` in constructor.  
**Verification:** Confirmed.

---

#### BUG-ADMIN-002: Product Deletion Doesn't Guard Simple Products — 🔴 CRITICAL
**File:** `app/Http/Controllers/Admin/ProductController.php:259-276`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Create simple product
2. Place an order with it
3. Try to delete product
4. Deletion succeeds

**Expected:** Should block deletion if product has order items.
**Actual:** Only checks `variants()->whereHas('orderItems')`. Simple products bypass check.

**Fix:** Add `|| $product->orderItems()->exists()`.

---

#### BUG-ADMIN-003: Category Filter Logic Broken for Childless Parents — 🟠 HIGH
**File:** `app/Http/Controllers/Admin/ProductController.php:53-64`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Create parent category with no children
2. Assign product to it
3. Filter by that category
4. Product hidden

**Expected:** Product should appear.
**Actual:** Query looks at `sub_category_id` because `children()->count() > 0` is false.

**Fix:** Change condition to `if ($selectedCat->parent_id === null)`.

---

#### BUG-ADMIN-004: Image Upload Creates Full-Size "Thumbnails" — 🟠 HIGH
**File:** `app/Services/ImageUploadService.php:94-112`  
**Status:** 🔴 REMAINS OPEN

**Issue:** `createThumbnails()` stores full-size copies without actual resizing. Wastes storage and bandwidth.

**Fix:** Implement actual image resizing with Intervention Image or GD.

---

#### BUG-ADMIN-005: Dynamic Image Preview Remove Button Broken — 🟡 MEDIUM
**File:** `resources/views/admin/products/form.blade.php:1386-1404`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Click "Select Images" → pick an image
2. Click red × on preview
3. Nothing happens

**Fix:** Attach `onclick` handler in `reader.onload` callback.

---

#### BUG-ADMIN-006: Sale Schedule Hidden on Edit Load — 🟢 LOW
**File:** `resources/views/admin/products/form.blade.php`  
**Status:** 🔴 NEW

**Issue:** Sale schedule section starts hidden even when product has `sale_start_date` / `sale_end_date`.

**Fix:** Remove `hidden` class server-side when dates are present.

---

### 3.3 Responsive Design Issues

#### BUG-RESP-001: POS Body `overflow: hidden` Prevents Scrolling — 🟠 HIGH
**File:** `resources/views/admin/pos/index.blade.php`  
**Status:** 🔴 NEW

**Issue:** `body { overflow: hidden; }` prevents ALL scrolling. On tablets/small laptops, product grid or cart is cut off.

**Fix:** Remove from body; use `overflow-hidden` only on POS wrapper div.

---

#### BUG-RESP-002: POS Cart Panel Fixed Width — 🟡 MEDIUM
**File:** `resources/views/admin/pos/index.blade.php`  
**Status:** 🔴 NEW

**Issue:** Right cart panel is fixed `w-96`. On viewports < 768px, content is clipped.

**Fix:** Use responsive widths (`w-full md:w-96`) and stack vertically on mobile.

---

#### BUG-RESP-003: Admin Sidebar No Mobile Behavior — 🟡 MEDIUM
**File:** `resources/views/admin/layouts/master.blade.php`  
**Status:** 🔴 NEW

**Issue:** Sidebar always expanded (260px). No off-canvas/overlay on mobile.

**Fix:** Add mobile hamburger toggle with overlay sidebar.

---

---

## 4. PERFORMANCE TESTING

### 4.1 Image Thumbnails Not Resized — 🟠 HIGH
**File:** `app/Services/ImageUploadService.php`  
**Status:** 🔴 REMAINS OPEN

**Impact:** Full-size images served as "thumbnails" — excessive bandwidth, slow page loads.
**Fix:** Implement Intervention Image v3 for actual resizing.

---

### 4.2 N+1 Query Risk in Product Lists — 🟡 MEDIUM
**File:** `app/Services/ProductApiTransformer.php`  
**Status:** 🔴 REMAINS OPEN

**Issue:** `getRelatedProducts()`, `getVariantAttributes()` may trigger N+1 queries.
**Fix:** Add `with()` eager loading in repository queries.

---

### 4.3 No Caching on Product Search — 🟡 MEDIUM
**Module:** Product Search  
**Status:** 🔴 REMAINS OPEN

**Issue:** Every search hits database directly.
**Fix:** Implement Redis caching for popular queries (TTL: 5 minutes).

---

### 4.4 Database Connection in Development Mode — 🟢 LOW
**File:** `.env`  
**Status:** 🔴 REMAINS OPEN

**Observation:** `APP_DEBUG=true` persists. Stack traces expose sensitive config.
**Fix:** Set `APP_DEBUG=false` in production.

---

### 4.5 Category Edit Uses Eager-Load Count — 🟢 LOW
**File:** `resources/views/admin/categories/edit.blade.php:242`  
**Status:** 🔴 NEW

**Issue:** `$category->products->count()` loads all products into memory then counts in PHP.
**Fix:** Use `$category->products()->count()`.

---

---

## 5. SECURITY TESTING

### 5.1 Payment Callback Forgery — 🔴 CRITICAL
*(See Section 2.1 — BUG-AUTH-001)*

---

### 5.2 Guest Checkout Authentication Bypass — 🔴 CRITICAL
*(See Section 1.1 — BUG-AUTH-002)*

---

### 5.3 Admin Routes Lack Role Authorization — 🔴 CRITICAL
**Module:** Admin  
**File:** `routes/admin.php`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Register as a customer via API
2. Obtain JWT token
3. Access `GET /admin/dashboard` with token
4. Full admin access granted

**Expected:** 403 Forbidden for non-admin users.
**Actual:** 200 OK with full admin data. Any authenticated user can access admin panel.

**Fix:** Add `->middleware('role:admin')` to admin route group.

---

### 5.4 Admin Controllers Lack Read Authorization — 🔴 CRITICAL
**Files:**
- `app/Http/Controllers/Admin/UserController.php` (index, create, show, edit methods)
- `app/Http/Controllers/Admin/RoleController.php` (index, create, show, edit methods)
- `app/Http/Controllers/Admin/PermissionController.php` (index, create, show, edit methods)
- `app/Http/Controllers/Admin/InventoryController.php` (ALL methods)
- `app/Http/Controllers/Admin/OrderController.php` (ALL methods)
- `app/Http/Controllers/Admin/SettingController.php` (ALL methods)
- `app/Http/Controllers/Admin/DashboardController.php` (ALL methods)

**Status:** 🔴 NEW

**Issue:** `abort_unless(auth()->user()->isAdmin(), 403)` was added to some write methods in previous sprint, but:
- Read methods (index, show, edit) remain unprotected
- Inventory, Order, Settings, Dashboard controllers have ZERO authorization checks
- Any authenticated JWT (including customers) can view all orders, inventory, settings, and modify payment credentials

**Impact:** Complete admin panel compromise by any registered customer.

**Fix:** Add route-level `role:admin` middleware to `routes/admin.php` group.

---

### 5.5 Settings Controller Exposes Payment Credentials — 🔴 CRITICAL
**File:** `app/Http/Controllers/Admin/SettingController.php`  
**Status:** 🔴 NEW

**Issue:** Combined with lack of authorization:
- Any authenticated user can view `aamarpay_store_id`, `aamarpay_signature_key`
- Any authenticated user can update payment credentials
- Any authenticated user can send test SMS to arbitrary numbers (SMS bombing)
- Any authenticated user can upload files (logo/favicon)

**Fix:**
1. Add admin middleware immediately
2. Encrypt sensitive settings like `aamarpay_signature_key`
3. Restrict test SMS to admin-only

---

### 5.6 IDOR in Address Management — ✅ FIXED
**File:** `app/Modules/User/src/Services/AddressService.php`  
**Fix Applied:** Added `user_id` ownership checks on update/delete.  
**Verification:** Confirmed.

---

#### BUG-SEC-001: Address Default Setting IDOR Remains — 🟡 MEDIUM
**File:** `app/Modules/User/src/Services/AddressService.php:71-74`  
**Status:** 🔴 NEW

**Issue:** `setDefaultAddress()` does not verify address belongs to user before setting default.

**Fix:** Add ownership check: `$address = $this->repository->findById($addressId); if (!$address || $address->user_id !== $userId) throw new AuthorizationException(...)`.

---

### 5.7 Review Helpful Vote Manipulation — ✅ FIXED
**File:** `app/Http/Controllers/Frontend/ReviewController.php`  
**Fix Applied:** Added `Auth::check()` gate.  
**Verification:** Confirmed.

---

#### BUG-SEC-002: Review Helpful Vote Not Deduplicated — 🟡 MEDIUM
**File:** `app/Services/ReviewService.php:133-147`  
**Status:** 🔴 NEW

**Issue:** `markHelpful()` ignores `$userId` parameter. Users can vote unlimited times.

**Fix:** Accept `$userId`, create `review_helpful_votes` pivot table, check for existing vote.

---

### 5.8 Sensitive Business Data Exposure — 🟠 HIGH
**Module:** Product API  
**File:** `app/Services/ProductApiTransformer.php:117-124`  
**Status:** 🔴 REMAINS OPEN

**Exposed Fields:** `cost_price`, `wholesale_price`, `wholesale_percentage`

**Impact:** Competitors can calculate exact profit margins.

**Fix:** Remove these fields from public API transforms. Restrict to admin-only endpoints.

---

### 5.9 OTP Stored in Plaintext (Modular) — ✅ FIXED
**File:** `app/Modules/Auth/src/Repositories/OTPRepository.php`  
**Fix Applied:** Added `Hash::make()` in `createOTP()`.  
**Verification:** Confirmed.

---

#### BUG-SEC-003: OTP Verification Still Has Race Condition — 🟡 MEDIUM
**File:** `app/Modules/Auth/src/Repositories/OTPRepository.php:57-65`  
**Status:** 🔴 NEW

**Issue:** `revokeExistingOTPs()` updates `expires_at` without row locking. Concurrent requests can create multiple valid OTPs.

**Fix:** Use database transactions with `FOR UPDATE` lock.

---

### 5.10 XSS via Review Comments — 🟡 MEDIUM
**Module:** Reviews  
**File:** `app/Http/Controllers/Frontend/ReviewController.php:43-49`  
**Status:** 🔴 REMAINS OPEN

**Issue:** Review `comment` validated as `string|min:10|max:2000` with no HTML sanitization. Stored XSS possible if rendered raw.

**Fix:** Sanitize HTML (allow only `<b>`, `<i>`) or escape output in API responses.

---

### 5.11 Image Upload Extension Spoofing — 🟡 MEDIUM
**Module:** Media Upload  
**File:** `app/Services/ImageUploadService.php`  
**Status:** 🔴 REMAINS OPEN

**Issue:** `$extension = $file->getClientOriginalExtension()` — client-controlled.

**Fix:** Use `$file->guessExtension()` or `$file->getMimeType()` for validation.

---

### 5.12 SVG Upload XSS — 🟠 HIGH
**Module:** Admin Settings  
**File:** `app/Http/Controllers/Admin/SettingController.php:164-192`  
**Status:** 🔴 NEW

**Issue:** `uploadLogo()` accepts `gif,svg`; `uploadFavicon()` accepts `svg`. SVG can contain JavaScript → Stored XSS.

**Fix:** Disallow SVG or sanitize with `enshrined/svg-sanitize`.

---

### 5.13 Insecure JWT Cookie — ✅ FIXED
**File:** `app/Traits/JWTAuthTrait.php`  
**Fix Applied:** Changed from `secure: false` to `config('jwt.cookie_secure', true)`.  
**Verification:** Confirmed.

---

#### BUG-SEC-004: Cookie Secure Flag Still Defaults to False — 🟡 MEDIUM
**File:** `app/Traits/JWTAuthTrait.php:175`, `app/Http/Middleware/JWTRefreshMiddleware.php:122`  
**Status:** 🔴 NEW

**Issue:** `config('jwt.cookie_secure', false)` and `config('session.secure', false)` still default to `false`. If config key missing, falls back to insecure.

**Fix:** Set defaults to `true` or enforce `APP_ENV=production` checks.

---

### 5.14 Error Message Information Disclosure — 🟢 LOW
**Files:** Multiple controllers  
**Status:** 🔴 REMAINS OPEN

**Issue:** Exception messages returned directly to client (`$e->getMessage()`), exposing internal paths and DB details.

**Fix:** Return generic messages to clients. Log detailed errors server-side.

---

---

## 6. ORDER & INVENTORY

### 6.1 Stock Not Restored on Admin Cancellation — ✅ FIXED
**File:** `app/Http/Controllers/Admin/OrderController.php`  
**Fix Applied:** Added stock restoration when status changed to `cancelled`.  
**Verification:** Confirmed.

---

#### BUG-INV-001: Admin Can Cancel Delivered Orders (Stock Inflation) — 🔴 CRITICAL
**File:** `app/Http/Controllers/Admin/OrderController.php:120`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Mark order as delivered
2. Change status to cancelled
3. Stock restored even though customer received goods

**Expected:** Final states (`delivered`, `completed`, `cancelled`) should not allow arbitrary transitions.
**Actual:** No state machine validation.

**Fix:** Implement `OrderStatus::canTransitionTo()` method. Reject invalid transitions.

---

### 6.2 Admin Status Updates Don't Log History — ✅ FIXED
**File:** `app/Http/Controllers/Admin/OrderController.php`  
**Fix Applied:** Added `$order->addStatusHistory(...)` call.  
**Verification:** Confirmed.

---

### 6.3 Inventory Movement Logs Incorrect — ✅ FIXED
**File:** `app/Models/InventoryMovement.php`  
**Fix Applied:** `stock_after` now calculates based on movement type.  
**Verification:** Confirmed.

---

#### BUG-INV-002: Inventory Movement Negative Stock Not Clamped — 🟠 HIGH
**File:** `app/Models/InventoryMovement.php:146`  
**Status:** 🔴 NEW

**Issue:** `$stockAfter = $stockBefore + $quantity` without clamping. If `quantity` is negative and `stockBefore` is 0, log records `stock_after = -5` while actual variant stock is clamped to 0.

**Fix:** Calculate `$stockAfter = max(0, $stockBefore + $quantity)` or pass actual new stock value.

---

### 6.4 Courier Assignment Database Errors — ✅ FIXED (Partially)
**Files:** `app/Http/Controllers/Admin/OrderFulfillmentController.php`  
**Fix Applied:** Fixed `order_id`, `assigned_at` defaults.  
**Verification:** Confirmed.
**Caution:** Migration schema changes require `migrate:fresh` or manual ALTER on existing DBs.

---

#### BUG-INV-003: Order Fulfillment Lacks Status Transition Validation — 🟠 HIGH
**File:** `app/Http/Controllers/Admin/OrderFulfillmentController.php`  
**Status:** 🔴 NEW

**Issue:** Any order can be marked shipped/delivered regardless of state. A `cancelled` order can be shipped.

**Fix:** Add explicit state checks. Only `processing`/`packed` → `shipped`. Only `shipped` → `delivered`.

---

### 6.5 Race Condition in Stock Deduction — 🔴 CRITICAL
**File:** `app/Modules/Sales/src/Services/OrderService.php:104`, `app/Modules/Catalog/src/Repositories/VariantRepository.php:46`  
**Status:** 🔴 NEW

**Steps to Reproduce:**
1. Set variant stock to 1
2. Submit two checkout requests simultaneously
3. Both succeed; stock becomes -1

**Expected:** Only one order succeeds.
**Actual:** PHP read-modify-write without `lockForUpdate()`.

**Fix:** Use atomic decrement:
```php
$this->model->where('id', $variantId)
    ->where('stock_quantity', '>=', $quantity)
    ->decrement('stock_quantity', $quantity);
```
Then check affected rows === 1.

---

### 6.6 Missing Inventory Movement Logs for Online Orders — 🟠 HIGH
**File:** `app/Modules/Sales/src/Services/OrderService.php`, `app/Services/PosOrderService.php`  
**Status:** 🔴 NEW

**Issue:** Stock decrements for online orders and POS orders don't create `InventoryMovement` records.

**Fix:** Replace direct stock changes with `InventoryMovement::logMovement()` calls.

---

### 6.7 StockInController Non-Atomic Bulk Updates — 🟡 MEDIUM
**File:** `app/Http/Controllers/Admin/StockInController.php:32`  
**Status:** 🔴 NEW

**Issue:** Stock updated via PHP addition (`$variant->stock_quantity += $qty; $variant->save()`) instead of `->increment()`.

**Fix:** Use `$variant->increment('stock_quantity', $item['quantity'])`.

---

### 6.8 POS Orders Don't Log Inventory Movements — 🟡 MEDIUM
**File:** `app/Services/PosOrderService.php:51`  
**Status:** 🔴 NEW

**Issue:** POS stock deductions use `$variant->decrement()` without `InventoryMovement` records.

**Fix:** Call `InventoryMovement::logMovement()` for POS sales.

---

### 6.9 Double Transaction Nesting — 🟡 MEDIUM
**File:** `app/Services/GuestCheckoutService.php:31`, `app/Services/StockManagerService.php:24`  
**Status:** 🔴 NEW

**Issue:** Outer `DB::transaction()` + inner `DB::beginTransaction()` = brittle nested transactions.

**Fix:** Remove inner transaction. Rely on caller's transaction.

---

### 6.10 Product Stock Status Not Updated After Variant Changes — 🟡 MEDIUM
**File:** `app/Modules/Catalog/src/Repositories/VariantRepository.php:46,59`  
**Status:** 🔴 NEW

**Issue:** After modifying `stock_quantity`, `updateStockStatus()` is never called. Variant at 0 may still show `in_stock`.

**Fix:** Call `$variant->updateStockStatus()` after quantity changes.

---

### 6.11 Admin Stock Restoration Dead Code — 🟡 MEDIUM
**File:** `app/Http/Controllers/Admin/OrderController.php:136`  
**Status:** 🔴 NEW

**Issue:** Code checks `$item->product_id`, but `order_items` table has no `product_id` column. `elseif` branch is unreachable.

**Fix:** Remove dead branch or add `product_id` to `order_items`.

---

---

## 7. API TESTING

### 7.1 Inconsistent API Response Formats — 🟠 HIGH
**Files:** Multiple controllers  
**Status:** 🔴 REMAINS OPEN

**Observation:** Three different response patterns:
1. `App\Traits\ApiResponse` — `success()`, `error()`, `paginated()`
2. `App\Modules\Sales\Http\Controllers\Controller` — `successResponse()`, `paginatedResponse()`
3. `App\Modules\Core\Abstracts\BaseController` — `successResponse()`, `paginatedResponse()` with `meta.pagination`

**Fix:** Standardize on single trait or base controller.

---

### 7.2 API Documentation Mismatch (`/api/v1` removed) — 🟠 HIGH
**Files:** `API_DOCUMENTATION.md`, `POSTMAN_COLLECTION.json`  
**Status:** 🔴 NEW

**Issue:** Git commit removed `/v1` prefix from routes, but docs/Postman still reference `/api/v1/...`.

**Fix:** Update documentation and Postman collection to remove `/v1`.

---

### 7.3 Missing API Documentation (Swagger) — 🟡 MEDIUM
**Files:** `app/Http/Controllers/Api/*`, `app/Modules/*`  
**Status:** 🔴 REMAINS OPEN

**Issue:** L5-Swagger configured but only unused controllers have `@OA` annotations. Real API surface is undocumented.

**Fix:** Add `@OA` annotations to all public API controllers.

---

### 7.4 Duplicate Model System — 🟢 LOW
**Observation:** Two parallel `Order`, `Cart`, `CartItem`, `OrderItem`, `OrderStatusHistory` models exist.  
**Status:** ⚠️ ACKNOWLEDGED

**Risk:** Data inconsistency when both systems interact with same tables.
**Fix:** Consolidate to single model per entity (P3 priority).

---

### 7.5 Order Status History Table Name Mismatch — ✅ FIXED
**File:** `app/Modules/Sales/src/Models/OrderStatusHistory.php`  
**Fix Applied:** Changed `$table` from `order_status_histories` to `order_status_history`.  
**Verification:** Confirmed.

---

#### BUG-API-001: Module OrderStatusHistory Still Uses `created_by` — 🔴 CRITICAL
**File:** `app/Modules/Sales/src/Models/OrderStatusHistory.php`, `app/Modules/Sales/src/Services/OrderService.php`  
**Status:** 🔴 NEW

**Issue:** Migration renamed `created_by` → `changed_by`. Module model `$fillable` and `OrderService::updateStatus()` still use `created_by`, causing SQL errors.

**Fix:** Update fillable from `created_by` to `changed_by`, and update service method.

---

### 7.6 JWT Refresh Returns Token in Cookie Only — 🟠 HIGH
**File:** `app/Http/Controllers/Auth/JWTAuthController.php:143-195`  
**Status:** 🔴 NEW

**Issue:** `refresh()` sets HTTP-Only cookie but returns JSON with only `expires_in`. Mobile/SPA clients can't retrieve new token.

**Fix:** Include `access_token` and `refresh_token` in JSON response body.

---

### 7.7 OTP Refresh Token Generation Broken — 🔴 CRITICAL
**File:** `app/Http/Controllers/Api/Auth/OTPAuthController.php:151-161`  
**Status:** 🔴 NEW

**Issue:** `generateTokenForUser()` calls `$this->generateRefreshToken()` which relies on `auth()->user()`. But `JWTAuth::fromUser($user)` doesn't authenticate into session, so `auth()->user()` returns `null`.

**Impact:** Mobile OTP users cannot refresh sessions.

**Fix:** Explicitly set user on auth guard before generating refresh token.

---

### 7.8 Unhandled ModelNotFoundException — 🟡 MEDIUM
**File:** `app/Modules/Sales/src/Http/Controllers/OrderController.php`  
**Status:** 🔴 NEW

**Issue:** `show()` and `track()` throw `ModelNotFoundException` → 500 instead of 404.

**Fix:** Add try/catch or configure exception handler mapping.

---

### 7.9 Wishlist Missing Routes — 🟡 MEDIUM
**File:** `app/Modules/Sales/src/Http/Controllers/WishlistController.php`  
**Status:** 🔴 NEW

**Issue:** `check()` and `toggle()` exist but no routes registered.

**Fix:** Add routes for `/wishlist/{productId}/check` and `/wishlist/toggle`.

---

### 7.10 Currency Default Mismatch — 🟡 MEDIUM
**File:** `app/Modules/Sales/src/Services/OrderService.php:81`, `API_DOCUMENTATION.md:720`  
**Status:** 🔴 NEW

**Issue:** `config('app.currency', 'USD')` used but application uses BDT. Documentation claims USD.

**Fix:** Change default to `BDT`. Fix documentation.

---

### 7.11 NoColumn Whitelist on Product Sort — 🟢 LOW
**File:** `app/Http/Controllers/Api/ProductController.php:62-64`  
**Status:** 🔴 NEW

**Issue:** `sort_by` passed directly to `orderBy()`. Invalid columns throw 500.

**Fix:** Whitelist allowed columns.

---

### 7.12 Payment Callback Returns 400 for Valid Events — 🟢 LOW
**File:** `app/Http/Controllers/Frontend/PaymentController.php`  
**Status:** 🔴 NEW

**Issue:** `aamarPayFail` and `aamarPayCancel` return HTTP 400. These are valid business events, not malformed requests.

**Fix:** Return 200 with `{success: false, message: "..."}` to acknowledge callback.

---

---

## 8. CROSS-BROWSER TESTING

### 8.1 Alpine.js CDN Version Unpinned — 🟡 MEDIUM
**Observation:** Uses `alpinejs@3.x.x` from CDN.  
**Status:** 🔴 REMAINS OPEN

**Risk:** Breaking changes in future updates could break POS UI.
**Fix:** Pin to specific version: `alpinejs@3.14.3`.

---

### 8.2 Tailwind CSS from CDN — 🟡 MEDIUM
**Observation:** `cdn.tailwindcss.com` used.  
**Status:** 🔴 REMAINS OPEN

**Risk:** Slower loading, no purging, potential blocking.
**Fix:** Build Tailwind with PostCSS for production.

---

### 8.3 Global Drag/Drop Event Suppression — 🟢 LOW
**File:** `resources/views/admin/categories/create.blade.php`  
**Status:** 🔴 NEW

**Issue:** `preventDefaults` attached to `document.body`, breaking all native drag/drop.

**Fix:** Only prevent default on specific drop zones.

---

---

## 9. REGRESSION TESTING

### 9.1 Zero Test Coverage — 🔴 CRITICAL
**Observation:** Only 2-3 example test files exist.  
**Status:** 🔴 REMAINS OPEN

**Risk:** Any code change can break functionality without detection.
**Fix:** Add comprehensive test suite:
- Feature tests for all API endpoints
- Unit tests for services (Cart, Order, Payment)
- Browser tests for critical checkout flows

---

### 9.2 POS Session Removal — ✅ VERIFIED
**Observation:** POS session feature fully removed.  
**Status:** ✅ CLEAN

**Verification:** `pos_session_id` removed from `PosOrder`. Migrations, models, views, routes all cleaned.

---

### 9.3 POS Held Cart Removal — ✅ VERIFIED
**Observation:** Held cart feature fully removed.  
**Status:** ✅ CLEAN

**Verification:** Model, migration, routes, and modal HTML all removed.

---

### 9.4 Duplicate Auth Architecture — ⚠️ PARTIALLY ADDRESSED
**File:** `routes/api.php`  
**Status:** ⚠️ PARTIALLY FIXED

**Previous State:** Two parallel auth systems with inconsistent behavior.  
**Current State:** Missing routes (`/cart/summary`, `/cart/clear`) added. Login throttle added. But `/api/auth/login` and `/api/v1/auth/login` still both exist with different controllers.

**Fix:** Consolidate to single auth system. Deprecate legacy routes.

---

### 9.5 Frontend Controllers Missing — 🟡 MEDIUM
**File:** `routes/web.php`  
**Status:** 🔴 NEW

**Observation:** Only `/` returning Laravel welcome page. No storefront Blade controllers.

**Fix:** Either implement Blade storefront or document that frontend is separate SPA.

---

---

## 10. RECOMMENDED FIX PRIORITY MATRIX

### P0 — Fix IMMEDIATELY (Before ANY Production Deploy)

| ID | Issue | File | Effort |
|----|-------|------|--------|
| PAY-001 | Payment callback signature verification | `AamarPayService.php` | Medium |
| AUTHZ-001 | Admin route authorization | `routes/admin.php` | Low |
| AUTH-002 | Guest checkout auth bypass | `GuestCheckoutService.php` | Low |
| INV-001 | Cancel delivered orders | `Admin/OrderController.php` | Medium |
| INV-005 | Stock race condition | `OrderService.php`, `VariantRepository.php` | Medium |
| API-001 | Module OrderStatusHistory `created_by` vs `changed_by` | `OrderStatusHistory.php` | Low |
| AUTH-003 | Password reset missing | Multiple | Medium |

### P1 — Fix Within 1 Week

| ID | Issue | File | Effort |
|----|-------|------|--------|
| AUTH-005 | Rate limiting OTP/guest | `routes/api.php` | Low |
| AUTH-004 | Weak password policy | `JWTAuthController.php` | Low |
| AUTH-008 | Refresh token type validation | `JWTAuthTrait.php` | Low |
| SEC-002 | Review vote deduplication | `ReviewService.php` | Medium |
| PROD-005 | Category `is_active` checkbox | `CategoryController.php` | Low |
| PROD-006 | Category orphan subcategories | `CategoryController.php` | Low |
| POS-003 | POS barcode inactive products | `PosController.php` | Low |
| POS-004 | POS variant barcode scan | `PosController.php` + JS | Medium |
| POS-005 | POS stock validation | `PosController.php` | Medium |
| PAY-002 | Cancel callback verification | `PaymentController.php` | Low |
| PAY-007 | COD payment initiate block | `PaymentController.php` | Low |
| DATA-001 | Remove cost_price from API | `ProductApiTransformer.php` | Low |

### P2 — Fix Within 2 Weeks

| ID | Issue | File | Effort |
|----|-------|------|--------|
| SEC-001 | Address default IDOR | `AddressService.php` | Low |
| AUTH-007 | OTP logged plaintext | `SendOTPJob.php` | Low |
| SEC-003 | OTP race condition | `OTPRepository.php` | Low |
| PROD-003 | Hardcoded attribute IDs | `ProductSearchService.php` | Medium |
| PROD-007 | Price filter ignores sale | `ProductSearchService.php` | Medium |
| CART-003 | Cart stock race | `CartService.php` | Low |
| INV-002 | InventoryMovement clamp | `InventoryMovement.php` | Low |
| INV-003 | Fulfillment state machine | `OrderFulfillmentController.php` | Medium |
| INV-006 | Missing movement logs | `OrderService.php`, `PosOrderService.php` | Medium |
| API-002 | API response standardization | Multiple | High |
| API-003 | Docs/Postman update | `API_DOCUMENTATION.md` | Medium |
| API-006 | JWT refresh JSON body | `JWTAuthController.php` | Low |
| API-007 | OTP refresh token fix | `OTPAuthController.php` | Low |
| IMG-001 | Actual image resizing | `ImageUploadService.php` | Medium |
| RESP-001 | POS overflow hidden | `pos/index.blade.php` | Low |

### P3 — Fix Within 1 Month

| ID | Issue | File | Effort |
|----|-------|------|--------|
| PROD-008 | Category self-parent | `CategoryController.php` | Low |
| CHKT-002 | Shipping cost trust frontend | `GuestCheckoutService.php` | Low |
| CHKT-003 | Guest checkout unique email | `GuestCheckoutRequest.php` | Low |
| ORD-005 | Order number collision | `OrderService.php` | Low |
| PAY-003 | Missing bKash/Nagad/Card | Multiple | High |
| PAY-005 | Transaction audit trail | New migration | Medium |
| SEC-004 | Cookie secure default | `JWTAuthTrait.php` | Low |
| XSS-001 | Review sanitization | `ReviewController.php` | Low |
| SVG-001 | SVG upload XSS | `SettingController.php` | Low |
| PERF-001 | Redis caching | Multiple | Medium |
| TEST-001 | Test coverage | New files | High |
| MODEL-001 | Consolidate duplicate models | Multiple | High |

---

## APPENDIX A: Quick Reference — Fixed in Previous Sprint

| Original Bug | Status | Evidence |
|--------------|--------|----------|
| `allProducts()` returns ALL products | ✅ Fixed | `where()` closure added |
| Missing `User` import in POS | ✅ Fixed | Import added |
| Missing `Str` import | ✅ Fixed | Import added |
| Missing `PricingService` injection | ✅ Fixed | Constructor updated |
| `stock_after` = `stock_before` | ✅ Fixed | Calculation by movement type |
| OTP plaintext storage | ✅ Fixed | `Hash::make()` added |
| Empty `SendOTPJob` | ✅ Fixed | SMS dispatch implemented |
| IDOR in Address CRUD | ✅ Fixed | `user_id` checks added |
| Review helpful unauthenticated | ✅ Fixed | `Auth::check()` added |
| Order tracking leaks all orders | ✅ Fixed | `getTrackingForUser()` added |
| Courier assignment DB errors | ✅ Fixed | `order_id`, `assigned_at` fixed |
| `total_stock` fillable conflict | ✅ Fixed | Removed from `$fillable` |
| Duplicate auth (missing routes) | ✅ Fixed | `/cart/summary`, `/cart/clear` added |
| Order status history table name | ✅ Fixed | `$table` corrected |
| Cart overwrites quantity | ✅ Fixed | Increment logic added |
| Insecure JWT cookie | ✅ Fixed | `config('jwt.cookie_secure')` used |
| Product search obsolete `status` | ✅ Fixed | Filter removed |
| Related products obsolete `status` | ✅ Fixed | Filter removed |
| Guest checkout duplicates | ✅ Fixed | Existing user check added |
| Order tracking ID vs number | ✅ Fixed | Dual lookup implemented |
| Missing `completed` enum | ✅ Fixed | `COMPLETED` added |
| Admin cancel restores stock | ✅ Fixed | Stock restoration added |
| Low stock out-of-stock variants | ✅ Fixed | `where('stock_quantity', '>', 0)` added |
| Stock-in missing movement logs | ✅ Fixed | `InventoryMovement::logMovement()` added |
| Unreachable cart endpoints | ✅ Fixed | Routes registered |
| Original price bug | ✅ Fixed | `compare_price ?? unit_price` used |

---

## APPENDIX B: Files with Most Issues

| File | Open Issues | Severities |
|------|-------------|------------|
| `app/Services/AamarPayService.php` | 4 | Critical ×2, High ×2 |
| `app/Http/Controllers/Admin/PosController.php` | 5 | Critical ×2, High ×1, Medium ×2 |
| `routes/admin.php` | 1 | Critical ×1 (affects ALL admin routes) |
| `app/Http/Controllers/Admin/OrderController.php` | 3 | Critical ×2, Medium ×1 |
| `app/Services/GuestCheckoutService.php` | 3 | Critical ×1, Medium ×2 |
| `app/Http/Controllers/Admin/SettingController.php` | 1 | Critical ×1 |
| `app/Http/Controllers/Admin/InventoryController.php` | 1 | Critical ×1 |
| `app/Modules/Sales/src/Services/OrderService.php` | 4 | Critical ×1, High ×2, Medium ×1 |
| `app/Http/Controllers/Admin/CategoryController.php` | 4 | High ×2, Medium ×2 |
| `app/Http/Controllers/Auth/JWTAuthController.php` | 2 | High ×2 |
| `app/Traits/JWTAuthTrait.php` | 2 | High ×1, Medium ×1 |
| `app/Services/ProductApiTransformer.php` | 1 | High ×1 |
| `app/Http/Controllers/Frontend/PaymentController.php` | 3 | High ×2, Low ×1 |

---

*Report generated by comprehensive automated code analysis + manual review.*  
*Previous sprint fixes verified against git history and file contents.*  
*Total issues identified: 110 (19 Critical, 27 High, 40 Medium, 24 Low)*  
*Issues fixed in previous sprint: 42 (14 Critical, 15 High, 8 Medium, 5 Low)*

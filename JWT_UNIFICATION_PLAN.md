# JWT Authentication Unification Plan

## Executive Summary
Unify Web and API authentication to use JWT (JSON Web Tokens) as the single authentication mechanism for both interfaces. Remove Laravel's session-based authentication entirely.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    Unified JWT Authentication                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│   ┌──────────────┐          JWT Token         ┌──────────────┐  │
│   │  Web Browser │  ◄──────────────────────►  │  API Client  │  │
│   └──────┬───────┘                            └──────┬───────┘  │
│          │                                          │            │
│          │  HTTP-Only Cookie OR Authorization Header │            │
│          │                                          │            │
│          └──────────────────┬───────────────────────┘            │
│                             │                                    │
│                             ▼                                    │
│   ┌──────────────────────────────────────────────────────────┐  │
│   │              Laravel Application (Unified)                │  │
│   │  ┌─────────────────────────────────────────────────────┐ │  │
│   │  │  Middleware: jwt.auth (Both Web & API)              │ │  │
│   │  │  Guard: jwt (Single guard for all routes)           │ │  │
│   │  └─────────────────────────────────────────────────────┘ │  │
│   └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## Implementation Phases

### Phase 1: Configuration & Setup (Low Risk)
**Estimated Time: 30 minutes**

1. **Update `config/auth.php`**
   - Add `jwt` guard for both web and api
   - Set `jwt` as default guard
   - Remove `web` session guard

2. **Update `config/jwt.php`**
   - Configure cookie settings for web
   - Set appropriate TTL values

3. **Create JWT Middleware for Web**
   - Create `app/Http/Middleware/JWTWebAuth.php`
   - Handle cookie-based token extraction
   - Redirect to login on token expiration

4. **Environment Variables**
   ```env
   AUTH_GUARD=jwt
   JWT_TTL=120  # 2 hours for web
   JWT_REFRESH_TTL=10080  # 7 days
   ```

**Deliverable:** Configuration files updated, no breaking changes yet.

---

### Phase 2: Web Authentication Controllers (Medium Risk)
**Estimated Time: 1-2 hours**

1. **Create Unified Auth Controller**
   - `app/Http/Controllers/Auth/JWTAuthController.php`
   - Handles both web and API login/logout
   - Returns tokens for API, sets cookies for web

2. **Update Web Login Flow**
   ```
   Login Form → POST /auth/login → Validate → Generate JWT
                                          ↓
                                    Web: Set HTTP-Only Cookie
                                    API: Return JSON Response
   ```

3. **Update Login View**
   - `resources/views/admin/auth/login.blade.php`
   - Keep existing form, just change form action

4. **Create Token Refresh Mechanism**
   - Automatic refresh before expiration
   - Background refresh for SPA-like experience

**Deliverable:** Web authentication works with JWT instead of sessions.

---

### Phase 3: Route Updates (Medium Risk)
**Estimated Time: 1 hour**

1. **Update `routes/web.php`**
   - Change middleware from `auth` to `jwt.auth`
   - Update route group configurations

2. **Update `routes/api.php`**
   - Change middleware from `auth:api` to `jwt.auth`
   - Ensure consistency

3. **Route Service Provider**
   - Configure default middleware groups
   - Remove session-based middleware from web routes

**Deliverable:** All routes protected by unified JWT middleware.

---

### Phase 4: Frontend Integration (Medium Risk)
**Estimated Time: 2-3 hours**

1. **Update Master Layout**
   - `resources/views/admin/layouts/master.blade.php`
   - Add meta tag with token for JavaScript access
   - CSRF token remains for forms

2. **Create JavaScript Auth Helper**
   ```javascript
   // public/js/auth.js
   // Handle token storage, refresh, and API calls
   ```

3. **Update AJAX Calls**
   - All admin panel AJAX calls include JWT token
   - Handle 401 errors with automatic redirect to login

4. **Logout Button**
   - Clear cookie on logout
   - Redirect to login page

**Deliverable:** Web interface fully functional with JWT.

---

### Phase 5: Session Cleanup (High Risk)
**Estimated Time: 1 hour**

1. **Remove Session Middleware**
   - From `app/Http/Kernel.php`
   - Remove `StartSession`, `ShareErrorsFromSession` if not needed

2. **Update Exception Handler**
   - `app/Exceptions/Handler.php`
   - Handle JWT exceptions differently for web vs API

3. **Clear Session Dependencies**
   - Remove session table migrations (optional)
   - Update config files

**Deliverable:** Session authentication completely removed.

---

### Phase 6: Testing & Validation (Critical)
**Estimated Time: 2-3 hours**

1. **Web Authentication Tests**
   - Login/logout flow
   - Token expiration handling
   - Page access with/without token

2. **API Authentication Tests**
   - All existing API endpoints
   - Token refresh mechanism
   - Mobile app compatibility

3. **Cross-Compatibility Tests**
   - Login on web, use token for API
   - Login via API, access web (if needed)

**Deliverable:** All tests passing, system stable.

---

## Detailed Technical Implementation

### 1. Configuration Changes

**config/auth.php:**
```php
'defaults' => [
    'guard' => 'jwt',
    'passwords' => 'users',
],

'guards' => [
    'jwt' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### 2. Token Storage Strategy

| Interface | Storage Method | Transport |
|-----------|---------------|-----------|
| **Web** | HTTP-Only Cookie | Automatic (Cookie header) |
| **API** | Client Storage (localStorage/Keychain) | Authorization Header |

**Why HTTP-Only Cookie for Web?**
- ✅ Protected from XSS attacks
- ✅ Automatic handling (no JS needed)
- ✅ Works with server-side rendering
- ✅ CSRF protection can still be applied

### 3. Middleware Logic

```php
// JWTWebAuth Middleware
public function handle($request, $next)
{
    // Try cookie first (web)
    $token = $request->cookie('jwt_token');
    
    // Fall back to header (API)
    if (!$token) {
        $token = $this->extractTokenFromHeader($request);
    }
    
    if (!$token || !$this->validateToken($token)) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return redirect()->route('login');
    }
    
    return $next($request);
}
```

### 4. Login Response Handler

```php
public function login(Request $request)
{
    // ... validation and authentication logic
    $token = auth()->login($user);
    
    if ($request->expectsJson()) {
        // API response
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
    
    // Web response with cookie
    return redirect()
        ->route('admin.dashboard')
        ->cookie('jwt_token', $token, 120, null, null, true, true); // httpOnly, secure
}
```

---

## File Changes Summary

### New Files to Create:
1. `app/Http/Middleware/JWTWebAuth.php`
2. `app/Http/Controllers/Auth/JWTAuthController.php`
3. `app/Http/Controllers/Auth/TokenController.php` (refresh/revoke)
4. `resources/views/admin/auth/login.blade.php` (update)
5. `public/js/jwt-auth.js`

### Files to Modify:
1. `config/auth.php`
2. `config/jwt.php`
3. `routes/web.php`
4. `routes/api.php`
5. `app/Http/Kernel.php`
6. `app/Exceptions/Handler.php`
7. `resources/views/admin/layouts/master.blade.php`

### Files to Remove/Deprecate:
1. `app/Http/Controllers/Admin/AuthController.php` (old session-based)
2. Session-related middleware bindings

---

## Security Considerations

### ✅ Advantages of JWT Unification:
1. **Single Source of Truth** - One auth mechanism
2. **Stateless** - No server-side session storage
3. **Scalability** - Easy to scale horizontally
4. **Cross-Domain** - Works across subdomains easily
5. **Mobile-Ready** - Native mobile apps work seamlessly

### ⚠️ Security Measures to Implement:
1. **HTTP-Only Cookies** - Prevent XSS token theft
2. **Secure Flag** - HTTPS only transmission
3. **SameSite Attribute** - CSRF protection
4. **Short TTL** - 2 hours for access tokens
5. **Refresh Tokens** - 7-day refresh window
6. **Token Blacklist** - For logout functionality
7. **Rate Limiting** - Prevent brute force

---

## Rollback Plan

If issues occur, we can quickly revert:

1. **Restore config/auth.php** - Switch back to `web` guard
2. **Revert routes** - Change middleware back to `auth`
3. **Keep JWT for API** - API continues working
4. **Web falls back** - Session-based auth restored

**Estimated Rollback Time:** 15 minutes

---

## Timeline Summary

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Phase 1: Configuration | 30 min | 30 min |
| Phase 2: Controllers | 1-2 hours | 2-3 hours |
| Phase 3: Routes | 1 hour | 3-4 hours |
| Phase 4: Frontend | 2-3 hours | 5-7 hours |
| Phase 5: Cleanup | 1 hour | 6-8 hours |
| Phase 6: Testing | 2-3 hours | 8-11 hours |

**Total Estimated Time:** 8-11 hours (can be split across multiple sessions)

---

## Success Criteria

✅ Web login uses JWT instead of sessions  
✅ API login continues working as before  
✅ Same JWT token works for both web and API  
✅ Token refresh works seamlessly  
✅ Logout clears tokens properly  
✅ No session table dependencies  
✅ All existing functionality preserved  
✅ Mobile apps unaffected  

---

## Questions to Consider Before Implementation

1. **Do you want to keep session support for any specific feature?** (e.g., flash messages)
2. **Should web tokens be shorter-lived than API tokens?**
3. **Do you need "Remember Me" functionality for web?**
4. **Should we implement automatic token refresh on the web?**
5. **Any third-party integrations that rely on session auth?**

---

**Ready to proceed?** Please confirm and I'll start with Phase 1 implementation.

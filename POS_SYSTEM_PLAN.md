# POS System Implementation Plan
## E-Commerce Admin Panel

---

## 1. Executive Summary

A **Point of Sale (POS)** system for the admin panel to handle in-store/physical sales. This will be a fast, keyboard-friendly interface designed for cashiers to quickly process orders.

---

## 2. Core Features

### A. Product Management
| Feature | Description |
|---------|-------------|
| **Quick Search** | Real-time product search by name, SKU, barcode |
| **Barcode Scanning** | Support for USB barcode scanners |
| **Category Filter** | Quick category tabs for browsing |
| **Product Grid** | Visual grid with images, price, stock status |
| **Variants Support** | Select size/color for variant products |

### B. Cart Management
| Feature | Description |
|---------|-------------|
| **Add/Remove Items** | One-click add, quantity adjustment |
| **Cart Persistence** | Auto-save cart during session |
| **Hold Cart** | Put cart on hold, serve next customer |
| **Retrieve Cart** | Resume held carts |
| **Clear Cart** | Quick clear for new customer |

### C. Customer Management
| Feature | Description |
|---------|-------------|
| **Walk-in Customer** | Default guest checkout |
| **Search Customer** | Find existing customers by phone/email |
| **Quick Add Customer** | Create new customer in 2 clicks |
| **Customer History** | View previous purchases |

### D. Payment Processing
| Feature | Description |
|---------|-------------|
| **Multiple Payment Methods** | Cash, Card, Mobile Banking, Mixed |
| **Split Payment** | Pay with multiple methods (e.g., Cash + Card) |
| **Cash Calculator** | Auto-calculate change |
| **Discount Application** | Apply coupons or manual discount |
| **Tax Handling** | Automatic tax calculation |

### E. Order Completion
| Feature | Description |
|---------|-------------|
| **Invoice Generation** | Print/email invoice |
| **Stock Deduction** | Real-time inventory update |
| **Receipt Printing** | Thermal printer support |
| **Order Sync** | Sync with online order system |

---

## 3. Database Schema

### New Tables

```sql
-- POS Sessions (Cash register sessions)
CREATE TABLE pos_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    opening_amount DECIMAL(12,2) NOT NULL,
    closing_amount DECIMAL(12,2) NULL,
    cash_sales DECIMAL(12,2) DEFAULT 0,
    card_sales DECIMAL(12,2) DEFAULT 0,
    other_sales DECIMAL(12,2) DEFAULT 0,
    status ENUM('active', 'closed') DEFAULT 'active',
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- POS Orders (Separate from online orders)
CREATE TABLE pos_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    pos_session_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(255) NULL,
    customer_phone VARCHAR(20) NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    discount_amount DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) NOT NULL,
    status ENUM('completed', 'refunded', 'cancelled') DEFAULT 'completed',
    note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- POS Order Items
CREATE TABLE pos_order_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pos_order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    variant_id BIGINT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(12,2) NOT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- POS Payments
CREATE TABLE pos_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pos_order_id BIGINT UNSIGNED NOT NULL,
    payment_method ENUM('cash', 'card', 'mobile_banking', 'other') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    reference_number VARCHAR(100) NULL,
    received_amount DECIMAL(12,2) NULL, -- For cash payments
    change_amount DECIMAL(12,2) NULL, -- For cash payments
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Held Carts (For holding and resuming sales)
CREATE TABLE pos_held_carts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    customer_id BIGINT UNSIGNED NULL,
    customer_name VARCHAR(255) NULL,
    cart_data JSON NOT NULL,
    note VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 4. UI/UX Design

### Layout (Split Screen)
```
+----------------------------------------------------------+
|  POS - Order #POS-001          [User: John]     [Close X] |
+----------------------------------------------------------+
|  +------------------------+  +-------------------------+ |
|  |   PRODUCT BROWSER      |  |      CART PANEL         | |
|  |  +------------------+  |  |                         | |
|  |  | Search/Barcode   |  |  |  Customer: Walk-in      | |
|  |  +------------------+  |  |  [Change Customer]      | |
|  |                        |  |                         | |
|  |  [All][Tops][Bottoms]  |  |  ---------------------   | |
|  |  +------------------+  |  |  Product A    2   $40   | |
|  |  | Product Grid     |  |  |  Product B    1   $25   | |
|  |  | [Img] $20        |  |  |  ---------------------   | |
|  |  | [Img] $25        |  |  |  Subtotal:      $65.00  | |
|  |  | [Img] $30        |  |  |  Discount:      -$5.00  | |
|  |  +------------------+  |  |  Tax:           $3.00   | |
|  |                        |  |  TOTAL:         $63.00  | |
|  +------------------------+  |                         | |
|                              |  [Hold] [Clear] [Pay]   | |
|                              +-------------------------+ |
+----------------------------------------------------------+
```

### Keyboard Shortcuts
| Key | Action |
|-----|--------|
| `F1` | Focus search box |
| `F2` | Add new customer |
| `F3` | Hold cart |
| `F4` | Retrieve held cart |
| `F5` | Clear cart |
| `F9` | Open payment modal |
| `+` | Increase quantity |
| `-` | Decrease quantity |
| `Del` | Remove item |
| `Ctrl+P` | Print last receipt |

---

## 5. API Endpoints

```php
// POS Routes (protected by jwt.auth)
Route::prefix('pos')->group(function () {
    // Session Management
    Route::post('/session/open', [PosController::class, 'openSession']);
    Route::post('/session/close', [PosController::class, 'closeSession']);
    Route::get('/session/current', [PosController::class, 'getCurrentSession']);
    
    // Product Search
    Route::get('/products/search', [PosController::class, 'searchProducts']);
    Route::get('/products/barcode/{barcode}', [PosController::class, 'findByBarcode']);
    
    // Cart Operations
    Route::post('/cart/hold', [PosController::class, 'holdCart']);
    Route::get('/cart/held', [PosController::class, 'getHeldCarts']);
    Route::post('/cart/retrieve/{id}', [PosController::class, 'retrieveCart']);
    Route::delete('/cart/held/{id}', [PosController::class, 'deleteHeldCart']);
    
    // Order Processing
    Route::post('/order', [PosController::class, 'createOrder']);
    Route::get('/order/{id}/receipt', [PosController::class, 'printReceipt']);
    Route::post('/order/{id}/refund', [PosController::class, 'processRefund']);
    
    // Reports
    Route::get('/reports/daily', [PosController::class, 'dailyReport']);
    Route::get('/reports/session/{id}', [PosController::class, 'sessionReport']);
});
```

---

## 6. Implementation Phases

### Phase 1: Core Infrastructure (Day 1-2)
- [ ] Database migrations
- [ ] Models and relationships
- [ ] Basic controller structure
- [ ] Routes setup

### Phase 2: Product Browser (Day 3-4)
- [ ] Product search API
- [ ] Barcode scanning integration
- [ ] Product grid UI
- [ ] Category filtering
- [ ] Variant selection modal

### Phase 3: Cart System (Day 5-6)
- [ ] Cart state management (Vue.js/Alpine.js)
- [ ] Add/remove items
- [ ] Quantity adjustments
- [ ] Hold/retrieve cart functionality
- [ ] Local storage persistence

### Phase 4: Payment & Checkout (Day 7-8)
- [ ] Payment modal
- [ ] Multiple payment methods
- [ ] Cash calculator
- [ ] Discount application
- [ ] Order completion
- [ ] Invoice generation

### Phase 5: Session Management (Day 9)
- [ ] Open/close register
- [ ] Cash tracking
- [ ] Session reports

### Phase 6: Polish & Testing (Day 10)
- [ ] Keyboard shortcuts
- [ ] Thermal printing
- [ ] Responsive design
- [ ] Testing & bug fixes

---

## 7. Technical Stack

| Component | Technology |
|-----------|------------|
| **Frontend** | Blade + Alpine.js (lightweight reactivity) |
| **State Management** | Alpine.js store / Pinia |
| **Real-time Updates** | Laravel Echo + Pusher (optional) |
| **Printing** | Browser print API / Thermal printer driver |
| **Barcode** | HTML5 Barcode API or external scanner |
| **Styling** | Tailwind CSS |

---

## 8. Security Considerations

1. **Role-based access** - Only users with 'pos' permission
2. **Session validation** - Every transaction linked to active session
3. **Cash reconciliation** - Force closeout with cash count
4. **Audit trail** - All actions logged
5. **Refund authorization** - Require supervisor PIN for refunds

---

## 9. Integration Points

| System | Integration |
|--------|-------------|
| **Products** | Use existing product catalog |
| **Inventory** | Deduct stock on sale |
| **Customers** | Use existing customer database |
| **Orders** | POS orders visible in main order list (flagged) |
| **Reports** | Include POS sales in sales reports |

---

## 10. Success Metrics

- Product search < 500ms
- Cart operations < 100ms
- Complete checkout in < 30 seconds
- Support for 1000+ products
- Works offline (queue sync)

---

## Questions for You:

1. **Payment Methods**: Which specific payment methods? (Cash, Card, bKash, Nagad, etc.)
2. **Printing**: Do you need thermal printer integration or just browser print?
3. **Barcode**: Will you use USB barcode scanners or manual entry?
4. **Offline Mode**: Should it work without internet and sync later?
5. **Multi-cashier**: Multiple cashiers on same register?
6. **Currency**: All prices in BDT (৳)?

Once you confirm, I'll start Phase 1 development immediately.

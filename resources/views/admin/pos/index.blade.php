@extends('admin.layouts.master')

@section('title', 'POS System')
@section('page-title', 'Point of Sale')

@section('content')
<div class="h-screen flex flex-col" x-data="posSystem()" x-init="init()" @keydown.window="handleKeydown($event)">
    <!-- Top Bar -->
    <div class="bg-gray-900 text-white px-4 py-2 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <h1 class="text-lg font-bold"><i class="fas fa-cash-register mr-2"></i>POS System</h1>
            <span class="text-gray-400">|</span>
            <span class="text-sm text-gray-300" x-text="currentTime"></span>
        </div>
        <div class="flex items-center gap-3">
            <button @click="isWholesale = !isWholesale" 
                class="px-3 py-1 rounded text-sm font-medium transition-colors"
                :class="isWholesale ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-300'">
                <i class="fas fa-warehouse mr-1"></i>
                <span x-text="isWholesale ? 'Wholesale' : 'Retail'"></span>
            </button>

        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Panel - Product Browser -->
        <div class="flex-1 flex flex-col bg-gray-100">
            <!-- Search Bar -->
            <div class="bg-white p-3 shadow">
                <div class="flex gap-2">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                            @keydown.enter="handleBarcodeSearch()"
                            placeholder="Search products or scan barcode (F1)"
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            id="searchInput">
                    </div>
                    <select x-model="selectedCategory" @change="searchProducts()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Loading State -->
                <div x-show="loading" class="text-center py-10">
                    <i class="fas fa-spinner fa-spin text-3xl text-blue-600"></i>
                    <p class="mt-2 text-gray-500">Loading products...</p>
                </div>
                
                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3" x-show="!loading">
                    <template x-for="product in products" :key="product.id">
                        <div @click="addToCart(product)"
                            class="bg-white rounded-lg shadow hover:shadow-md cursor-pointer transition-all border-2 hover:border-blue-500 overflow-hidden"
                            :class="{ 'opacity-50': product.stock <= 0 }">
                            <div class="aspect-square bg-gray-200 flex items-center justify-center">
                                <img x-show="product.image" :src="product.image" class="w-full h-full object-cover">
                                <i x-show="!product.image" class="fas fa-box text-gray-400 text-3xl"></i>
                            </div>
                            <div class="p-2">
                                <h3 class="font-medium text-sm truncate" x-text="product.name"></h3>
                                <div class="flex justify-between items-center mt-1">
                                    <div>
                                        <span class="text-blue-600 font-bold">৳<span x-text="formatPrice(product.wholesale_price && isWholesale ? product.wholesale_price : product.price)"></span></span>
                                        <span x-show="product.wholesale_price && isWholesale" class="text-xs text-green-600 ml-1 font-medium">WS</span>
                                    </div>
                                    <span class="text-xs text-gray-500" x-text="product.stock + ' in stock'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-show="products.length === 0 && !loading" class="text-center text-gray-500 py-10">
                    <i class="fas fa-search text-4xl mb-3"></i>
                    <p>No products found</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white p-2 border-t flex gap-2 text-sm">
                <button @click="clearCart()" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
                    <i class="fas fa-trash mr-1"></i>Clear (F5)
                </button>
                <div class="flex-1"></div>
                <span class="text-gray-500 px-2">Shortcuts: F1=Search, F2=Customer, F9=Pay</span>
            </div>
        </div>

        <!-- Right Panel - Cart -->
        <div class="w-96 bg-white shadow-lg flex flex-col">
            <!-- Customer Info -->
            <div class="p-3 border-b bg-gray-50">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-xs text-gray-500">Customer</p>
                        <p class="font-medium" x-text="customer.name || 'Walk-in Customer'"></p>
                        <p class="text-xs text-gray-500" x-show="customer.phone" x-text="customer.phone"></p>
                    </div>
                    <button @click="showCustomerModal = true" class="text-blue-600 hover:text-blue-800 text-sm">
                        <i class="fas fa-user-edit"></i> Change
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-3">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex gap-2 p-2 bg-gray-50 rounded-lg mb-2">
                        <div class="flex-1">
                            <h4 class="font-medium text-sm" x-text="item.name"></h4>
                            <p class="text-xs text-gray-500" x-show="item.variant_name" x-text="item.variant_name"></p>
                            <div class="flex items-center gap-2 mt-1">
                                <button @click="updateQuantity(index, -1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300 text-xs">-</button>
                                <span class="text-sm font-medium w-8 text-center" x-text="item.quantity"></span>
                                <button @click="updateQuantity(index, 1)" class="w-6 h-6 bg-gray-200 rounded hover:bg-gray-300 text-xs">+</button>
                                <span class="text-sm text-gray-500 ml-2">× ৳<span x-text="formatPrice(item.price)"></span></span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-sm">৳<span x-text="formatPrice(item.price * item.quantity)"></span></p>
                            <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700 text-xs mt-1">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="text-center text-gray-400 py-10">
                    <i class="fas fa-shopping-cart text-4xl mb-2"></i>
                    <p>Cart is empty</p>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="p-3 border-t bg-gray-50">
                <!-- Discount Controls -->
                <div x-show="!appliedDiscount.active" class="mb-2">
                    <button @click="showDiscountInput = !showDiscountInput" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-tag mr-1"></i>Add Discount
                    </button>
                    <div x-show="showDiscountInput" class="mt-1 flex gap-1">
                        <select x-model="discountType" class="text-xs px-2 py-1 border rounded">
                            <option value="fixed">Fixed ৳</option>
                            <option value="percentage">% Off</option>
                        </select>
                        <input type="number" x-model="discountInput" placeholder="0" min="0"
                            :max="discountType === 'percentage' ? 100 : subtotal"
                            class="text-xs w-16 px-2 py-1 border rounded">
                        <button @click="applyDiscount()" class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Apply</button>
                    </div>
                </div>
                <div x-show="appliedDiscount.active" class="mb-2 p-2 bg-green-50 rounded flex justify-between items-center">
                    <div class="text-xs">
                        <span class="font-medium text-green-700">
                            <i class="fas fa-check mr-1"></i>Discount:
                        </span>
                        <span x-show="appliedDiscount.type === 'fixed'">৳<span x-text="formatPrice(appliedDiscount.value)"></span></span>
                        <span x-show="appliedDiscount.type === 'percentage'" x-text="appliedDiscount.value + '%'"></span>
                    </div>
                    <button @click="removeDiscount()" class="text-xs text-red-500 hover:text-red-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal:</span>
                        <span class="font-medium">৳<span x-text="formatPrice(subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between" x-show="discount > 0">
                        <span class="text-gray-600">Discount:</span>
                        <span class="font-medium text-red-600">-৳<span x-text="formatPrice(discount)"></span></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax (5%):</span>
                        <span class="font-medium">৳<span x-text="formatPrice(tax)"></span></span>
                    </div>
                    <div class="flex justify-between border-t pt-2 mt-2">
                        <span class="text-lg font-bold">Total:</span>
                        <span class="text-xl font-bold text-blue-600">৳<span x-text="formatPrice(total)"></span></span>
                    </div>
                </div>

                <button @click="showPaymentModal = true" :disabled="cart.length === 0"
                    class="w-full mt-3 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-bold text-lg disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <i class="fas fa-credit-card mr-2"></i>Pay (F9)
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold mb-4">Payment</h2>
            
            <div class="text-center mb-6">
                <p class="text-gray-600">Total Amount</p>
                <p class="text-4xl font-bold text-blue-600">৳<span x-text="formatPrice(total)"></span></p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                <div class="grid grid-cols-3 gap-2">
                    <button @click="payment.method = 'cash'" :class="{ 'bg-blue-500 text-white': payment.method === 'cash', 'bg-gray-200': payment.method !== 'cash' }" class="p-3 rounded-lg font-medium">
                        <i class="fas fa-money-bill-wave mr-1"></i>Cash
                    </button>
                    <button @click="payment.method = 'card'" :class="{ 'bg-blue-500 text-white': payment.method === 'card', 'bg-gray-200': payment.method !== 'card' }" class="p-3 rounded-lg font-medium">
                        <i class="fas fa-credit-card mr-1"></i>Card
                    </button>
                    <button @click="payment.method = 'bkash'" :class="{ 'bg-blue-500 text-white': payment.method === 'bkash', 'bg-gray-200': payment.method !== 'bkash' }" class="p-3 rounded-lg font-medium">
                        <i class="fas fa-mobile-alt mr-1"></i>bKash
                    </button>
                    <button @click="payment.method = 'nagad'" :class="{ 'bg-blue-500 text-white': payment.method === 'nagad', 'bg-gray-200': payment.method !== 'nagad' }" class="p-3 rounded-lg font-medium">
                        <i class="fas fa-mobile-alt mr-1"></i>Nagad
                    </button>
                    <button @click="payment.method = 'split'" :class="{ 'bg-blue-500 text-white': payment.method === 'split', 'bg-gray-200': payment.method !== 'split' }" class="p-3 rounded-lg font-medium">
                        <i class="fas fa-columns mr-1"></i>Split
                    </button>
                </div>
            </div>

            <!-- Cash Payment Fields -->
            <div x-show="payment.method === 'cash'" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Amount Received (৳)</label>
                <input type="number" x-model="payment.received" @input="calculateChange()"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-lg"
                    placeholder="Enter amount">
                <div x-show="payment.change > 0" class="mt-2 text-center">
                    <p class="text-gray-600">Change Due:</p>
                    <p class="text-2xl font-bold text-green-600">৳<span x-text="formatPrice(payment.change)"></span></p>
                </div>
            </div>

            <!-- Split Payment -->
            <div x-show="payment.method === 'split'" class="mb-4 space-y-2">
                <div class="flex gap-2">
                    <select x-model="payment.split[0].method" class="flex-1 px-3 py-2 border border-gray-300 rounded">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                    </select>
                    <input type="number" x-model="payment.split[0].amount" placeholder="Amount" class="w-24 px-3 py-2 border border-gray-300 rounded">
                </div>
                <div class="flex gap-2">
                    <select x-model="payment.split[1].method" class="flex-1 px-3 py-2 border border-gray-300 rounded">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                    </select>
                    <input type="number" x-model="payment.split[1].amount" placeholder="Amount" class="w-24 px-3 py-2 border border-gray-300 rounded">
                </div>
            </div>

            <!-- Reference Number for Card/Mobile -->
            <div x-show="payment.method !== 'cash' && payment.method !== 'split'" class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                <input type="text" x-model="payment.reference" class="w-full px-4 py-2 border border-gray-300 rounded-lg" placeholder="Last 4 digits / Transaction ID">
            </div>

            <div class="flex gap-3">
                <button @click="showPaymentModal = false" class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button @click="processPayment()" :disabled="!canCompletePayment()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:bg-gray-400">
                    Complete Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div x-show="showCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Select Customer</h2>
            <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()" placeholder="Search by name or phone"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4" id="customerSearchInput">
            <div class="max-h-48 overflow-y-auto space-y-2 mb-4">
                <div @click="selectCustomer(null)" class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                    <p class="font-medium">Walk-in Customer</p>
                </div>
                <template x-for="cust in customers" :key="cust.id">
                    <div @click="selectCustomer(cust)" class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                        <p class="font-medium" x-text="cust.name"></p>
                        <p class="text-sm text-gray-500" x-text="cust.mobile"></p>
                    </div>
                </template>
                <div x-show="customers.length === 0" class="text-center text-gray-400 py-4">
                    No customers found
                </div>
            </div>
            <button @click="showCustomerModal = false" class="w-full px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                Cancel
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function posSystem() {
    return {
        // Data
        products: [],
        cart: [],
        isWholesale: false,
        customer: { name: '', phone: '', id: null },
        customerSearch: '',
        customers: [],
        searchQuery: '',
        selectedCategory: '',
        loading: false,
        currentTime: new Date().toLocaleTimeString(),

        
        // Modals
        showPaymentModal: false,
        showCustomerModal: false,
        
        initCustomerModal() {
            this.$watch('showCustomerModal', (value) => {
                if (value) {
                    this.customerSearch = '';
                    this.customers = [];
                    this.$nextTick(() => {
                        this.searchCustomers();
                        const input = document.getElementById('customerSearchInput');
                        if (input) input.focus();
                    });
                }
            });
        },
        
        // Payment
        payment: {
            method: 'cash',
            received: 0,
            change: 0,
            reference: '',
            split: [
                { method: 'cash', amount: 0 },
                { method: 'card', amount: 0 }
            ]
        },

        // Discount
        showDiscountInput: false,
        discountType: 'fixed',
        discountInput: 0,
        appliedDiscount: { active: false, type: 'fixed', value: 0 },

        // Computed
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        get discount() {
            if (!this.appliedDiscount.active) return 0;
            if (this.appliedDiscount.type === 'fixed') {
                return Math.min(this.appliedDiscount.value, this.subtotal);
            }
            return Math.min((this.subtotal * this.appliedDiscount.value) / 100, this.subtotal);
        },
        get tax() {
            return Math.max(0, (this.subtotal - this.discount) * 0.05); // 5% tax after discount
        },
        get total() {
            return this.subtotal - this.discount + this.tax;
        },
        
        // Methods
        async init() {
            console.log('POS System initializing...');
            this.initCustomerModal();
            
            // Load initial products immediately
            this.loading = true;
            try {
                const response = await fetch('/admin/pos/products/search', {
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (!response.ok) {
                    console.error('Response not OK:', response.status, response.statusText);
                    const text = await response.text();
                    console.error('Response body:', text);
                    throw new Error('Failed to load products');
                }
                
                const data = await response.json();
                console.log('Initial products loaded:', data);
                if (data.success) {
                    this.products = data.data;
                }
            } catch (error) {
                console.error('Error loading products:', error);
            } finally {
                this.loading = false;
            }
            
            // Update time
            setInterval(() => {
                this.currentTime = new Date().toLocaleTimeString();
            }, 1000);
            
            // Customer search ready
        },
        
        formatPrice(price) {
            return Number(price).toFixed(2);
        },
        
        async searchProducts() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.searchQuery) params.append('search', this.searchQuery);
                if (this.selectedCategory) params.append('category_id', this.selectedCategory);
                
                console.log('Fetching products...', params.toString());
                const response = await fetch(`/admin/pos/products/search?${params}`, {
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                if (!response.ok) {
                    console.error('Response not OK:', response.status);
                    throw new Error('Failed to fetch products');
                }
                
                const data = await response.json();
                console.log('Products response:', data);
                if (data.success) {
                    this.products = data.data;
                }
            } catch (error) {
                console.error('Error searching products:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async handleBarcodeSearch() {
            if (!this.searchQuery) return;
            
            try {
                const response = await fetch(`/admin/pos/products/barcode/${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                if (data.success) {
                    const product = data.data;
                    this.addToCart(product);
                    this.searchQuery = '';
                } else {
                    alert('Product not found');
                }
            } catch (error) {
                console.error('Error scanning barcode:', error);
            }
        },
        
        addToCart(product) {
            // Check if product has variants
            if (product.has_variants && product.variants && product.variants.length > 0) {
                // For simplicity, add first variant or show variant selector
                // In a full implementation, show a modal to select variant
                const variant = product.variants[0];
                this.addItemToCart({
                    product_id: product.id,
                    variant_id: variant.id,
                    name: product.name,
                    variant_name: variant.name,
                    price: (this.isWholesale && variant.wholesale_price) ? variant.wholesale_price : variant.price,
                    stock: variant.stock
                });
            } else {
                this.addItemToCart({
                    product_id: product.id,
                    variant_id: null,
                    name: product.name,
                    variant_name: null,
                    price: (this.isWholesale && product.wholesale_price) ? product.wholesale_price : product.price,
                    stock: product.stock
                });
            }
        },
        
        addItemToCart(item) {
            const existing = this.cart.find(i => i.product_id === item.product_id && i.variant_id === item.variant_id);
            if (existing) {
                if (existing.quantity < item.stock) {
                    existing.quantity++;
                } else {
                    alert('Not enough stock');
                }
            } else {
                this.cart.push({ ...item, quantity: 1 });
            }
        },
        
        updateQuantity(index, delta) {
            const item = this.cart[index];
            const newQty = item.quantity + delta;
            if (newQty > 0 && newQty <= item.stock) {
                item.quantity = newQty;
            } else if (newQty <= 0) {
                this.removeFromCart(index);
            }
        },
        
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        
        clearCart() {
            if (confirm('Clear all items from cart?')) {
                this.cart = [];
                this.customer = { name: '', phone: '', id: null };
            }
        },
        
        calculateChange() {
            this.payment.change = Math.max(0, this.payment.received - this.total);
        },
        
        canCompletePayment() {
            if (this.payment.method === 'cash') {
                return this.payment.received >= this.total;
            }
            if (this.payment.method === 'split') {
                const totalSplit = this.payment.split.reduce((sum, s) => sum + Number(s.amount), 0);
                return totalSplit >= this.total;
            }
            return true;
        },
        
        async processPayment() {
            try {
                let payments = [];
                
                if (this.payment.method === 'split') {
                    payments = this.payment.split.filter(s => s.amount > 0).map(s => ({
                        method: s.method,
                        amount: parseFloat(s.amount),
                        reference: ''
                    }));
                } else if (this.payment.method === 'cash') {
                    payments = [{
                        method: 'cash',
                        amount: this.total,
                        received_amount: parseFloat(this.payment.received),
                        change_amount: this.payment.change,
                        reference: ''
                    }];
                } else {
                    payments = [{
                        method: this.payment.method,
                        amount: this.total,
                        reference: this.payment.reference
                    }];
                }
                
                const orderData = {
                    items: this.cart.map(item => ({
                        product_id: item.product_id,
                        variant_id: item.variant_id,
                        quantity: item.quantity,
                        price: item.price
                    })),
                    subtotal: this.subtotal,
                    discount_amount: this.discount,
                    tax_amount: this.tax,
                    total_amount: this.total,
                    is_wholesale: this.isWholesale,
                    payments: payments,
                    customer_id: this.customer.id,
                    customer_name: this.customer.name,
                    customer_phone: this.customer.phone
                };
                
                const response = await fetch('/admin/pos/order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(orderData)
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Order completed! Order #: ' + data.data.order_number);
                    this.cart = [];
                    this.showPaymentModal = false;
                    this.payment = { method: 'cash', received: 0, change: 0, reference: '', split: [{ method: 'cash', amount: 0 }, { method: 'card', amount: 0 }] };
                    
                    // Print receipt
                    window.open(`/admin/pos/order/${data.data.order_id}/receipt`, '_blank');
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                alert('Payment failed');
            }
        },
        
        async searchCustomers() {
            try {
                const params = new URLSearchParams();
                if (this.customerSearch) params.append('query', this.customerSearch);

                const response = await fetch(`/admin/pos/customers/search?${params}`, {
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();
                if (data.success) {
                    this.customers = data.data;
                } else {
                    this.customers = [];
                }
            } catch (error) {
                console.error('Customer search error:', error);
                this.customers = [];
            }
        },

        applyDiscount() {
            const value = parseFloat(this.discountInput);
            if (!value || value <= 0) return;
            if (this.discountType === 'percentage' && value > 100) {
                alert('Discount percentage cannot exceed 100%');
                return;
            }
            if (this.discountType === 'fixed' && value > this.subtotal) {
                alert('Discount cannot exceed subtotal');
                return;
            }
            this.appliedDiscount = { active: true, type: this.discountType, value: value };
            this.showDiscountInput = false;
            this.discountInput = 0;
        },

        removeDiscount() {
            this.appliedDiscount = { active: false, type: 'fixed', value: 0 };
        },

        selectCustomer(cust) {
            if (cust) {
                this.customer = { name: cust.name, phone: cust.mobile, id: cust.id };
            } else {
                this.customer = { name: '', phone: '', id: null };
            }
            this.showCustomerModal = false;
        },
        
        handleKeydown(e) {
            switch(e.key) {
                case 'F1':
                    e.preventDefault();
                    document.getElementById('searchInput').focus();
                    break;
                case 'F2':
                    e.preventDefault();
                    this.showCustomerModal = true;
                    break;

                case 'F5':
                    e.preventDefault();
                    this.clearCart();
                    break;
                case 'F9':
                    e.preventDefault();
                    if (this.cart.length > 0) {
                        this.showPaymentModal = true;
                    }
                    break;
            }
        }
    }
}
</script>
@endpush

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    body { overflow: hidden; }
</style>
@endpush

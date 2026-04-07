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
            <span class="text-sm text-gray-300">Session: #{{ $activeSession->id }}</span>
            <span class="text-gray-400">|</span>
            <span class="text-sm text-gray-300" x-text="currentTime"></span>
        </div>
        <div class="flex items-center gap-3">
            <button @click="showHeldCartsModal = true" class="px-3 py-1 bg-gray-700 rounded hover:bg-gray-600 text-sm">
                <i class="fas fa-pause mr-1"></i>Hold (<span x-text="heldCartsCount"></span>)
            </button>
            <form action="{{ route('admin.pos.session.close') }}" method="POST" class="inline" onsubmit="return confirm('Close this POS session?')">
                @csrf
                <button type="submit" class="px-3 py-1 bg-red-600 rounded hover:bg-red-700 text-sm">
                    <i class="fas fa-times mr-1"></i>Close Session
                </button>
            </form>
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
                                    <span class="text-blue-600 font-bold">৳<span x-text="formatPrice(product.price)"></span></span>
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
                <button @click="holdCart()" class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200">
                    <i class="fas fa-pause mr-1"></i>Hold (F3)
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

    <!-- Held Carts Modal -->
    <div x-show="showHeldCartsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold mb-4">Held Carts</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <template x-for="cart in heldCarts" :key="cart.id">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium" x-text="cart.customer_name || 'No Name'"></p>
                            <p class="text-sm text-gray-500">
                                <span x-text="cart.item_count"></span> items - ৳<span x-text="formatPrice(cart.total)"></span>
                            </p>
                            <p class="text-xs text-gray-400" x-text="cart.created_at"></p>
                        </div>
                        <div class="flex gap-2">
                            <button @click="retrieveCart(cart.id)" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                                Retrieve
                            </button>
                            <button @click="deleteHeldCart(cart.id)" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </template>
                <div x-show="heldCarts.length === 0" class="text-center text-gray-400 py-6">
                    No held carts
                </div>
            </div>
            <button @click="showHeldCartsModal = false" class="w-full mt-4 px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                Close
            </button>
        </div>
    </div>

    <!-- Customer Modal -->
    <div x-show="showCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" x-cloak>
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h2 class="text-xl font-bold mb-4">Select Customer</h2>
            <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()" placeholder="Search by name or phone"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4">
            <div class="max-h-48 overflow-y-auto space-y-2 mb-4">
                <div @click="selectCustomer(null)" class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                    <p class="font-medium">Walk-in Customer</p>
                </div>
                <template x-for="cust in customers" :key="cust.id">
                    <div @click="selectCustomer(cust)" class="p-3 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50">
                        <p class="font-medium" x-text="cust.name"></p>
                        <p class="text-sm text-gray-500" x-text="cust.phone"></p>
                    </div>
                </template>
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
        customer: { name: '', phone: '', id: null },
        customerSearch: '',
        customers: [],
        searchQuery: '',
        selectedCategory: '',
        loading: false,
        currentTime: new Date().toLocaleTimeString(),
        heldCartsCount: {{ $heldCartsCount }},
        heldCarts: [],
        
        // Modals
        showPaymentModal: false,
        showHeldCartsModal: false,
        showCustomerModal: false,
        
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
        
        // Computed
        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },
        get discount() {
            return 0; // TODO: Implement discount logic
        },
        get tax() {
            return this.subtotal * 0.05; // 5% tax
        },
        get total() {
            return this.subtotal - this.discount + this.tax;
        },
        
        // Methods
        async init() {
            console.log('POS System initializing...');
            
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
            
            // Load held carts count
            this.getHeldCarts();
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
                    price: variant.price,
                    stock: variant.stock
                });
            } else {
                this.addItemToCart({
                    product_id: product.id,
                    variant_id: null,
                    name: product.name,
                    variant_name: null,
                    price: product.price,
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
        
        async holdCart() {
            if (this.cart.length === 0) {
                alert('Cart is empty');
                return;
            }
            
            try {
                const response = await fetch('/admin/pos/cart/hold', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        cart_data: {
                            items: this.cart,
                            total: this.total,
                            item_count: this.cart.reduce((sum, item) => sum + item.quantity, 0),
                            customer: this.customer
                        },
                        customer_name: this.customer.name,
                        customer_phone: this.customer.phone,
                        note: ''
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    alert('Cart held successfully');
                    this.cart = [];
                    this.customer = { name: '', phone: '', id: null };
                    this.heldCartsCount++;
                }
            } catch (error) {
                console.error('Error holding cart:', error);
            }
        },
        
        async getHeldCarts() {
            try {
                const response = await fetch('/admin/pos/cart/held');
                const data = await response.json();
                if (data.success) {
                    this.heldCarts = data.data;
                }
            } catch (error) {
                console.error('Error fetching held carts:', error);
            }
        },
        
        async retrieveCart(id) {
            try {
                const response = await fetch(`/admin/pos/cart/retrieve/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.cart = data.data.items || [];
                    this.customer = data.data.customer || { name: '', phone: '', id: null };
                    this.showHeldCartsModal = false;
                    this.heldCartsCount--;
                }
            } catch (error) {
                console.error('Error retrieving cart:', error);
            }
        },
        
        async deleteHeldCart(id) {
            if (!confirm('Delete this held cart?')) return;
            
            try {
                const response = await fetch(`/admin/pos/cart/held/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                if (response.ok) {
                    this.getHeldCarts();
                    this.heldCartsCount--;
                }
            } catch (error) {
                console.error('Error deleting held cart:', error);
            }
        },
        
        async searchCustomers() {
            // TODO: Implement customer search API
            // For now, using mock data
        },
        
        selectCustomer(cust) {
            if (cust) {
                this.customer = { name: cust.name, phone: cust.phone, id: cust.id };
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
                case 'F3':
                    e.preventDefault();
                    this.holdCart();
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

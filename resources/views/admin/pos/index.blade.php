@extends('admin.layouts.master')

@section('title', 'POS System')
@section('page-title', 'Point of Sale')
@section('no-padding')

@section('content')
<div class="h-full flex flex-col bg-slate-50" x-data="posSystem()" x-init="init()" @keydown.window="handleKeydown($event)">
    
    <!-- Modern Header -->
    <header class="bg-white border-b border-slate-200 px-5 py-3 flex items-center justify-between shadow-sm shrink-0">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-cash-register text-white text-sm"></i>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-800 leading-tight">Point of Sale</h1>
                    <p class="text-xs text-slate-400" x-text="currentTime"></p>
                </div>
            </div>
            <div class="h-8 w-px bg-slate-200 mx-1"></div>
            <button @click="isWholesale = !isWholesale" 
                class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all duration-200 border"
                :class="isWholesale 
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-700' 
                    : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'">
                <i class="fas fa-warehouse mr-1.5" :class="isWholesale ? 'text-emerald-500' : 'text-slate-400'"></i>
                <span x-text="isWholesale ? 'Wholesale Mode' : 'Retail Mode'"></span>
            </button>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 rounded-full border border-slate-200">
                <div class="w-7 h-7 bg-indigo-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-indigo-600 text-xs"></i>
                </div>
                <span class="text-sm font-medium text-slate-700">{{ auth()->user()->name ?? 'Admin' }}</span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- LEFT: Product Browser -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Search Bar -->
            <div class="bg-white px-5 py-3 border-b border-slate-200 shrink-0">
                <div class="flex gap-3">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts()"
                            @keydown.enter="handleBarcodeSearch()"
                            placeholder="Search products or scan barcode (F1)"
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                            id="searchInput">
                    </div>
                    <div class="relative">
                        <select x-model="selectedCategory" @change="searchProducts()"
                            class="pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 appearance-none cursor-pointer min-w-[160px]">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <!-- Product Grid -->
            <div class="flex-1 overflow-y-auto p-5">
                <!-- Loading -->
                <div x-show="loading" class="flex flex-col items-center justify-center h-64">
                    <div class="w-10 h-10 border-3 border-indigo-100 border-t-indigo-600 rounded-full animate-spin"></div>
                    <p class="mt-3 text-sm text-slate-400">Loading products...</p>
                </div>
                
                <!-- Empty State -->
                <div x-show="products.length === 0 && !loading" class="flex flex-col items-center justify-center h-64">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mb-3">
                        <i class="fas fa-search text-slate-300 text-xl"></i>
                    </div>
                    <p class="text-slate-400 text-sm">No products found</p>
                </div>
                
                <!-- Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" x-show="!loading">
                    <template x-for="product in products" :key="product.id">
                        <div @click="addToCart(product)"
                            class="group bg-white rounded-2xl border border-slate-200 overflow-hidden cursor-pointer transition-all duration-200 hover:shadow-lg hover:border-indigo-300 hover:-translate-y-0.5"
                            :class="{ 'opacity-40 pointer-events-none': product.stock <= 0 }"
                            :title="product.name">
                            
                            <!-- Image Area -->
                            <div class="aspect-[4/3] bg-slate-100 relative overflow-hidden">
                                <img x-show="product.image" :src="product.image" 
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                    x-on:error="$event.target.style.display='none'; $event.target.nextElementSibling.style.display='flex'">
                                <!-- Image fallback - shown when no image or on error -->
                                <div class="w-full h-full flex items-center justify-center" 
                                    :style="product.image ? 'display:none' : 'display:flex'"
                                    :id="'product-fallback-'+product.id">
                                    <div class="w-full h-full bg-gradient-to-br from-slate-100 to-slate-200 flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 rounded-2xl bg-white/80 backdrop-blur flex items-center justify-center shadow-sm">
                                            <i class="fas fa-box text-slate-300 text-2xl"></i>
                                        </div>
                                        <p class="text-[10px] text-slate-400 mt-2 font-medium uppercase tracking-wide" x-text="product.sku || 'No Image'"></p>
                                    </div>
                                </div>
                                
                                <!-- Stock Badge -->
                                <div class="absolute top-2.5 right-2.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide"
                                        :class="product.stock <= 0 ? 'bg-red-100 text-red-600' : product.stock <= 5 ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'"
                                        x-text="product.stock <= 0 ? 'Out' : product.stock <= 5 ? 'Low' : product.stock + ' left'">
                                    </span>
                                </div>
                                
                                <!-- Wholesale Badge -->
                                <div x-show="isWholesale && product.wholesale_price" 
                                    class="absolute top-2.5 left-2.5 px-2 py-0.5 bg-indigo-600 text-white rounded-full text-[10px] font-bold uppercase tracking-wide">
                                    WS
                                </div>
                                
                                <!-- Quick Add Overlay -->
                                <div class="absolute inset-0 bg-indigo-600/0 group-hover:bg-indigo-600/10 transition-colors flex items-center justify-center">
                                    <div class="w-10 h-10 bg-white rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transform scale-75 group-hover:scale-100 transition-all duration-200">
                                        <i class="fas fa-plus text-indigo-600"></i>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Info -->
                            <div class="p-3">
                                <h3 class="text-sm font-semibold text-slate-800 leading-snug line-clamp-2 min-h-[2.5rem]" x-text="product.name"></h3>
                                <div class="mt-2 flex items-end justify-between">
                                    <div>
                                        <span class="text-lg font-bold text-indigo-600">৳<span x-text="formatPrice(product.wholesale_price && isWholesale ? product.wholesale_price : product.price)"></span></span>
                                        <span x-show="product.wholesale_price && isWholesale" class="text-[10px] text-slate-400 line-through ml-1">৳<span x-text="formatPrice(product.price)"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Bottom Actions Bar -->
            <div class="bg-white px-5 py-2.5 border-t border-slate-200 flex items-center justify-between shrink-0">
                <button @click="clearCart()" 
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="fas fa-trash-alt text-xs"></i>
                    Clear Cart
                </button>
                <div class="flex items-center gap-1 text-xs text-slate-400">
                    <span class="px-2 py-1 bg-slate-100 rounded font-mono">F1</span><span>Search</span>
                    <span class="mx-1">·</span>
                    <span class="px-2 py-1 bg-slate-100 rounded font-mono">F2</span><span>Customer</span>
                    <span class="mx-1">·</span>
                    <span class="px-2 py-1 bg-slate-100 rounded font-mono">F9</span><span>Pay</span>
                </div>
            </div>
        </div>

        <!-- RIGHT: Cart Panel -->
        <div class="w-[400px] bg-white border-l border-slate-200 flex flex-col shadow-xl shrink-0">
            
            <!-- Customer Header -->
            <div class="p-4 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                            <i class="fas fa-user text-indigo-500"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-medium uppercase tracking-wide">Customer</p>
                            <p class="text-sm font-semibold text-slate-800" x-text="customer.name || 'Walk-in Customer'"></p>
                            <p class="text-xs text-slate-400" x-show="customer.phone" x-text="customer.phone"></p>
                        </div>
                    </div>
                    <button @click="showCustomerModal = true" 
                        class="px-3 py-2 bg-slate-50 hover:bg-indigo-50 text-slate-500 hover:text-indigo-600 rounded-lg text-sm font-medium transition-colors border border-slate-200 hover:border-indigo-200">
                        <i class="fas fa-exchange-alt mr-1.5"></i>Change
                    </button>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4">
                <!-- Empty Cart -->
                <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-center">
                    <div class="w-20 h-20 bg-slate-50 rounded-2xl flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-basket text-slate-300 text-3xl"></i>
                    </div>
                    <p class="text-slate-400 font-medium">Your cart is empty</p>
                    <p class="text-xs text-slate-300 mt-1">Click a product to add it</p>
                </div>
                
                <!-- Cart List -->
                <div class="space-y-3" x-show="cart.length > 0">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 group hover:border-indigo-200 transition-colors">
                            <div class="flex items-start gap-3">
                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-slate-800 truncate" x-text="item.name"></h4>
                                    <p class="text-xs text-slate-400 mt-0.5" x-show="item.variant_name" x-text="item.variant_name"></p>
                                    
                                    <!-- Quantity Controls -->
                                    <div class="flex items-center gap-2 mt-2">
                                        <button @click="updateQuantity(index, -1)" 
                                            class="w-7 h-7 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 hover:border-slate-300 flex items-center justify-center text-slate-500 transition-colors">
                                            <i class="fas fa-minus text-[10px]"></i>
                                        </button>
                                        <span class="text-sm font-bold text-slate-700 w-8 text-center" x-text="item.quantity"></span>
                                        <button @click="updateQuantity(index, 1)" 
                                            class="w-7 h-7 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 hover:border-slate-300 flex items-center justify-center text-slate-500 transition-colors">
                                            <i class="fas fa-plus text-[10px]"></i>
                                        </button>
                                        <span class="text-xs text-slate-400 ml-1">× ৳<span x-text="formatPrice(item.price)"></span></span>
                                    </div>
                                </div>
                                
                                <!-- Price & Remove -->
                                <div class="text-right shrink-0">
                                    <p class="text-sm font-bold text-slate-800">৳<span x-text="formatPrice(item.price * item.quantity)"></span></p>
                                    <button @click="removeFromCart(index)" 
                                        class="mt-2 text-xs text-slate-300 hover:text-red-500 transition-colors px-2 py-1 rounded hover:bg-red-50">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Summary & Checkout -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                
                <!-- Discount -->
                <div x-show="!appliedDiscount.active" class="mb-3">
                    <button @click="showDiscountInput = !showDiscountInput" 
                        class="flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors">
                        <i class="fas fa-tag"></i>
                        Add Discount
                    </button>
                    <div x-show="showDiscountInput" class="mt-2 flex gap-2" x-transition>
                        <select x-model="discountType" 
                            class="text-sm px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="fixed">Fixed ৳</option>
                            <option value="percentage">% Off</option>
                        </select>
                        <input type="number" x-model="discountInput" placeholder="0" min="0"
                            :max="discountType === 'percentage' ? 100 : subtotal"
                            class="text-sm w-24 px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <button @click="applyDiscount()" 
                            class="text-sm px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">Apply</button>
                    </div>
                </div>
                
                <!-- Active Discount -->
                <div x-show="appliedDiscount.active" class="mb-3 p-3 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-emerald-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-emerald-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-emerald-700">Discount Applied</p>
                            <p class="text-sm font-bold text-emerald-800">
                                <span x-show="appliedDiscount.type === 'fixed'">৳<span x-text="formatPrice(appliedDiscount.value)"></span></span>
                                <span x-show="appliedDiscount.type === 'percentage'" x-text="appliedDiscount.value + '% OFF'"></span>
                            </p>
                        </div>
                    </div>
                    <button @click="removeDiscount()" class="w-7 h-7 hover:bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-500 transition-colors">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>

                <!-- Totals -->
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Subtotal</span>
                        <span class="font-semibold text-slate-700">৳<span x-text="formatPrice(subtotal)"></span></span>
                    </div>
                    <div x-show="discount > 0" class="flex justify-between text-sm">
                        <span class="text-emerald-600">Discount</span>
                        <span class="font-semibold text-emerald-600">-৳<span x-text="formatPrice(discount)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Tax (5%)</span>
                        <span class="font-semibold text-slate-700">৳<span x-text="formatPrice(tax)"></span></span>
                    </div>
                    <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                        <span class="text-base font-bold text-slate-800">Total</span>
                        <span class="text-2xl font-bold text-indigo-600">৳<span x-text="formatPrice(total)"></span></span>
                    </div>
                </div>

                <!-- Pay Button -->
                <button @click="showPaymentModal = true" :disabled="cart.length === 0"
                    class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white rounded-xl font-bold text-base shadow-lg shadow-indigo-200 hover:shadow-indigo-300 transition-all flex items-center justify-center gap-2">
                    <i class="fas fa-credit-card"></i>
                    <span>Proceed to Payment</span>
                    <span class="px-2 py-0.5 bg-white/20 rounded text-xs" x-show="cart.length > 0" x-text="cart.length + ' item' + (cart.length > 1 ? 's' : '')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div x-show="showPaymentModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center" x-cloak x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.away="showPaymentModal = false" x-transition>
            <!-- Header -->
            <div class="bg-indigo-600 px-6 py-4 text-white">
                <h2 class="text-lg font-bold">Payment</h2>
                <p class="text-indigo-200 text-sm">Complete the transaction</p>
            </div>
            
            <div class="p-6">
                <!-- Amount Display -->
                <div class="text-center mb-6 p-4 bg-slate-50 rounded-xl">
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium mb-1">Total Amount</p>
                    <p class="text-4xl font-bold text-indigo-600">৳<span x-text="formatPrice(total)"></span></p>
                </div>

                <!-- Payment Methods -->
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Payment Method</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button @click="payment.method = 'cash'" 
                            :class="payment.method === 'cash' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                            class="p-3 rounded-xl border-2 font-medium text-sm transition-all flex flex-col items-center gap-1">
                            <i class="fas fa-money-bill-wave text-lg"></i>
                            Cash
                        </button>
                        <button @click="payment.method = 'card'" 
                            :class="payment.method === 'card' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                            class="p-3 rounded-xl border-2 font-medium text-sm transition-all flex flex-col items-center gap-1">
                            <i class="fas fa-credit-card text-lg"></i>
                            Card
                        </button>
                        <button @click="payment.method = 'bkash'" 
                            :class="payment.method === 'bkash' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                            class="p-3 rounded-xl border-2 font-medium text-sm transition-all flex flex-col items-center gap-1">
                            <i class="fas fa-mobile-alt text-lg"></i>
                            bKash
                        </button>
                        <button @click="payment.method = 'nagad'" 
                            :class="payment.method === 'nagad' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                            class="p-3 rounded-xl border-2 font-medium text-sm transition-all flex flex-col items-center gap-1">
                            <i class="fas fa-mobile-alt text-lg"></i>
                            Nagad
                        </button>
                        <button @click="payment.method = 'split'" 
                            :class="payment.method === 'split' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:border-indigo-300'"
                            class="p-3 rounded-xl border-2 font-medium text-sm transition-all flex flex-col items-center gap-1 col-span-2">
                            <i class="fas fa-columns text-lg"></i>
                            Split Payment
                        </button>
                    </div>
                </div>

                <!-- Cash Payment -->
                <div x-show="payment.method === 'cash'" class="mb-5" x-transition>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Amount Received</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">৳</span>
                        <input type="number" x-model="payment.received" @input="calculateChange()"
                            class="w-full pl-9 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-lg font-bold focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                            placeholder="0.00">
                    </div>
                    <div x-show="payment.change > 0" class="mt-3 p-3 bg-emerald-50 rounded-xl text-center">
                        <p class="text-xs text-emerald-600 font-medium uppercase tracking-wide">Change Due</p>
                        <p class="text-2xl font-bold text-emerald-600">৳<span x-text="formatPrice(payment.change)"></span></p>
                    </div>
                </div>

                <!-- Split Payment -->
                <div x-show="payment.method === 'split'" class="mb-5 space-y-3" x-transition>
                    <div class="flex gap-2">
                        <select x-model="payment.split[0].method" class="flex-1 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                        </select>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">৳</span>
                            <input type="number" x-model="payment.split[0].amount" placeholder="0" class="w-28 pl-7 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select x-model="payment.split[1].method" class="flex-1 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                        </select>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">৳</span>
                            <input type="number" x-model="payment.split[1].amount" placeholder="0" class="w-28 pl-7 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm">
                        </div>
                    </div>
                </div>

                <!-- Reference -->
                <div x-show="payment.method !== 'cash' && payment.method !== 'split'" class="mb-5" x-transition>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Reference Number</label>
                    <input type="text" x-model="payment.reference" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500" placeholder="Transaction ID / Last 4 digits">
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button @click="showPaymentModal = false" 
                        class="flex-1 px-4 py-3 bg-slate-100 text-slate-600 rounded-xl hover:bg-slate-200 font-medium transition-colors">
                        Cancel
                    </button>
                    <button @click="processPayment()" :disabled="!canCompletePayment()"
                        class="flex-1 px-4 py-3 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 disabled:bg-slate-300 disabled:cursor-not-allowed font-bold transition-colors">
                        Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Modal -->
    <div x-show="showCustomerModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center" x-cloak x-transition.opacity>
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden" @click.away="showCustomerModal = false" x-transition>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Select Customer</h2>
                        <p class="text-sm text-slate-400">Choose a customer for this order</p>
                    </div>
                    <button @click="showCustomerModal = false" class="w-8 h-8 hover:bg-slate-100 rounded-lg flex items-center justify-center text-slate-400 transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="relative mb-4">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" x-model="customerSearch" @input.debounce.300ms="searchCustomers()" 
                        placeholder="Search by name or phone..."
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"
                        id="customerSearchInput">
                </div>
                
                <div class="max-h-72 overflow-y-auto space-y-2">
                    <!-- Walk-in -->
                    <div @click="selectCustomer(null)" 
                        class="p-3.5 bg-slate-50 rounded-xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition-all flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm">
                            <i class="fas fa-walking text-slate-400"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">Walk-in Customer</p>
                            <p class="text-xs text-slate-400">No customer account</p>
                        </div>
                    </div>
                    
                    <!-- Customer List -->
                    <template x-for="cust in customers" :key="cust.id">
                        <div @click="selectCustomer(cust)" 
                            class="p-3.5 bg-slate-50 rounded-xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 border border-transparent transition-all flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                                <span class="text-sm font-bold text-indigo-600" x-text="cust.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div>
                                <p class="font-semibold text-slate-700" x-text="cust.name"></p>
                                <p class="text-xs text-slate-400" x-text="cust.mobile"></p>
                            </div>
                            <i class="fas fa-chevron-right ml-auto text-slate-300 text-xs"></i>
                        </div>
                    </template>
                    
                    <div x-show="customers.length === 0 && customerSearch" class="text-center py-6">
                        <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-search text-slate-300"></i>
                        </div>
                        <p class="text-sm text-slate-400">No customers found</p>
                    </div>
                </div>
            </div>
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
        currentTime: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),

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
            return Math.max(0, (this.subtotal - this.discount) * 0.05);
        },
        get total() {
            return this.subtotal - this.discount + this.tax;
        },
        
        // Methods
        async init() {
            this.initCustomerModal();
            
            this.loading = true;
            try {
                const response = await fetch('/admin/pos/products/search', {
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
                if (data.success) {
                    this.products = data.data;
                }
            } catch (error) {
                console.error('Error loading products:', error);
            } finally {
                this.loading = false;
            }
            
            setInterval(() => {
                this.currentTime = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }, 1000);
        },
        
        formatPrice(price) {
            return Number(price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        
        async searchProducts() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.searchQuery) params.append('search', this.searchQuery);
                if (this.selectedCategory) params.append('category_id', this.selectedCategory);
                
                const response = await fetch(`/admin/pos/products/search?${params}`, {
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });
                
                const data = await response.json();
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
                    // Show a nice toast instead of alert
                    this.showToast('Product not found', 'error');
                }
            } catch (error) {
                console.error('Error scanning barcode:', error);
            }
        },
        
        addToCart(product) {
            if (product.stock <= 0) {
                this.showToast('Product out of stock', 'error');
                return;
            }
            
            if (product.has_variants && product.variants && product.variants.length > 0) {
                // For products with variants, add the first available variant
                const variant = product.variants.find(v => v.stock > 0) || product.variants[0];
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
                    this.showToast('Quantity increased', 'success');
                } else {
                    this.showToast('Not enough stock available', 'error');
                }
            } else {
                this.cart.push({ ...item, quantity: 1 });
                this.showToast('Added to cart', 'success');
            }
        },
        
        updateQuantity(index, delta) {
            const item = this.cart[index];
            const newQty = item.quantity + delta;
            if (newQty > 0 && newQty <= item.stock) {
                item.quantity = newQty;
            } else if (newQty <= 0) {
                this.removeFromCart(index);
            } else {
                this.showToast('Maximum stock reached', 'warning');
            }
        },
        
        removeFromCart(index) {
            this.cart.splice(index, 1);
        },
        
        clearCart() {
            if (this.cart.length === 0) return;
            if (confirm('Clear all items from cart?')) {
                this.cart = [];
                this.customer = { name: '', phone: '', id: null };
                this.appliedDiscount = { active: false, type: 'fixed', value: 0 };
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
                const totalSplit = this.payment.split.reduce((sum, s) => sum + Number(s.amount || 0), 0);
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
                    this.showToast('Order completed! #' + data.data.order_number, 'success');
                    this.cart = [];
                    this.showPaymentModal = false;
                    this.payment = { method: 'cash', received: 0, change: 0, reference: '', split: [{ method: 'cash', amount: 0 }, { method: 'card', amount: 0 }] };
                    this.appliedDiscount = { active: false, type: 'fixed', value: 0 };
                    this.customer = { name: '', phone: '', id: null };
                    
                    window.open(`/admin/pos/order/${data.data.order_id}/receipt`, '_blank');
                } else {
                    this.showToast(data.message || 'Payment failed', 'error');
                }
            } catch (error) {
                console.error('Error processing payment:', error);
                this.showToast('Payment failed. Please try again.', 'error');
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
                this.showToast('Discount percentage cannot exceed 100%', 'error');
                return;
            }
            if (this.discountType === 'fixed' && value > this.subtotal) {
                this.showToast('Discount cannot exceed subtotal', 'error');
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
        
        showToast(message, type = 'success') {
            // Simple toast implementation
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-emerald-600',
                error: 'bg-red-500',
                warning: 'bg-amber-500'
            };
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle'
            };
            toast.className = `fixed bottom-6 right-6 ${colors[type]} text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 z-50 animate-bounce`;
            toast.innerHTML = `<i class="fas ${icons[type]}"></i><span class="font-medium">${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                toast.style.transition = 'all 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 2500);
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
    @media (min-width: 1024px) {
        body { overflow: hidden; }
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    /* Custom scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush

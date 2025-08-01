<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const { props } = usePage();
const user = computed(() => props.auth.user);

const categories = ref([]);
const cartItemCount = ref(0);
const showUserDropdown = ref(false);

// Fetch categories
const fetchCategories = async () => {
    try {
        const response = await axios.get('/api/categories');
        categories.value = response.data;
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

// Fetch cart count
const fetchCartCount = async () => {
    if (user.value) { // Only fetch if user is logged in
        try {
            const response = await axios.get('/api/cart');
            cartItemCount.value = response.data.cart.cart_items.length;
        } catch (error) {
            console.error('Error fetching cart count:', error);
            cartItemCount.value = 0;
        }
    } else {
        cartItemCount.value = 0;
    }
};

onMounted(() => {
    fetchCategories();
    fetchCartCount();
});

// Logout function
const logout = async () => {
    try {
        await axios.post('/logout');
        // Redirect to home or login page after logout
        window.location.href = '/';
    } catch (error) {
        console.error('Error logging out:', error);
    }
};
</script>

<template>
    <header class="bg-white shadow-sm py-4">
        <div class="container mx-auto px-4 flex items-center justify-between">
            <div class="flex-shrink-0">
                <Link :href="route('home')" class="text-2xl font-bold text-blue-600">BeeMarket</Link>
            </div>

            <div class="relative group">
                <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                    <i class="fas fa-bars"></i>
                    <span>All Categories</span>
                </button>
                <div class="absolute hidden group-hover:block bg-white shadow-lg mt-2 py-2 rounded-md w-48 z-10">
                    <Link v-for="category in categories" :key="category.id" :href="route('products.index', { category: category.slug })" class="block px-4 py-2 text-gray-800 hover:bg-gray-100">
                        {{ category.name }}
                    </Link>
                </div>
            </div>

            <div class="flex-grow max-w-lg mx-4">
                <div class="relative">
                    <input type="text" placeholder="I am shopping for..." class="w-full pl-4 pr-10 py-2 rounded-full border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <div class="relative">
                    <button @click="showUserDropdown = !showUserDropdown" class="flex items-center space-x-1 text-gray-700 hover:text-blue-600">
                        <i class="fas fa-user"></i>
                        <span v-if="user">{{ user.name }}</span>
                        <span v-else>Account</span>
                    </button>
                    <div v-if="showUserDropdown" @click.outside="showUserDropdown = false" class="absolute right-0 mt-2 py-2 w-48 bg-white rounded-md shadow-xl z-20">
                        <template v-if="user">
                            <Link :href="route('profile.edit')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Profile
                            </Link>
                            <Link :href="route('orders.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                My Orders
                            </Link>
                            <Link v-if="user.role === 'seller' || user.role === 'admin'" :href="route('seller.dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Seller Dashboard
                            </Link>
                            <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Admin Dashboard
                            </Link>
                            <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Log Out
                            </button>
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Sign In
                            </Link>
                            <Link :href="route('register')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                Register
                            </Link>
                        </template>
                    </div>
                </div>

                <Link :href="route('cart.index')" class="relative text-gray-700 hover:text-blue-600">
                    <i class="fas fa-shopping-cart text-lg"></i>
                    <span v-if="cartItemCount > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">
                        {{ cartItemCount }}
                    </span>
                </Link>

                </div>
        </div>
        <nav class="bg-gray-50 border-t border-gray-200 py-2">
            <div class="container mx-auto px-4 flex items-center space-x-6 text-sm">
                <Link href="#" class="text-gray-700 hover:text-blue-600">Popular</Link>
                <Link :href="route('products.index')" class="text-gray-700 hover:text-blue-600">Shop</Link>
                <Link href="#" class="text-gray-700 hover:text-blue-600">Contact</Link>
                <Link href="#" class="text-gray-700 hover:text-blue-600">Pages</Link>
                <Link href="#" class="text-gray-700 hover:text-blue-600">Blogs</Link>
                <div class="ml-auto bg-red-500 text-white px-3 py-1 rounded-full text-xs font-semibold">
                    Best Selling <span class="ml-1">SALE</span>
                </div>
            </div>
        </nav>
    </header>
</template>
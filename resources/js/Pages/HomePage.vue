<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, onMounted } from 'vue';
import ProductCard from '@/Components/ProductCard.vue'; // Sẽ tạo component này sau
import HeroBanner from '@/Components/HeroBanner.vue'; // Sẽ tạo component này sau
import PromoBanner from '@/Components/PromoBanner.vue'; // Sẽ tạo component này sau

const newArrivals = ref([]);
const banners = ref([]); // For main hero and promo banners

const fetchNewArrivals = async () => {
    try {
        const response = await axios.get('/api/products', {
            params: {
                sort_by: 'created_at',
                sort_order: 'desc',
                limit: 8 // Lấy 8 sản phẩm mới nhất, có thể điều chỉnh
            }
        });
        newArrivals.value = response.data;
    } catch (error) {
        console.error('Error fetching new arrivals:', error);
    }
};

const fetchBanners = async () => {
    try {
        const response = await axios.get('/api/banners');
        banners.value = response.data;
    } catch (error) {
        console.error('Error fetching banners:', error);
    }
};

onMounted(() => {
    fetchNewArrivals();
    fetchBanners();
});

// Dummy function for adding to cart (actual logic will be in ProductCard and use API)
const handleAddToCart = (productId) => {
    console.log(`Add product ${productId} to cart from HomePage.`);
    // Here you would typically dispatch an event or use a global store
    // to update the cart count in TheHeader. For now, it's a console log.
    // The actual API call is made within ProductCard.
};
</script>

<template>
    <Head title="Home" />

    <AppLayout>
        <div class="container mx-auto px-4 py-8">
            <section class="flex flex-col lg:flex-row gap-6 mb-12">
                <div class="lg:w-2/3">
                    <HeroBanner :banners="banners.filter(b => b.target_url.includes('iphone'))" />
                </div>
                <div class="lg:w-1/3 flex flex-col gap-6">
                    <PromoBanner v-for="banner in banners.filter(b => b.target_url.includes('security') || b.target_url.includes('galaxy'))" :key="banner.id" :banner="banner" />
                </div>
            </section>

            <section class="mb-12">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-3xl font-bold text-gray-800">New Arrivals</h2>
                    <Link :href="route('products.index')" class="text-blue-600 hover:underline">View All</Link>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <ProductCard v-for="product in newArrivals" :key="product.id" :product="product" @add-to-cart="handleAddToCart" />
                </div>
            </section>

            </div>
    </AppLayout>
</template>
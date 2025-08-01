<script setup>
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['addToCart']);

const addToCart = async () => {
    try {
        const response = await axios.post('/api/cart/add', {
            product_id: props.product.id,
            quantity: 1, // Default to 1
        });
        console.log('Product added to cart:', response.data);
        alert(`${props.product.name} has been added to your cart!`);
        emit('addToCart', props.product.id); // Emit event to HomePage or parent to update cart count
    } catch (error) {
        console.error('Error adding to cart:', error.response ? error.response.data : error.message);
        alert(`Failed to add ${props.product.name} to cart. ${error.response ? error.response.data.message : ''}`);
    }
};
</script>

<template>
    <div class="bg-white rounded-lg shadow-md overflow-hidden relative group">
        <Link :href="route('products.show', props.product.slug)">
            <img :src="props.product.image || '/images/default-product.png'" :alt="props.product.name" class="w-full h-48 object-contain p-4 group-hover:scale-105 transition-transform duration-300" />
        </Link>

        <div class="absolute inset-x-0 bottom-0 bg-white bg-opacity-90 py-2 px-4 flex justify-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 transform translate-y-full group-hover:translate-y-0">
            <button class="p-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                <i class="fas fa-eye"></i> </button>
            <button @click="addToCart" class="p-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                <i class="fas fa-cart-plus"></i> </button>
            <button class="p-2 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                <i class="fas fa-heart"></i> </button>
        </div>

        <div class="p-4 pt-0 text-center">
            <Link :href="route('products.show', props.product.slug)">
                <h3 class="text-lg font-semibold text-gray-800 line-clamp-2">{{ props.product.name }}</h3>
            </Link>
            <div class="flex items-center justify-center mt-2">
                <p class="text-xl font-bold text-gray-900 mr-2">${{ product.price.toFixed(2) }}</p>
                <p v-if="product.old_price" class="text-sm text-gray-500 line-through">${{ product.old_price.toFixed(2) }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom styles for the overlay if needed, or use Tailwind variants directly */
</style>
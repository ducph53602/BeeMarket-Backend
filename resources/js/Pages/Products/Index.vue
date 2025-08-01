<template>
  <AppLayout>
    <Head title="Products" />
    <h1 class="text-3xl font-bold mb-6">All Products</h1>

    <div v-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <div v-for="product in products" :key="product.id" class="border rounded-lg overflow-hidden shadow-lg">
        <img :src="product.image_url || '/images/placeholder.png'" :alt="product.name" class="w-full h-48 object-cover">
        <div class="p-4">
          <h3 class="text-xl font-medium text-gray-900">{{ product.name }}</h3>
          <p class="text-gray-700 mt-1">${{ product.price }}</p>
          <p class="text-sm text-gray-500">Stock: {{ product.quantity }}</p>
          <inertia-link :href="route('products.show', product.slug)" class="mt-4 inline-block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">View Details</inertia-link>
        </div>
      </div>
    </div>
    <p v-else>No products found.</p>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const products = ref([]);

onMounted(async () => {
  try {
    const response = await axios.get(route('api.products.index'));
    products.value = response.data.products || response.data; // Adjust based on actual API response structure
  } catch (error) {
    console.error('Error fetching products:', error);
  }
});
</script>
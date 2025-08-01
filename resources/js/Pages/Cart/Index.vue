<template>
  <AppLayout>
    <Head title="Your Cart" />
    <h1 class="text-3xl font-bold mb-6">Your Shopping Cart</h1>

    <div v-if="cart && cart.cart_items && cart.cart_items.length > 0">
      <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div v-for="item in cart.cart_items" :key="item.id" class="flex items-center justify-between border-b pb-4 mb-4 last:border-b-0 last:pb-0 last:mb-0">
          <div class="flex items-center">
            <img :src="item.product.image_url || '/images/placeholder.png'" :alt="item.product.name" class="w-20 h-20 object-cover rounded mr-4">
            <div>
              <h3 class="text-lg font-semibold">{{ item.product.name }}</h3>
              <p class="text-gray-600">${{ item.product.price }}</p>
            </div>
          </div>
          <div class="flex items-center">
            <input
              type="number"
              v-model.number="item.quantity"
              @change="updateCartItem(item)"
              min="0"
              class="w-20 text-center border border-gray-300 rounded-md py-1 px-2 focus:ring-blue-500 focus:border-blue-500"
            >
            <button @click="removeCartItem(item)" class="ml-4 text-red-600 hover:text-red-800">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
            </button>
          </div>
        </div>
        <div class="mt-6 text-right">
          <p class="text-xl font-bold">Total: ${{ cart.total_amount ? cart.total_amount.toFixed(2) : '0.00' }}</p>
          <button @click="checkoutCart" class="mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-xl">
            Proceed to Checkout
          </button>
        </div>
      </div>
    </div>
    <div v-else class="text-center text-xl text-gray-600">
      Your cart is empty. <inertia-link :href="route('products.index')" class="text-blue-500 hover:underline">Start shopping!</inertia-link>
    </div>
    <p v-if="cartMessage" :class="{'text-green-600': cartMessage.includes('success'), 'text-red-600': cartMessage.includes('Error')}" class="mt-4 text-center">{{ cartMessage }}</p>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const cart = ref(null);
const cartMessage = ref('');

const fetchCart = async () => {
  try {
    const response = await axios.get(route('api.cart.show'));
    cart.value = response.data.cart;
    cart.value.total_amount = response.data.total_amount; // Make sure total_amount is reactive
  } catch (error) {
    console.error('Error fetching cart:', error);
    cartMessage.value = 'Error fetching cart data.';
  }
};

onMounted(() => {
  fetchCart();
});

const updateCartItem = async (item) => {
  if (item.quantity < 0) {
    item.quantity = 0; // Prevent negative quantity
  }
  try {
    const response = await axios.put(route('api.cart.update', item.id), {
      quantity: item.quantity,
    });
    cart.value = response.data; // API should return updated cart
    // Recalculate total amount as it's not always returned directly by update API
    cart.value.total_amount = cart.value.cart_items.sum(i => i.quantity * i.product.price);
    cartMessage.value = 'Cart updated successfully!';
  } catch (error) {
    cartMessage.value = 'Error updating cart: ' + (error.response?.data?.message || 'Unknown error');
    console.error('Error updating cart:', error.response?.data || error);
    fetchCart(); // Re-fetch cart to revert to original state if update failed
  }
};

const removeCartItem = async (item) => {
  try {
    const response = await axios.delete(route('api.cart.remove', item.id));
    cart.value = response.data; // API should return updated cart
    // Recalculate total amount
    cart.value.total_amount = cart.value.cart_items.sum(i => i.quantity * i.product.price);
    cartMessage.value = 'Item removed from cart.';
  } catch (error) {
    cartMessage.value = 'Error removing item: ' + (error.response?.data?.message || 'Unknown error');
    console.error('Error removing item:', error.response?.data || error);
  }
};

const checkoutCart = async () => {
  try {
    const response = await axios.post(route('api.cart.checkout'));
    cartMessage.value = 'Checkout successful! Order placed.';
    router.visit(route('orders.index')); // Redirect to orders page
  } catch (error) {
    cartMessage.value = 'Checkout failed: ' + (error.response?.data?.message || 'Unknown error');
    console.error('Checkout error:', error.response?.data || error);
  }
};
</script>
<template>
  <AppLayout>
    <Head :title="product.name" />
    <div v-if="product" class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-lg">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
          <img :src="product.image_url || '/images/placeholder.png'" :alt="product.name" class="w-full h-96 object-contain rounded-lg shadow-md">
        </div>
        <div>
          <h1 class="text-4xl font-extrabold text-gray-900 mb-4">{{ product.name }}</h1>
          <p class="text-2xl text-green-600 font-semibold mb-4">${{ product.price }}</p>
          <p class="text-gray-700 text-lg mb-4">{{ product.description }}</p>
          <p class="text-gray-600 mb-2"><strong>Category:</strong> {{ product.category ? product.category.name : 'N/A' }}</p>
          <p class="text-gray-600 mb-4"><strong>Available Stock:</strong> {{ product.quantity }}</p>

          <div class="flex items-center mb-6">
            <label for="quantity" class="mr-4 text-lg">Quantity:</label>
            <input
              type="number"
              id="quantity"
              v-model.number="quantityToAdd"
              min="1"
              :max="product.quantity"
              class="w-24 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-lg"
            />
          </div>

          <button
            @click="addToCart"
            :disabled="quantityToAdd <= 0 || quantityToAdd > product.quantity"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg text-xl transition duration-300 ease-in-out"
          >
            Add to Cart
          </button>
          <p v-if="cartMessage" :class="{'text-green-600': cartMessage.includes('success'), 'text-red-600': cartMessage.includes('Error')}" class="mt-4 text-center">{{ cartMessage }}</p>
        </div>
      </div>

      <hr class="my-10 border-gray-300" />

      <section>
        <h2 class="text-3xl font-bold mb-6">Customer Reviews</h2>
        <div v-if="reviews.length" class="space-y-6">
          <div v-for="review in reviews" :key="review.id" class="border p-4 rounded-lg shadow-sm bg-gray-50">
            <div class="flex items-center mb-2">
              <span class="font-semibold text-gray-800">{{ review.user.name }}</span>
              <span class="ml-auto text-yellow-500">
                <i v-for="n in review.rating" :key="n" class="fas fa-star"></i>
                <i v-for="n in (5 - review.rating)" :key="n + 5" class="far fa-star"></i>
              </span>
            </div>
            <p class="text-gray-700">{{ review.comment }}</p>
            <p class="text-sm text-gray-500 mt-2">{{ new Date(review.created_at).toLocaleDateString() }}</p>
          </div>
        </div>
        <p v-else class="text-gray-600">No reviews yet. Be the first to review this product!</p>

        <div v-if="$page.props.auth.user" class="mt-8 p-6 border rounded-lg shadow-md bg-white">
          <h3 class="text-2xl font-bold mb-4">Leave a Review</h3>
          <form @submit.prevent="submitReview">
            <div class="mb-4">
              <label for="rating" class="block text-gray-700 text-sm font-bold mb-2">Rating (1-5 Stars):</label>
              <input type="number" id="rating" v-model.number="newReview.rating" min="1" max="5" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
              <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment:</label>
              <textarea id="comment" v-model="newReview.comment" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Submit Review</button>
            <p v-if="reviewMessage" :class="{'text-green-600': reviewMessage.includes('success'), 'text-red-600': reviewMessage.includes('Error')}" class="mt-4">{{ reviewMessage }}</p>
          </form>
        </div>
        <p v-else class="mt-8 text-gray-600">Please <inertia-link :href="route('login')" class="text-blue-500 hover:underline">log in</inertia-link> to leave a review.</p>
      </section>
    </div>
    <div v-else class="text-center text-xl text-gray-600">Loading product details...</div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Props passed from Laravel controller (assuming ProductController@show passes 'product' data)
const props = defineProps({
  product: Object, // This prop will contain the product data including categories and initial reviews
});

const product = ref(props.product); // Use ref for reactivity
const quantityToAdd = ref(1);
const cartMessage = ref('');
const reviews = ref([]); // To store product reviews
const reviewMessage = ref('');
const newReview = ref({
  rating: 5,
  comment: '',
});

const page = usePage(); // Access Inertia page props, including auth.user

// Fetch reviews specifically for this product
const fetchReviews = async () => {
  try {
    const response = await axios.get(route('api.products.reviews', product.value.slug));
    reviews.value = response.data.reviews || response.data; // Adjust based on API structure
  } catch (error) {
    console.error('Error fetching reviews:', error);
  }
};

onMounted(() => {
  // If product data is already available from props, fetch reviews
  if (product.value) {
    fetchReviews();
  }
});


const addToCart = async () => {
  try {
    const response = await axios.post(route('api.cart.add'), {
      product_id: product.value.id,
      quantity: quantityToAdd.value,
    });
    cartMessage.value = 'Product added to cart successfully!';
    // Optionally, update cart count in global state or show a success toast
    console.log('Cart updated:', response.data);
  } catch (error) {
    cartMessage.value = 'Error adding to cart: ' + (error.response?.data?.message || 'Unknown error');
    console.error('Error adding to cart:', error.response?.data || error);
  }
};

const submitReview = async () => {
  if (!page.props.auth.user) {
    reviewMessage.value = 'Please log in to submit a review.';
    return;
  }
  if (!newReview.value.rating || !newReview.value.comment) {
    reviewMessage.value = 'Please provide both rating and comment.';
    return;
  }

  try {
    await axios.post(route('api.products.reviews.store', product.value.slug), newReview.value);
    reviewMessage.value = 'Review submitted successfully!';
    newReview.value = { rating: 5, comment: '' }; // Clear form
    fetchReviews(); // Reload reviews
  } catch (error) {
    reviewMessage.value = 'Error submitting review: ' + (error.response?.data?.message || 'Unknown error');
    console.error('Error submitting review:', error.response?.data || error);
  }
};
</script>
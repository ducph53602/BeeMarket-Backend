<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    banner: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <Link :href="banner.target_url || '#'" class="block bg-blue-100 rounded-lg p-6 flex items-center justify-between hover:shadow-lg transition-shadow duration-300"
        :style="{ backgroundImage: `url(${banner.image_url || '/images/default-promo.png'})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
        <div class="text-gray-900 z-10">
            <h3 class="text-xl font-bold mb-1">{{ banner.title }}</h3>
            <p class="text-sm text-gray-700">{{ banner.description || 'Save up to $450' }}</p>
        </div>
        </Link>
</template>
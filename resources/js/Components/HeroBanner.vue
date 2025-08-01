<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    banners: {
        type: Array,
        default: () => [],
    },
});

// For simplicity, pick the first banner for now
const mainBanner = computed(() => {
    // You might want to add a 'type' field to your banner model
    // to distinguish main hero banners from smaller promo banners.
    // For now, let's assume the first banner that includes 'iphone' in target_url is the main one.
    return props.banners.find(b => b.target_url && b.target_url.includes('iphone')) || props.banners[0];
});
</script>

<template>
    <div v-if="mainBanner" class="bg-gray-800 text-white rounded-lg overflow-hidden relative h-96 flex items-center justify-center p-8"
        :style="{ backgroundImage: `url(${mainBanner.image_url || '/images/default-banner.png'})`, backgroundSize: 'cover', backgroundPosition: 'center' }">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="relative z-10 text-center lg:text-left max-w-xl">
            <p class="text-sm uppercase tracking-wider text-gray-300 mb-2">Limited Edition</p>
            <h2 class="text-5xl font-extrabold mb-4 leading-tight">{{ mainBanner.title || 'iPhone 16 Pro Max' }}</h2>
            <p class="text-lg text-gray-200 mb-6">Featuring A18 Chip, Liquid Glass, and AI-Powered Innovation</p>
            <Link :href="mainBanner.target_url || '#'" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-8 rounded-full transition-colors duration-300">
                Shop Now
            </Link>
        </div>
    </div>
    <div v-else class="bg-gray-300 rounded-lg h-96 flex items-center justify-center">
        <p class="text-gray-600">No main banner available</p>
    </div>
</template>
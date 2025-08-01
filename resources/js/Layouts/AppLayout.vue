<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import TheHeader from '@/Components/TheHeader.vue';
// import TheFooter from '@/Components/TheFooter.vue'; // Sẽ tạo sau

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <TheHeader />

        <main>
            <slot />
        </main>

        </div>
</template>
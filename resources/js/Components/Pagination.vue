<script setup>
import { Link } from '@inertiajs/vue3';
import { defineProps, computed } from 'vue';

const props = defineProps({
    links: Array, // Mảng các link phân trang từ Laravel paginate
    currentPage: Number,
    totalPages: Number,
});

const emit = defineEmits(['pageChanged']);

// Tính toán xem có cần hiển thị phân trang không
const shouldShowPagination = computed(() => {
    return props.links && props.links.length > 3; // Kiểm tra nếu có ít nhất 'previous', '1', 'next' và các link số khác
});

// Hàm để điều hướng khi click vào link phân trang
const navigateToPage = (url) => {
    if (url) {
        emit('pageChanged', url);
    }
};
</script>

<template>
    <nav v-if="shouldShowPagination" class="flex justify-center mt-8">
        <ul class="flex items-center space-x-2">
            <li v-for="(link, index) in links" :key="index">
                <button
                    @click="navigateToPage(link.url)"
                    :disabled="!link.url"
                    :class="{
                        'px-4 py-2 rounded-md text-sm font-medium': true,
                        'bg-indigo-600 text-white': link.active,
                        'bg-gray-200 text-gray-700 hover:bg-gray-300': !link.active && link.url,
                        'text-gray-400 cursor-not-allowed': !link.url,
                    }"
                    v-html="link.label"
                ></button>
            </li>
        </ul>
    </nav>
</template>
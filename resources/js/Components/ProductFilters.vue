<template>
  <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
    <form @submit.prevent="submitFilters" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
      <div>
        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Tìm kiếm sản phẩm</label>
        <input
          type="text"
          id="search"
          v-model="form.search"
          placeholder="Tên sản phẩm..."
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        />
      </div>

      <div>
        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Danh mục</label>
        <select
          id="category"
          v-model="form.category_id"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
          <option value="">Tất cả danh mục</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label for="min_price" class="block text-sm font-medium text-gray-700 mb-1">Giá từ</label>
          <input
            type="number"
            id="min_price"
            v-model.number="form.min_price"
            placeholder="Min"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          />
        </div>
        <div>
          <label for="max_price" class="block text-sm font-medium text-gray-700 mb-1">Giá đến</label>
          <input
            type="number"
            id="max_price"
            v-model.number="form.max_price"
            placeholder="Max"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          />
        </div>
      </div>

      <div>
        <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">Sắp xếp theo</label>
        <select
          id="sort_by"
          v-model="form.sort_by"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
          <option value="created_at">Ngày đăng</option>
          <option value="price">Giá</option>
          <option value="name">Tên sản phẩm</option>
        </select>
      </div>

      <div>
        <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Thứ tự</label>
        <select
          id="sort_order"
          v-model="form.sort_order"
          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
        >
          <option value="desc">Giảm dần</option>
          <option value="asc">Tăng dần</option>
        </select>
      </div>

      <div class="col-span-1 md:col-span-full flex justify-end">
        <button
          type="submit"
          class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
        >
          Áp dụng
        </button>
        <button
          type="button"
          @click="resetFilters"
          class="ml-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50"
        >
          Reset
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  categories: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(['update:filters']);

const form = ref({
  search: '',
  category_id: '',
  min_price: '',
  max_price: '',
  sort_by: 'created_at',
  sort_order: 'desc',
});

const submitFilters = () => {
  emit('update:filters', form.value);
};

const resetFilters = () => {
  form.value = {
    search: '',
    category_id: '',
    min_price: '',
    max_price: '',
    sort_by: 'created_at',
    sort_order: 'desc',
  };
  emit('update:filters', form.value);
};

// Có thể thêm watch để tự động apply khi input thay đổi, nhưng với nhiều bộ lọc, dùng nút "Áp dụng" sẽ hiệu quả hơn.
// watch(form, () => {
//   submitFilters();
// }, { deep: true });
</script>
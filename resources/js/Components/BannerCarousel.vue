<template>
  <div v-if="banners.length" class="relative w-full overflow-hidden rounded-lg shadow-md">
    <div
      class="flex transition-transform duration-700 ease-in-out"
      :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
    >
      <div v-for="(banner, index) in banners" :key="banner.id" class="w-full flex-shrink-0">
        <a :href="banner.target_url || '#'" class="block">
          <img
            :src="getBannerImageUrl(banner.image_url)"
            :alt="banner.title"
            class="w-full h-64 md:h-96 object-cover"
          />
        </a>
      </div>
    </div>

    <button
      @click="prevSlide"
      class="absolute top-1/2 left-4 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 focus:outline-none"
    >
      &#10094;
    </button>
    <button
      @click="nextSlide"
      class="absolute top-1/2 right-4 -translate-y-1/2 bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 focus:outline-none"
    >
      &#10095;
    </button>

    <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
      <span
        v-for="(banner, index) in banners"
        :key="index"
        @click="goToSlide(index)"
        :class="['block w-3 h-3 rounded-full bg-white bg-opacity-50 cursor-pointer', { 'bg-opacity-90': index === currentIndex }]"
      ></span>
    </div>
  </div>
  <p v-else class="text-center text-gray-500 py-8">Không có banner nào.</p>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const banners = ref([]);
const currentIndex = ref(0);
let intervalId = null; // Biến để lưu ID của setInterval

// Hàm để lấy URL ảnh, đã được bạn và tôi tối ưu trước đó
const getBannerImageUrl = (path) => {
  if (!path) {
    return '/images/placeholder.png'; // Hoặc đường dẫn ảnh mặc định
  }
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path; // Đã là URL đầy đủ
  }
  if (path.startsWith('/storage/') || path.startsWith('storage/')) {
    return path; // Đã bao gồm 'storage/', sử dụng trực tiếp
  }
  return '/storage/' + path; // Thêm tiền tố '/storage/'
};

// Hàm chuyển đến slide tiếp theo
const nextSlide = () => {
  currentIndex.value = (currentIndex.value + 1) % banners.value.length;
  resetInterval(); // Đặt lại bộ đếm khi người dùng tương tác
};

// Hàm chuyển đến slide trước đó
const prevSlide = () => {
  currentIndex.value = (currentIndex.value - 1 + banners.value.length) % banners.value.length;
  resetInterval(); // Đặt lại bộ đếm khi người dùng tương tác
};

// Hàm chuyển đến một slide cụ thể
const goToSlide = (index) => {
  currentIndex.value = index;
  resetInterval(); // Đặt lại bộ đếm khi người dùng tương tác
};

// Hàm khởi động lại interval
const startCarousel = () => {
  // Clear bất kỳ interval nào đang chạy trước khi tạo cái mới
  if (intervalId) {
    clearInterval(intervalId);
  }

  // Lấy thời gian hiển thị từ banner hiện tại, mặc định 5000ms nếu không có
  const currentBannerInterval = banners.value[currentIndex.value]?.interval || 5000;

  intervalId = setInterval(() => {
    nextSlide();
  }, currentBannerInterval);
};

// Hàm đặt lại interval (dừng và khởi động lại)
const resetInterval = () => {
  startCarousel();
};

onMounted(async () => {
  try {
    const response = await axios.get(route('api.banners.index'));
    banners.value = response.data.data;
    if (banners.value.length > 0) {
      startCarousel(); // Khởi động carousel sau khi tải banner
    }
  } catch (error) {
    console.error('Error fetching banners:', error);
  }
});

// Quan trọng: Dọn dẹp interval khi component bị hủy để tránh rò rỉ bộ nhớ
onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId);
  }
});
</script>

<style scoped>
/* Không cần Bootstrap CSS, chỉ cần Tailwind */
/* Bạn có thể thêm các tùy chỉnh CSS nếu cần */
</style>
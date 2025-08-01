import axios from 'axios';
window.axios = axios;

axios.defaults.baseURL = import.meta.env.VITE_API_URL;; // Đặt URL cơ sở cho tất cả các request
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'; // Header mặc định của Laravel AJAX
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
axios.defaults.withCredentials = true;
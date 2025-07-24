<div class="flex items-center border border-gray-300 rounded-full shadow-sm overflow-hidden p-1 bg-white">
    <input
        type="text"
        id="search-input"
        name="search"
        placeholder="Tìm kiếm sản phẩm..."
        class="flex-grow border-none focus:outline-none focus:ring-0 p-2 text-gray-700 placeholder-gray-400 rounded-full"
    />
    <button
        id="search-button"
        class="bg-blue-600 hover:bg-blue-700 text-white rounded-full px-5 py-2 transition duration-300 ease-in-out"
    >
        Tìm kiếm
    </button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-input');
        const searchButton = document.getElementById('search-button');

        const performSearch = () => {
            const searchTerm = searchInput.value.trim();
            if (searchTerm) {
                // Điều hướng đến trang tìm kiếm hoặc gửi AJAX request
                window.location.href = `{{ route('products.index') }}?search=${encodeURIComponent(searchTerm)}`;
            } else {
                window.location.href = `{{ route('products.index') }}`;
            }
        };

        searchButton.addEventListener('click', performSearch);
        searchInput.addEventListener('keyup', (event) => {
            if (event.key === 'Enter') {
                performSearch();
            }
        });
    });
</script>
@endpush
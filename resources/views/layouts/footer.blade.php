<footer class="bg-gray-800 text-white py-8 mt-12">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-lg font-bold mb-4">BeeMarket</h3>
                <p class="text-sm text-gray-400">
                    Cửa hàng thương mại điện tử hàng đầu của bạn.
                </p>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Liên kết nhanh</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="text-gray-400 hover:text-white transition">Trang chủ</a></li>
                    <li><a href="{{ route('products.index') }}" class="text-gray-400 hover:text-white transition">Sản phẩm</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Về chúng tôi</a></li>
                    <li><a href="#" class="text-gray-400 hover:text-white transition">Liên hệ</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-lg font-bold mb-4">Thông tin liên hệ</h3>
                <p class="text-sm text-gray-400">
                    Địa chỉ: 123 Đường ABC, Quận XYZ, TP. HCM<br>
                    Email: support@beemarket.com<br>
                    Điện thoại: (028) 1234 5678
                </p>
            </div>
        </div>
        <div class="border-t border-gray-700 mt-8 pt-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} BeeMarket. All rights reserved.
        </div>
    </div>
</footer>
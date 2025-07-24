<header class="bg-white">
    <div class="mx-auto max-w-screen-xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="md:flex md:items-center md:gap-12">
                <a href="{{ route('home') }}" class="block text-teal-600">
                    <span class="sr-only">Home</span>
                    <img src="{{ asset('images/beemarket-logo.png') }}" alt="BeeMarket Logo" class="h-8 w-auto" />
                </a>
            </div>

            <div class="hidden md:block">
                <nav aria-label="Global">
                    <ul class="flex items-center gap-6 text-sm">
                        <li>
                            <a class="text-gray-500 transition hover:text-gray-500/75" href="{{ route('home') }}">
                                Trang chủ
                            </a>
                        </li>
                        <li>
                            <a class="text-gray-500 transition hover:text-gray-500/75"
                                href="{{ route('products.index') }}">
                                Sản phẩm
                            </a>
                        </li>
                        <li>
                            <a class="text-gray-500 transition hover:text-gray-500/75" href="#">
                                Về chúng tôi
                            </a>
                        </li>
                        <li>
                            <a class="text-gray-500 transition hover:text-gray-500/75" href="#">
                                Liên hệ
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <div class="sm:flex sm:gap-4">
                    @guest 
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">Đăng nhập</a>
                        <a href="{{ route('register') }}"
                            class="px-4 py-2 text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50">Đăng ký</a>
                    @endguest
                    @auth
                        <a href="{{ route('dashboard') }}"
                            class="rounded-md bg-teal-600 px-5 py-2.5 text-sm font-medium text-white shadow">
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:flex">
                            @csrf
                            <button type="submit"
                                class="rounded-md bg-gray-100 px-5 py-2.5 text-sm font-medium text-teal-600">
                                Đăng xuất
                            </button>
                        </form>
                    @endauth
                </div>

                <div class="block md:hidden">
                    <button id="mobile-menu-toggle"
                        class="rounded bg-gray-100 p-2 text-gray-600 transition hover:text-gray-600/75">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="md:hidden bg-white shadow-lg py-4 hidden">
            <nav aria-label="Mobile Global">
                <ul class="flex flex-col items-center gap-4 text-sm">
                    <li><a class="text-gray-500 transition hover:text-gray-500/75" href="{{ route('home') }}">Trang
                            chủ</a></li>
                    <li><a class="text-gray-500 transition hover:text-gray-500/75"
                            href="{{ route('products.index') }}">Sản phẩm</a></li>
                    <li><a class="text-gray-500 transition hover:text-gray-500/75" href="#">Về chúng tôi</a></li>
                    <li><a class="text-gray-500 transition hover:text-gray-500/75" href="#">Liên hệ</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });
    </script>
@endpush

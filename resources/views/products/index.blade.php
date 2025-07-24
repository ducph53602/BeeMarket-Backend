@extends('layouts.app')

@section('title', 'Tất cả sản phẩm')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-8 text-center">Tất Cả Sản Phẩm</h1>

            <div class="mb-8 flex flex-col md:flex-row gap-4 items-center">
                @include('partials.search-bar') {{-- Dùng lại search bar --}}
                {{-- Thêm bộ lọc danh mục hoặc sắp xếp tại đây nếu muốn --}}
                <div class="relative inline-block text-left">
                    <button type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-100 focus:ring-indigo-500" id="options-menu" aria-haspopup="true" aria-expanded="true">
                        Sắp xếp theo
                        <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    {{-- Dropdown content (sẽ cần JS thuần để toggle) --}}
                    {{-- <div class="origin-top-right absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                        <div class="py-1" role="none">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Giá thấp đến cao</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Giá cao đến thấp</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900" role="menuitem">Mới nhất</a>
                        </div>
                    </div> --}}
                </div>
            </div>

            @if($errorMessage)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Lỗi!</strong>
                    <span class="block sm:inline">{{ $errorMessage }}</span>
                </div>
            @endif

            @if(isset($products['data']) && count($products['data']) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($products['data'] as $product)
                        @include('partials.product-card', ['product' => (object)$product])
                    @endforeach
                </div>

                {{-- Phân trang --}}
                @if(isset($products['links']))
                    <div class="mt-8">
                        {{-- Laravel's default pagination links --}}
                        {{-- Nếu API trả về cấu trúc paginate đầy đủ, bạn có thể dùng: --}}
                        {{-- {{ $products->links() }} --}}

                        {{-- Hoặc tự tạo các nút phân trang thủ công --}}
                        <nav class="flex justify-center items-center space-x-2">
                            @foreach($products['links'] as $link)
                                <a href="{{ $link['url'] }}"
                                   class="px-4 py-2 rounded-lg text-sm
                                   @if($link['active']) bg-blue-600 text-white @else bg-gray-200 text-gray-700 hover:bg-gray-300 @endif
                                   @if(is_null($link['url'])) opacity-50 cursor-not-allowed @endif"
                                >
                                    {!! $link['label'] !!}
                                </a>
                            @endforeach
                        </nav>
                    </div>
                @endif
            @else
                <div class="text-gray-500 text-center py-10">Không tìm thấy sản phẩm nào.</div>
            @endif
        </div>
    </div>
@endsection
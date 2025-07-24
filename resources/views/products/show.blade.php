@extends('layouts.app')

@section('title', $product->name ?? 'Chi tiết sản phẩm')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($errorMessage)
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <strong class="font-bold">Lỗi!</strong>
                    <span class="block sm:inline">{{ $errorMessage }}</span>
                </div>
            @endif

            @if($product)
                <div class="lg:flex lg:gap-8 bg-white p-8 rounded-lg shadow-lg">
                    <div class="lg:w-1/2 mb-8 lg:mb-0">
                        <img src="{{ $product->image_url ?? asset('images/default_product.png') }}"
                             alt="{{ $product->name }}"
                             class="w-full h-auto object-cover rounded-lg shadow-md">
                    </div>

                    <div class="lg:w-1/2">
                        <h1 class="text-4xl font-extrabold text-gray-900 mb-4">{{ $product->name }}</h1>
                        <p class="text-2xl font-bold text-green-600 mb-6">
                            {{ number_format($product->price, 0, ',', '.') }} VNĐ
                        </p>

                        <p class="text-gray-700 leading-relaxed mb-6">
                            {{ $product->description ?? $product->short_description ?? 'Chưa có mô tả cho sản phẩm này.' }}
                        </p>

                        <div class="flex items-center gap-4 mb-6">
                            <input type="number" value="1" min="1" class="w-24 border border-gray-300 rounded-md p-2 text-center text-gray-800 focus:ring-blue-500 focus:border-blue-500" id="quantity">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-md shadow-md transition duration-300 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Thêm vào giỏ hàng
                            </button>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6 text-sm text-gray-600">
                            <p class="mb-2"><strong class="font-semibold text-gray-800">Mã sản phẩm:</strong> SP{{ $product->id }}</p>
                            <p class="mb-2"><strong class="font-semibold text-gray-800">Danh mục:</strong> {{ $product->category->name ?? 'Chưa phân loại' }}</p>
                            <p class="mb-2"><strong class="font-semibold text-gray-800">Trạng thái:</strong> Còn hàng</p>
                        </div>
                    </div>
                </div>

                {{-- <div class="mt-12">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">Sản phẩm liên quan</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @foreach($relatedProducts as $relatedProduct)
                            @include('partials.product-card', ['product' => $relatedProduct])
                        @endforeach
                    </div>
                </div> --}}
            @endif
        </div>
    </div>
@endsection
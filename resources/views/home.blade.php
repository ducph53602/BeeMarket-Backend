@extends('layouts.app')

@section('content')
<x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Trang chủ') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                @include('partials.search-bar') {{-- Sẽ tạo partial view cho search bar --}}
            </div>

            <div class="mb-12">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Ưu Đãi Đặc Biệt</h3>
                {{-- Bạn sẽ cần Controller để truyền dữ liệu banners vào view này --}}
                @if(isset($banners) && count($banners) > 0)
                    @include('partials.banner-display', ['banners' => $banners])
                @else
                    <div class="text-gray-500">Không có banner nào để hiển thị.</div>
                @endif
            </div>

            <h3 class="text-2xl font-bold text-gray-800 mb-4">Sản Phẩm Nổi Bật</h3>
            {{-- Bạn sẽ cần Controller để truyền dữ liệu products vào view này --}}
            @if(isset($products) && count($products) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>
            @else
                <div class="text-gray-500">Không có sản phẩm nào để hiển thị.</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Có thể thêm các script riêng cho trang chủ ở đây --}}
@endpush
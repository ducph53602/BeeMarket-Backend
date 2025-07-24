<div class="relative w-full overflow-hidden rounded-lg shadow-lg">
    <div class="flex">
        @foreach($banners as $banner)
        <div class="w-full flex-shrink-0">
            <a href="{{ $banner->link_url ?? '#' }}" target="_blank">
                <img src="{{ $banner->image_url ?? asset('images/default_banner.png') }}" alt="{{ $banner->title }}" class="w-full h-64 object-cover object-center rounded-lg">
            </a>
            @if($banner->title)
            <div class="absolute bottom-4 left-4 text-white text-lg font-bold">
                {{ $banner->title }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @if(count($banners) === 0)
    <div class="flex items-center justify-center h-64 bg-gray-200 text-gray-600">
        Không có banner nào.
    </div>
    @endif
</div>
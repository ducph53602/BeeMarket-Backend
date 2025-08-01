<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // For Str::slug
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    /**
     * Display a listing of active banners.
     * Public API: GET /api/banners
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('created_at', 'desc')->get();
        // Transform image_url to full URL for public display
        $banners->transform(function ($banner) {
            if ($banner->image_url) {
                $banner->image_url = Storage::url($banner->image_url);
            }
            return $banner;
        });
        return response()->json($banners);
    }

    /**
     * Display the specified banner.
     * Admin API: GET /api/admin/banners/{banner}
     *
     * @param  \App\Models\Banner  $banner // Laravel's Route Model Binding
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Banner $banner): JsonResponse
    {
        if ($banner->image_url) {
            $banner->image_url = Storage::url($banner->image_url);
        }
        return response()->json($banner);
    }

    /**
     * Store a newly created banner in storage.
     * Admin API: POST /api/admin/banners
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Authorization handled by middleware 'role:admin'
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:2048', // Image file
            'target_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $imagePath = $request->file('image')->store('banners', 'public');

        $banner = Banner::create([
            'title' => $request->title,
            'image_url' => $imagePath, // Store relative path
            'target_url' => $request->target_url,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Return with full image URL
        $banner->image_url = Storage::url($banner->image_url);

        return response()->json($banner, 201);
    }

    /**
     * Update the specified banner in storage.
     * Admin API: PUT /api/admin/banners/{banner}
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Banner $banner)
    {
        // Authorization handled by middleware 'role:admin'
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048', // Image file is optional for update
            'target_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $imagePath = $banner->image_url;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
                Storage::disk('public')->delete($banner->image_url);
            }
            $imagePath = $request->file('image')->store('banners', 'public');
        }

        $banner->update([
            'title' => $request->title,
            'image_url' => $imagePath,
            'target_url' => $request->target_url,
            'is_active' => $request->boolean('is_active'),
        ]);

        // Return with full image URL
        $banner->image_url = Storage::url($banner->image_url);

        return response()->json($banner);
    }

    /**
     * Remove the specified banner from storage.
     * Admin API: DELETE /api/admin/banners/{banner}
     *
     * @param  \App\Models\Banner  $banner
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Banner $banner)
    {
        // Authorization handled by middleware 'role:admin'
        if ($banner->image_url && Storage::disk('public')->exists($banner->image_url)) {
            Storage::disk('public')->delete($banner->image_url);
        }

        $banner->delete();
        return response()->json(['message' => 'Banner deleted successfully.'], 200);
    }

    /**
     * Display a listing of all banners (for admin).
     * Admin API: GET /api/admin/banners
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminIndex()
    {
        // Authorization handled by middleware 'role:admin'
        $banners = Banner::latest()->paginate(10); // Get all banners, paginated

        // Transform image_url to full URL for response
        $banners->getCollection()->transform(function ($banner) {
            if ($banner->image_url) {
                $banner->image_url = Storage::url($banner->image_url);
            }
            return $banner;
        });

        return response()->json($banners);
    }
}
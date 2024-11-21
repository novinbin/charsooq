<?php

namespace App\Http\Controllers;

use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function getBanners(string $keys)
    {
        $keys = explode(',', $keys);
        $banners = [];
        foreach (Banner::whereIn('key', $keys)->get() as $banner) {
            $banners[$banner->key] = new BannerResource($banner);
        }
        return $banners;
    }

    public function updateOrCreate(Request $request)
    {
        $banners = [];
        foreach ($request->allFiles() as $key => $file) {
            if($banner = Banner::firstWhere('key', $key)) {
                Storage::disk('public')->delete($banner->photo);
                $banner->photo = $file->store('banners', 'public');
                $banner->save();
                $banners[$key] = $banner;
            } else {
                $banners[$key] = Banner::create([
                    'key' => $key,
                    'photo' => $file->store('banners', 'public'),
                ]);
            }
        }
        foreach ($request->all() as $key => $value) {
            if (!str_contains($key, '_link')) { continue; }
            if (array_key_exists(str_replace('_link', '', $key), $banners)) {
                $banners[str_replace('_link', '', $key)]->update(['link' => $value]);
            } else {
                $banners[str_replace('_link', '', $key)] = Banner::firstOrCreate('key', str_replace('_link', '', $key));
                $banners[str_replace('_link', '', $key)]->update(['link' => $value]);
            }
        }
        return BannerResource::collection(collect($banners));
    }

    public function delete(string $keys)
    {
        $keys = explode(',', $keys);
        foreach (Banner::whereIn('key', $keys)->get() as $banner) {
            $banner->delete();
        }
        return response()->noContent();
    }
}

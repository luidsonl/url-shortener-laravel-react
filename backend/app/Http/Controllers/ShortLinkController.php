<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShortLinkResource;
use App\Models\ShortLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class ShortLinkController extends Controller
{
    /**
     * Get all short links for current user
     */
    public function index(Request $request)
    {
        $user = auth()->guard()->user();
        
        $shortLinks = ShortLink::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 50);
        
        return ShortLinkResource::collection($shortLinks);
    }

    /**
     * Create a new short link
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'original_url' => 'required|url|max:2048',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shortLink = ShortLink::create([
            'user_id' => auth()->guard()->id(),
            'original_url' => $request->original_url,
            'expires_at' => $request->expires_at,
            'clicks' => 0,
        ]);

        return response()->json([
            'message' => 'Short link created',
            'data' => new ShortLinkResource($shortLink)
        ], 201);
    }

    /**
     * Get a specific short link
     */
    public function show(ShortLink $shortLink)
    {
        $user = auth()->guard()->user();
        
        if ($shortLink->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new ShortLinkResource($shortLink);
    }

    /**
     * Update a short link
     */
    public function update(Request $request, ShortLink $shortLink)
    {
        $user = auth()->guard()->user();
        
        if ($shortLink->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'original_url' => 'sometimes|url|max:2048',
            'expires_at' => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $shortLink->update($request->only(['original_url', 'expires_at']));

        return response()->json([
            'message' => 'Short link updated',
            'data' => new ShortLinkResource($shortLink)
        ]);
    }

    /**
     * Delete a short link
     */
    public function destroy(ShortLink $shortLink)
    {
        $user = auth()->guard()->user();
        
        if ($shortLink->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $shortLink->delete();

        return response()->json(['message' => 'Short link deleted'], 200);
    }

    /**
     * Redirect to original URL
     */
    public function redirect($code)
    {
        $originalUrl = Cache::get("code:$code");

        if (!$originalUrl){
            $shortLink = ShortLink::findByCode($code);

            if(!$shortLink){
                Cache::put("code:$code", 'not_found', now()->addMinutes(10));
                return response()->json(['message' => 'Link not found'], 404);
            }

            if ($shortLink->isExpired()){
                Cache::put("code:$code", 'link_expired', now()->addMinutes(10));
                return response()->json(['message' => 'Link expired'], 410);
            }

            if ($shortLink){
                $originalUrl = $shortLink->original_url;
                Cache::put("code:$code", $originalUrl, now()->addMinutes(10));
                return redirect()->away($originalUrl);
            }
        }

        if ($originalUrl === 'not_found'){
            return response()->json(['message' => 'Link not found'], 404);
        }

        if ($originalUrl == 'link_expired'){
            return response()->json(['message' => 'Link expired'], 410);
        }
        
        return redirect()->away($originalUrl);
    }


    /**
     * Bulk delete links
     */
    public function bulkDestroy(Request $request)
    {
        $user = auth()->guard()->user();
        
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:short_links,id,user_id,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deleted = ShortLink::where('user_id', $user->id)
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json([
            'message' => "{$deleted} links deleted"
        ]);
    }
}
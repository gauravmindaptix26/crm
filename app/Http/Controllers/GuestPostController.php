<?php

namespace App\Http\Controllers;

use App\Models\GuestPost;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestPostController extends Controller
{
    // Show the list of guest posts and the add/edit form in the same view
    public function index(Request $request)
{
    $search  = $request->get('search', '');
    $perPage = $request->get('per_page', 10);

    $query = GuestPost::with('country', 'creator');

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('website', 'like', "%{$search}%")
              ->orWhere('da', 'like', "%{$search}%")
              ->orWhere('pa', 'like', "%{$search}%")
              ->orWhere('industry', 'like', "%{$search}%")
              ->orWhere('publisher', 'like', "%{$search}%")
              ->orWhere('traffic', 'like', "%{$search}%")
              ->orWhereHas('country', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
              ->orWhereHas('creator', fn($cq) => $cq->where('name', 'like', "%{$search}%"));
        });
    }

    $guestPosts = $query->paginate($perPage);
    $guestPosts->appends(['search' => $search, 'per_page' => $perPage]);

    $countries = Country::all();

    return view('guest_posts.index', compact('guestPosts', 'countries', 'search', 'perPage'));
}

    // Store a newly created guest post
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'website' => 'required|string|max:255',
                'da' => 'required|integer|min:0',
                'pa' => 'nullable|integer|min:0',
                'industry' => 'nullable|string|max:255',
                'country_id' => 'nullable|exists:countries,id',
                'traffic' => 'nullable|string|max:255',
                'publisher' => 'nullable|string|max:255',
                'publisher_price' => 'nullable|numeric|min:0',
                'our_price' => 'nullable|numeric|min:0',
                'publisher_details' => 'nullable|string',
                'live_link' => 'nullable|url',
            ]);

            $validated['created_by'] = auth()->id();

            $guestPost = GuestPost::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Guest post added successfully!',
                'data' => [
                    'id' => $guestPost->id,
                    'website' => $guestPost->website,
                    'da' => $guestPost->da,
                    'pa' => $guestPost->pa,
                    'industry' => $guestPost->industry,
                    'traffic' => $guestPost->traffic,
                    'publisher' => $guestPost->publisher,
                    'publisher_price' => $guestPost->publisher_price,
                    'our_price' => $guestPost->our_price,
                    'publisher_details' => $guestPost->publisher_details,
                    'live_link' => $guestPost->live_link,
                    'country_id' => $guestPost->country_id,
                    'country_name' => $guestPost->country ? $guestPost->country->name : 'N/A',
                    'creator_name' => $guestPost->creator ? $guestPost->creator->name : 'N/A',
                    'created_at' => $guestPost->created_at->toDateTimeString(),
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating guest post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }

    public function edit(GuestPost $guestPost)
{
    return response()->json([
        'success' => true,
        'data' => [
            'id' => $guestPost->id,
            'website' => $guestPost->website ?? '',
            'da' => $guestPost->da ?? '',
            'pa' => $guestPost->pa ?? '',
            'industry' => $guestPost->industry ?? '',
            'traffic' => $guestPost->traffic ?? '',
            'publisher' => $guestPost->publisher ?? '',
            'publisher_price' => $guestPost->publisher_price ?? '',
            'our_price' => $guestPost->our_price ?? '',
            'publisher_details' => $guestPost->publisher_details ?? '',
            'live_link' => $guestPost->live_link ?? '',
            'country_id' => $guestPost->country_id,
        ]
    ]);
}

    public function update(Request $request, GuestPost $guestPost)
    {
        try {
            $validated = $request->validate([
                'website' => 'required|string|max:255',
                'da' => 'required|integer|min:0',
                'pa' => 'nullable|integer|min:0',
                'industry' => 'nullable|string|max:255',
                'country_id' => 'nullable|exists:countries,id',
                'traffic' => 'nullable|string|max:255',
                'publisher' => 'nullable|string|max:255',
                'publisher_price' => 'nullable|numeric|min:0',
                'our_price' => 'nullable|numeric|min:0',
                'publisher_details' => 'nullable|string',
                'live_link' => 'nullable|url',
            ]);

            $validated['created_by'] = auth()->id();

            $guestPost->update(array_filter($validated));

            return response()->json([
                'success' => true,
                'message' => 'Guest post updated successfully!',
                'data' => [
                    'id' => $guestPost->id,
                    'website' => $guestPost->website,
                    'da' => $guestPost->da,
                    'pa' => $guestPost->pa,
                    'industry' => $guestPost->industry,
                    'traffic' => $guestPost->traffic,
                    'publisher' => $guestPost->publisher,
                    'publisher_price' => $guestPost->publisher_price,
                    'our_price' => $guestPost->our_price,
                    'publisher_details' => $guestPost->publisher_details,
                    'live_link' => $guestPost->live_link,
                    'country_id' => $guestPost->country_id,
                    'country_name' => $guestPost->country ? $guestPost->country->name : 'N/A',
                    'creator_name' => $guestPost->creator ? $guestPost->creator->name : 'N/A',
                    'created_at' => $guestPost->created_at->toDateTimeString(),
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating guest post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }

    public function destroy(GuestPost $guestPost)
    {
        try {
            $guestPost->delete();
            return response()->json([
                'success' => true,
                'message' => 'Guest post deleted successfully!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting guest post: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }
}
    


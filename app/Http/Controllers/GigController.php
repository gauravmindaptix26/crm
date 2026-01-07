<?php

namespace App\Http\Controllers;

use App\Models\Gig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GigController extends Controller
{
    public function index()
    {
        $gigs = Gig::with('user')->latest()->paginate(10);
        return view('gigs.index', compact('gigs'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'website' => 'required|string|max:255',
                'price' => 'required|numeric',
                'gig_link' => 'required|url',
                'gig_on' => 'required|in:Fiverr,PPH,Upwork,Other',
            ]);

            $validated['created_by'] = Auth::id();

            $gig = Gig::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Gig added successfully!',
                'data' => [
                    'id' => $gig->id,
                    'website' => $gig->website,
                    'price' => $gig->price,
                    'gig_link' => $gig->gig_link,
                    'gig_on' => $gig->gig_on,
                    'created_by' => $gig->created_by,
                    'creator_name' => $gig->user ? $gig->user->name : 'N/A',
                    'created_at' => $gig->created_at->toDateTimeString(),
                ],
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating gig: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }

    public function edit(Gig $gig)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $gig->id,
                'website' => $gig->website,
                'price' => $gig->price,
                'gig_link' => $gig->gig_link,
                'gig_on' => $gig->gig_on,
                'created_by' => $gig->created_by,
            ],
        ], 200);
    }

    public function update(Request $request, Gig $gig)
    {
        try {
            $validated = $request->validate([
                'website' => 'required|string|max:255',
                'price' => 'required|numeric',
                'gig_link' => 'required|url',
                'gig_on' => 'required|in:Fiverr,PPH,Upwork,Other',
            ]);

            $validated['created_by'] = Auth::id();

            $gig->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Gig updated successfully!',
                'data' => [
                    'id' => $gig->id,
                    'website' => $gig->website,
                    'price' => $gig->price,
                    'gig_link' => $gig->gig_link,
                    'gig_on' => $gig->gig_on,
                    'created_by' => $gig->created_by,
                    'creator_name' => $gig->user ? $gig->user->name : 'N/A',
                    'created_at' => $gig->created_at->toDateTimeString(),
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating gig: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }

    public function destroy(Gig $gig)
    {
        try {
            $gig->delete();
            return response()->json([
                'success' => true,
                'message' => 'Gig deleted successfully!',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting gig: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }
}

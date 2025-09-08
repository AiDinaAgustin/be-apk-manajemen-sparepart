<?php

namespace App\Http\Controllers;

use App\Models\Sparepart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SparepartController extends Controller
{
    // Hapus constructor dengan authorizeResource
    
    /**
     * Display a listing of the resource.
     */
    public function index() : JsonResponse
    {
        $spareparts = Sparepart::all();
        
        return response()->json([
            'success' => true,
            'data' => $spareparts
        ]);
    }

    /**
     * Show simple list for staff
     */
    public function list() : JsonResponse
    {
        $spareparts = Sparepart::select('id', 'name_sparepart', 'stok')->get();
        
        return response()->json([
            'success' => true,
            'data' => $spareparts
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) : JsonResponse
    {
        $validated = $request->validate([
            'name_sparepart' => 'required|string|max:255',
            'minimal_stok' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
        ]);

        $sparepart = Sparepart::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Sparepart created successfully',
            'data' => $sparepart
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Sparepart $sparepart) : JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $sparepart
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sparepart $sparepart): JsonResponse
    {
            // Validasi input
            $validated = $request->validate([
                'name_sparepart' => 'sometimes|string|max:255',
                'minimal_stok' => 'sometimes|integer|min:0',
                'stok' => 'sometimes|integer|min:0',
            ]);
    
            $sparepart->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Sparepart updated successfully',
                'data' => $sparepart
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sparepart $sparepart) : JsonResponse
    {
        $sparepart->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Sparepart deleted successfully'
        ]);
    }
}
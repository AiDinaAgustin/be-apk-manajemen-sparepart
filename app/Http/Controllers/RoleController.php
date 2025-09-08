<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with their permissions.
     */
    public function index(): JsonResponse
    {
        // Ambil semua role dengan permission yang terkait
        $roles = Role::with('permissions')->get();
        
        // Format data untuk response
        $formattedRoles = $roles->map(function($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedRoles
        ]);
    }
    
    /**
     * Display a specific role with its permissions.
     */
    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');
        
        $roleData = [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name'),
            'created_at' => $role->created_at,
            'updated_at' => $role->updated_at
        ];
        
        return response()->json([
            'success' => true,
            'data' => $roleData
        ]);
    }
    
    /**
     * Get all available permissions in the system.
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all()->pluck('name');
        
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = User::with('roles')->get();
        
        // Transform users to include all permissions
        $users = $users->map(function($user) {
            $userData = $user->toArray();
            $userData['all_permissions'] = $user->getAllPermissions()->pluck('name');
            return $userData;
        });
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);
        
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user->load('roles', 'permissions')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $userData = $user->load('roles')->toArray();
        $userData['all_permissions'] = $user->getAllPermissions()->pluck('name');
        
        return response()->json([
            'success' => true,
            'data' => $userData
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => ['sometimes', 'string', Password::defaults()],
        ]);
        
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        
        $user->update($validated);
        
        $userData = $user->fresh()->load('roles')->toArray();
        $userData['all_permissions'] = $user->getAllPermissions()->pluck('name');
        
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $userData
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        if (auth()->id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }
        
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
    
    /**
     * Assign roles to a user
     */
    public function syncRoles(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
        
        $user->syncRoles($validated['roles']);
        
        $userData = $user->fresh()->load('roles')->toArray();
        $userData['all_permissions'] = $user->getAllPermissions()->pluck('name');
        
        return response()->json([
            'success' => true,
            'message' => 'User roles updated successfully',
            'data' => $userData
        ]);
    }
}
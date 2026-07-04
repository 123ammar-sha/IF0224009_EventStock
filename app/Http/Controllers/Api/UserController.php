<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Gate::denies('manageUsers')) return response()->json(['message' => 'Unauthorized'], 403);
        return response()->json(['data' => User::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Gate::denies('manageUsers')) return response()->json(['message' => 'Hanya Super Admin yang bisa menambah kru'], 403);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,warehouse_manager,field_crew'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role']
        ]);

        return response()->json(['message' => 'User berhasil dibuat', 'data' => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (Gate::denies('manageUsers')) return response()->json(['message' => 'Unauthorized'], 403);
        $user = User::findOrFail($id);
        return response()->json(['data' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Gate::denies('manageUsers')) return response()->json(['message' => 'Akses ditolak'], 403);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'role' => 'sometimes|in:super_admin,warehouse_manager,field_crew',
            'password' => 'sometimes|nullable|min:6'
        ]);

        // Jika password diisi, hash passwordnya
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json(['message' => 'User berhasil diperbarui', 'data' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Gate::denies('manageUsers')) return response()->json(['message' => 'Akses ditolak'], 403);

        $user = User::findOrFail($id);

        // Mencegah admin menghapus dirinya sendiri agar tidak terkunci dari sistem
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri'], 400);
        }

        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus']);
    }
}

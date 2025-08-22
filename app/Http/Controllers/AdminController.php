<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    //
    public function index()
    {
        // Logic to list all admins
        return response()->json([
            'success' => true,
            'message' => 'List of admins',
            'data' => Admin::all()
        ]);
    }

    public function show($id)
    {
        // Logic to show a specific admin
        $admin = Admin::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Admin details',
            'data' => $admin
        ]);
    }

    public function store(Request $request)
    {
        // Logic to create a new admin
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:admin,email',
            'no_hp' => 'required|string|max:15',
            'role' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username' => $request->input('username'),
                'email' => $validated['email'],
                'password' => bcrypt($request->input('password')),
            ]);

            $admin = Admin::create([
                'user_id' => $user->id,
                'nama_lengkap' => $validated['nama_lengkap'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'role' => $validated['role'],
                'status' => $validated['status'],
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin',
                'error' => $e->getMessage()
            ], 500);
        }

        $admin = Admin::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Admin created successfully',
            'data' => $admin
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing admin
        $admin = Admin::findOrFail($id);
        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:admin,email,' . $admin->id,
            'no_hp' => 'sometimes|string|max:15',
            'role' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive',
        ]);

        DB::beginTransaction();
        try {
            $admin->update($validated);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully',
                'data' => $admin
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        // Logic to delete an admin
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ], 200);
    }
}

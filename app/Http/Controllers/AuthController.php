<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function jurusanIndex()
    {
        $jurusan = Jurusan::all();

        if ($jurusan->isEmpty()) {
            return response()->json(['message' => 'No jurusan found'], 404);
        }

        return response()->json($jurusan, 200);
    }

    public function register(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        $fields['password'] = Hash::make($fields['password']);
        $fields['role'] = 'assesi'; // Default role is 'user'

        $user = User::create($fields);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->only(['id', 'jurusan_id', 'role'])
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'input' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->input)
            ->orWhere('username', $request->input)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('Login Token');

        return response()->json([
            'message' => 'login success',
            'token' => $token->plainTextToken,
            'user' => $user->only(['id', 'jurusan_id', 'role'])
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'logout success'], 200);
    }
}

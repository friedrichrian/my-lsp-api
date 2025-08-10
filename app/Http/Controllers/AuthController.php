<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function jurusanIndex()
    {
        $jurusan = Jurusan::all();
        return response()->json($jurusan);
    }
    
    public function register(Request $request){

        $fields = $request->validate([
            'username' => 'required',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);
        
        $fields['password'] = bcrypt($fields['password']);
        
        $user = User::create($fields);

        $token = $user->createToken($request->username);

        return response()->json([
            'message' => 'User created successfully',
            'token' => $token->plainTextToken,
            'user' => $user
        ], 201);
    }

    public function login(Request $request){
        $request->validate([
            'input' => 'required',
            'password' => 'required'
        ]);

        $email = User::where('email', $request->input)->first();
        $username = User::where('username', $request->input)->first();

        if(!$email && !$username){
            return response()->json(['message' => 'User not found'], 404);
        }
        
        $user = $email ?: $username;
        
        if((!$email || !$username) && !Hash::check($request->password, $user->password)){
            return response()->json(['message' => 'Wrong email or Password'], 401);
        }

        $token = $user->createToken($user->username);

        return [
            'message' => 'login success',
            'token' => $token->plainTextToken,
            'user' => $user
        ];
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();

        return ['message' => 'logout success'];
    }
}

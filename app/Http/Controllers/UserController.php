<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Assesor;

class UserController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth()->user()->assesi;
        if (!$user) {
            return response()->json(['error' => 'no asesi found'], 404);
        }
        return response()->json(['user' =>$user], 200);
    }

    public function showAssesor(Request $request)
    {
        $user = Auth()->user()->assesor;
        if (!$user) {
            return response()->json(['error' => 'no assesor found'], 404);
        }
        return response()->json([
            'user' => User::with('assesor')->find($user->user_id)
        ], 200);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}

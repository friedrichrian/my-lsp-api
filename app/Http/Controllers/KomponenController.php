<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Komponen;

class KomponenController extends Controller
{
    public function index(){
        $data = Komponen::all();
        return response()->json([
            'success' => true,
            'data' => $data
        ], 200);
    }
}

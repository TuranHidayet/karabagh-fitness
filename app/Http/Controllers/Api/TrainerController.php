<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    // Bütün trainerləri göstər
    public function index()
    {
        $trainers = User::role('trainer')->get();
        return response()->json($trainers);
    }

    // Tək trainer göstər
    public function show($id)
    {
        $trainer = User::role('trainer')->findOrFail($id);
        return response()->json($trainer);
    }

    // Trainer sil
    public function destroy($id)
    {
        $trainer = User::role('trainer')->findOrFail($id);
        $trainer->delete();

        return response()->json([
            'message' => 'Trainer silindi'
        ]);
    }
}


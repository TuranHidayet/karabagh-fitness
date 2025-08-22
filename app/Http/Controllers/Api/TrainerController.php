<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\CommonHelper;

class TrainerController extends Controller
{
    // Bütün trainerləri göstər
    public function index()
    {
        $trainers = User::role('trainer')->get();
        return CommonHelper::jsonResponse('success', 'Bütün trainerlər uğurla gətirildi', $trainers);
    }

    // Tək trainer göstər
    public function show($id)
    {
        $trainer = User::role('trainer')->findOrFail($id);
        return CommonHelper::jsonResponse('success', 'Trainer məlumatı uğurla gətirildi', $trainer);
    }

    // Trainer sil
    public function destroy($id)
    {
        $trainer = User::role('trainer')->findOrFail($id);
        $trainer->delete();

        return CommonHelper::jsonResponse('success', 'Trainer uğurla silindi', null);
    }
}

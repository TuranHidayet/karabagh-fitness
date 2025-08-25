<?php

namespace App\Http\Controllers;

use App\Models\LogEntry;
use Illuminate\Http\Request;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $logs = LogEntry::latest()->paginate(50);

        return response()->json([
            'status' => 'success',
            'data'   => $logs
        ]);
    }
}

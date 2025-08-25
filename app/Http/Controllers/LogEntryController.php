<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogEntry;

class LogEntryController extends Controller
{
    public function index()
    {
        $logs = LogEntry::latest()->paginate(50);
        return view('logs.index', compact('logs'));
    }
}

<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\LogEntry;

class CommonHelper
{
    /**
     * Şəkil yükləmə funksiyası
     */
    public static function uploadImage($image, $folder = 'uploads')
    {
        if (!$image) return null;

        if (is_string($image) && str_starts_with($image, 'data:image')) {
            @list($type, $file_data) = explode(';', $image);
            @list(, $file_data) = explode(',', $file_data);

            $filename = time() . '.png';
            Storage::disk('public')->put($folder . '/' . $filename, base64_decode($file_data));
            return $folder . '/' . $filename;
        }

        if (is_object($image)) {
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->storeAs($folder, $filename, 'public');
            return $folder . '/' . $filename;
        }

        return null;
    }

    /**
     * Package / Campaign üçün end date hesabla
     */
    public static function calculateEndDate($startDate, $model)
    {
        $start = Carbon::parse($startDate);

         if ($model->getTable() === 'packages') {
            // duration_type yoxdursa default days kimi qəbul edirik
            if (!empty($model->duration_days) && $model->duration_days > 0) {
                $end = $start->copy()->addDays($model->duration_days);
            } elseif (!empty($model->duration) && $model->duration > 0) {
                // duration ay kimi nəzərə alınır
                $end = $start->copy()->addMonths($model->duration);
            } else {
                $end = $start;
            }
        } elseif ($model->getTable() === 'campaigns') {
            $end = $start->copy()->addMonths($model->duration_months);
        } else {
            $end = $start;
        }

        return $end->format('Y-m-d');
    }

    /**
     * Ortak JSON response
     */
    public static function jsonResponse($status = 'success', $message = '', $data = null, $code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    // public static function add($message, $level = 'info', $payload = [])
    // {
    //     $request = request();

    //     return LogEntry::create([
    //         'message'  => $message,             
    //         'level'    => $level,               
    //         'method'   => $request->method(),   
    //         'path'     => $request->path(),     
    //         'ip'       => $request->ip(),      
    //         'user_id'  => Auth::id(),           
    //         'payload'  => $payload,            
    //         'ua'       => $request->userAgent()
    //     ]);
    // }
}

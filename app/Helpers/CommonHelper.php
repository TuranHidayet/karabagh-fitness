<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
            switch ($model->duration_type) {
                case 'day': $end = $start->copy()->addDays($model->duration); break;
                case 'month': $end = $start->copy()->addMonths($model->duration); break;
                case 'year': $end = $start->copy()->addYears($model->duration); break;
                default: $end = $start;
            }
        } else if ($model->getTable() === 'campaigns') {
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
}

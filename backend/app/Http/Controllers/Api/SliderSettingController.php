<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SliderSetting;

class SliderSettingController extends Controller
{
    /**
     * GET /api/slider-setting
     * Public - Homepage
     */
    public function show()
    {
        $setting = SliderSetting::first();

        return response()->json([
            'message' => 'Slider setting',
            'data' => $setting
        ], 200);
    }

    /**
     * POST /api/slider-setting
     * Admin only
     */
    public function store(Request $request)
    {
        $request->validate([
            'main_title'     => 'nullable|string|max:255',
            'title'          => 'nullable|string|max:255',
            'description'    => 'nullable|string',
            'subdescription' => 'nullable|string',
        ]);

        $setting = SliderSetting::updateOrCreate(
            ['id' => 1], // selalu 1 row (GLOBAL)
            [
                'main_title'     => $request->main_title,
                'title'          => $request->title,
                'description'    => $request->description,
                'subdescription' => $request->subdescription,
            ]
        );

        return response()->json([
            'message' => 'Pengaturan slider berhasil disimpan',
            'data' => $setting
        ]);
    }
}

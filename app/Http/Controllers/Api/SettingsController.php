<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::all()->groupBy('group');
        return response()->json($settings);
    }

    public function get(string $group): JsonResponse
    {
        $settings = Setting::where('group', $group)->get();
        return response()->json($settings);
    }

    public function set(Request $request): JsonResponse
    {
        $data = $request->validate([
            'key' => 'required|string|max:255',
            'value' => 'nullable|string',
            'group' => 'nullable|string|max:100',
        ]);

        $setting = Setting::updateOrCreate(
            ['key' => $data['key']],
            $data
        );

        return response()->json($setting);
    }

    public function update(Request $request, Setting $setting): JsonResponse
    {
        $setting->update($request->validate([
            'value' => 'nullable|string',
            'group' => 'nullable|string|max:100',
        ]));
        return response()->json($setting);
    }

    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();
        return response()->json(['message' => 'Setting deleted']);
    }
}

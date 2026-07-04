<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return response()->json(['data' => $settings]);
    }

    public function getByGroup(string $group)
    {
        $settings = Setting::getByGroup($group);
        return response()->json(['data' => $settings]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string'
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    public function updateGroup(Request $request, string $group)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string'
        ]);

        Setting::setGroup($group, $validated['settings']);

        return response()->json(['message' => "Settings for group '{$group}' updated successfully"]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\SettingResource;
use App\Models\Setting;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $group    = $request->input('group');
        $query    = Setting::query();

        if ($group) {
            $query->where('group', $group);
        }

        $settings = $query->orderBy('group')->orderBy('key')->get();

        return $this->success(SettingResource::collection($settings), 'Settings fetched successfully');
    }

    public function update(Request $request): JsonResponse
    {
        if (!$request->user()->isAdmin()) {
            return $this->forbidden();
        }

        $data = $request->validate([
            'settings'       => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:100'],
            'settings.*.value' => ['present', 'nullable', 'string', 'max:1000'],
        ]);

        foreach ($data['settings'] as $item) {
            Setting::set($item['key'], $item['value'] ?? '');
        }

        return $this->noContent('Settings updated successfully');
    }
}

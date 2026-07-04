<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class EmailConfigController extends Controller
{
    public function index()
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $lines = explode("\n", $envContent);
        $config = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || $trimmed[0] === '#') continue;

            if (str_contains($trimmed, '=')) {
                [$key, $value] = explode('=', $trimmed, 2);
                $key = trim($key);
                $value = trim($value);

                if (str_starts_with($key, 'MAIL_')) {
                    $config[$key] = $value;
                }
            }
        }

        return response()->json(['data' => $config]);
    }
}

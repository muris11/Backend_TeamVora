<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class AdminEnvController extends Controller
{
    private array $sensitivePatterns = ['PASSWORD', 'SECRET', 'KEY', 'TOKEN', 'CREDENTIAL'];

    public function index()
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        $lines = explode("\n", $envContent);
        $config = [];

        foreach ($lines as $index => $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || $trimmed[0] === '#') continue;

            if (str_contains($trimmed, '=')) {
                [$key, $value] = explode('=', $trimmed, 2);
                $key = trim($key);
                $value = trim($value);

                $config[$key] = [
                    'value' => $value,
                    'masked' => false,
                    'line' => $index,
                ];
            }
        }

        return response()->json(['data' => $config]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $envPath = base_path('.env');
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($validated['settings'] as $key => $value) {
            // Find the line with this key
            $found = false;
            foreach ($lines as &$line) {
                $trimmed = trim($line);
                if (str_starts_with($trimmed, $key . '=')) {
                    $line = $key . '=' . $value;
                    $found = true;
                    break;
                }
            }
            unset($line);

            // Add new key if not found
            if (! $found) {
                $lines[] = $key . '=' . $value;
            }
        }

        file_put_contents($envPath, implode("\n", $lines) . "\n");

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return response()->json(['message' => 'Konfigurasi .env berhasil diperbarui.']);
    }

    private function isSensitive(string $key): bool
    {
        $upper = strtoupper($key);
        foreach ($this->sensitivePatterns as $pattern) {
            if (str_contains($upper, $pattern)) return true;
        }
        return false;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;

class SseController extends Controller
{
    public function stream(Request $request)
    {
        $token = $request->query('token');
        if (! $token) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (! $accessToken) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user = $accessToken->tokenable;
        if (! $user || ! ($user instanceof User)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $activeUsersKey = 'sse_active_users';
        $userId = (string) $user->id;

        $lock = Cache::lock('sse_active_users_lock', 5);
        $lock->block(3);
        try {
            $activeUsers = Cache::get($activeUsersKey, []);
            $activeUsers[$userId] = now()->timestamp;
            Cache::put($activeUsersKey, $activeUsers, 35);
        } finally {
            $lock->forceRelease();
        }

        return response()->stream(function () use ($user, $isSuperAdmin, $activeUsersKey, $userId) {
            $this->sendEvent('connected', [
                'user_id' => $user->id,
                'timestamp' => now()->toIso8601String(),
            ]);

            $lastNotificationTime = now();
            $lastTeamUpdate = now()->timestamp;
            $heartbeatInterval = 15; 
            $lastHeartbeat = time();
            $lastAdminStats = 0;

            try {
                while (true) {
                    if (connection_aborted()) {
                        break;
                    }
                    $lock = Cache::lock('sse_active_users_lock', 5);
                    $lock->block(3);
                    try {
                        $activeUsers = Cache::get($activeUsersKey, []);
                        $activeUsers[$userId] = now()->timestamp;
                        Cache::put($activeUsersKey, $activeUsers, 35);
                    } finally {
                        $lock->forceRelease();
                    }
                    if (time() - $lastHeartbeat >= $heartbeatInterval) {
                        $this->sendEvent('heartbeat', [
                            'timestamp' => now()->toIso8601String(),
                        ]);
                        $lastHeartbeat = time();
                    }
                    if ($isSuperAdmin && (time() - $lastAdminStats >= 10)) {
                        $activeUsers = Cache::get('sse_active_users', []);
                        $activeCount = count($activeUsers);
                        $this->sendEvent('admin_stats', [
                            'active_users' => $activeCount,
                            'timestamp' => now()->toIso8601String(),
                        ]);
                        $lastAdminStats = time();
                    }
                    $newNotifications = $user->notifications()
                        ->where('created_at', '>', $lastNotificationTime)
                        ->orderBy('created_at', 'asc')
                        ->get();

                    foreach ($newNotifications as $notification) {
                        $this->sendEvent('notification', [
                            'id' => $notification->id,
                            'type' => $notification->type,
                            'title' => $notification->data['title'] ?? 'Notifikasi',
                            'message' => $notification->data['message'] ?? '',
                            'url' => $notification->data['url'] ?? null,
                            'read_at' => $notification->read_at?->toIso8601String(),
                            'created_at' => $notification->created_at->toIso8601String(),
                        ]);
                        if ($notification->created_at > $lastNotificationTime) {
                            $lastNotificationTime = $notification->created_at;
                        }
                    }

                    if ($user->team_id) {
                        $teamUpdateCheck = User::where('team_id', $user->team_id)
                            ->where('updated_at', '>', now()->subSeconds(5))
                            ->pluck('updated_at')
                            ->max();

                        if ($teamUpdateCheck && $teamUpdateCheck->timestamp > $lastTeamUpdate) {
                            $this->sendEvent('team_updated', [
                                'team_id' => $user->team_id,
                                'timestamp' => now()->toIso8601String(),
                            ]);
                            $lastTeamUpdate = $teamUpdateCheck->timestamp;
                        }
                    }
                    if (ob_get_level()) {
                        ob_end_flush();
                    }
                    flush();

                usleep(1000000);
                }
            } finally {

            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-transform',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept',
        ]);
    }

    public function options(): \Illuminate\Http\Response
    {
        return response('', 204, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With, Accept',
            'Access-Control-Max-Age' => '86400',
        ]);
    }

    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";

        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();
    }
}

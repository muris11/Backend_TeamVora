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
        // Auth via query string token (EventSource doesn't support custom headers)
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

        // Add user to active set
        $activeUsers = Cache::get($activeUsersKey, []);
        $activeUsers[$userId] = now()->timestamp;
        Cache::put($activeUsersKey, $activeUsers, 35);

        return response()->stream(function () use ($user, $isSuperAdmin, $activeUsersKey, $userId) {
            // Send initial connection event
            $this->sendEvent('connected', [
                'user_id' => $user->id,
                'timestamp' => now()->toIso8601String(),
            ]);

            $lastNotificationId = 0;
            $lastTeamUpdate = now()->timestamp;
            $heartbeatInterval = 15; // seconds
            $lastHeartbeat = time();
            $lastAdminStats = 0;

            try {
                while (true) {
                    // Check if client disconnected
                    if (connection_aborted()) {
                        break;
                    }

                    // Refresh active user heartbeat
                    $activeUsers = Cache::get($activeUsersKey, []);
                    $activeUsers[$userId] = now()->timestamp;
                    Cache::put($activeUsersKey, $activeUsers, 35);

                    // Heartbeat to keep connection alive
                    if (time() - $lastHeartbeat >= $heartbeatInterval) {
                        $this->sendEvent('heartbeat', [
                            'timestamp' => now()->toIso8601String(),
                        ]);
                        $lastHeartbeat = time();
                    }

                    // Send admin stats every 10 seconds for super admins
                    if ($isSuperAdmin && (time() - $lastAdminStats >= 10)) {
                        $activeUsers = Cache::get('sse_active_users', []);
                        $activeCount = count($activeUsers);
                        $this->sendEvent('admin_stats', [
                            'active_users' => $activeCount,
                            'timestamp' => now()->toIso8601String(),
                        ]);
                        $lastAdminStats = time();
                    }

                    // Check for new notifications
                    $newNotifications = $user->notifications()
                        ->where('id', '>', $lastNotificationId)
                        ->orderBy('id', 'asc')
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
                        $lastNotificationId = $notification->id;
                    }

                    // Check for team member changes (if user is in a team)
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

                    // Flush output buffer
                    if (ob_get_level()) {
                        ob_end_flush();
                    }
                    flush();

                    // Sleep 1 second between checks
                    usleep(1000000); // 1 second
                }
            } finally {
                // Remove active user marker on disconnect
                $activeUsers = Cache::get($activeUsersKey, []);
                unset($activeUsers[$userId]);
                Cache::put($activeUsersKey, $activeUsers, 35);
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

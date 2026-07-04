<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'boolean',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load('roles', 'team')),
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'team_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->filled('team_name')) {
            $team = Team::create([
                'name' => $request->team_name,
                'slug' => Str::slug($request->team_name),
                'description' => '',
                'leader_id' => $user->id,
            ]);

            $user->update([
                'team_id' => $team->id,
                'role' => 'team_leader',
            ]);

            $user->syncRoles('team_leader');
        } else {
            $user->assignRole('member');
            $user->update(['role' => 'member']);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load('roles', 'team')),
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles', 'team');

        $resource = new UserResource($user);

        // Check if this is an impersonation token
        $token = $request->user()->currentAccessToken();
        $impersonatorId = $token->abilities[0] ?? null;

        if (str_starts_with((string) $impersonatorId, 'impersonate:')) {
            $impersonatorId = (int) str_replace('impersonate:', '', (string) $impersonatorId);
            $impersonator = User::find($impersonatorId);

            if ($impersonator) {
                return $resource->additional([
                    'impersonator' => [
                        'id' => $impersonator->id,
                        'name' => $impersonator->name,
                        'email' => $impersonator->email,
                    ],
                ]);
            }
        }

        return $resource;
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $request->user()->update($request->only('name', 'email', 'phone'));

        return new UserResource($request->user()->fresh()->load('roles', 'team'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'Email tidak terdaftar.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetLink = 'http://localhost:3000/reset-password?token=' . $token . '&email=' . urlencode($user->email);

        $settings = DB::table('platform_settings')->where('key', 'general')->first();
        $emailSettings = DB::table('platform_settings')->where('key', 'email')->first();
        
        $settingsData = [];
        if ($settings) {
            $generalData = json_decode($settings->value, true);
            $settingsData['email_logo_url'] = $generalData['logo_url'] ?? null;
        }
        if ($emailSettings) {
            $emailData = json_decode($emailSettings->value, true);
            $settingsData['email_sender_name'] = $emailData['email_sender_name'] ?? 'TeamVora';
            $settingsData['email_reply_to'] = $emailData['email_reply_to'] ?? null;
        }

        Mail::send('emails.reset_password', ['resetLink' => $resetLink, 'settings' => $settingsData], function ($message) use ($user, $settingsData) {
            $message->to($user->email)
                ->subject('Reset Password TeamVora');
            
            if (!empty($settingsData['email_reply_to'])) {
                $message->replyTo($settingsData['email_reply_to']);
            }
        });

        return response()->json(['message' => 'Link reset password telah dikirim ke email Anda.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $resetRecord || ! Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['message' => 'Token tidak valid atau sudah kedaluwarsa.'], 422);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token sudah kedaluwarsa. Silakan minta reset password baru.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset. Silakan login.']);
    }

    /**
     * Impersonate a user — creates a new token with impersonator_id in abilities.
     * Only Super Admin can impersonate.
     */
    public function impersonate(Request $request, $userId)
    {
        if (! $request->user()->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $targetUser = User::find($userId);
        if (! $targetUser) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Don't allow impersonating yourself
        if ($targetUser->id === $request->user()->id) {
            return response()->json(['message' => 'Tidak bisa impersonate diri sendiri.'], 422);
        }

        // Delete previous impersonation token if any
        $request->user()->tokens()
            ->where('name', 'impersonation')
            ->where('id', '!=', $request->user()->currentAccessToken()->id)
            ->delete();

        // Create impersonation token with impersonator_id in abilities
        $token = $targetUser->createToken(
            'impersonation',
            ['impersonate:' . $request->user()->id]
        )->plainTextToken;

        // Log the impersonation activity
        DB::table('activity_log')->insert([
            'log_name' => 'impersonation',
            'description' => $request->user()->name . ' impersonated as ' . $targetUser->name,
            'subject_type' => User::class,
            'subject_id' => $targetUser->id,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'properties' => json_encode([
                'impersonator_id' => $request->user()->id,
                'impersonator_name' => $request->user()->name,
                'target_id' => $targetUser->id,
                'target_name' => $targetUser->name,
            ]),
            'event' => 'impersonated',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'user' => new UserResource($targetUser->load('roles', 'team')),
            'token' => $token,
            'impersonator' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    }

    /**
     * Stop impersonation — return to original admin user.
     */
    public function stopImpersonation(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        $abilities = $token->abilities ?? [];
        $firstAbility = $abilities[0] ?? null;

        if (! str_starts_with((string) $firstAbility, 'impersonate:')) {
            return response()->json(['message' => 'Tidak dalam mode impersonate.'], 422);
        }

        $impersonatorId = (int) str_replace('impersonate:', '', (string) $firstAbility);
        $impersonator = User::find($impersonatorId);

        if (! $impersonator) {
            return response()->json(['message' => 'User asal tidak ditemukan.'], 404);
        }

        // Delete the impersonation token
        $token->delete();

        // Create new token for the impersonator (admin)
        $newToken = $impersonator->createToken('auth-token')->plainTextToken;

        // Log the stop impersonation
        DB::table('activity_log')->insert([
            'log_name' => 'impersonation',
            'description' => $impersonator->name . ' stopped impersonating ' . $request->user()->name,
            'subject_type' => User::class,
            'subject_id' => $impersonator->id,
            'causer_type' => User::class,
            'causer_id' => $request->user()->id,
            'properties' => json_encode([
                'action' => 'stop_impersonation',
                'impersonator_id' => $impersonator->id,
                'impersonator_name' => $impersonator->name,
                'target_id' => $request->user()->id,
                'target_name' => $request->user()->name,
            ]),
            'event' => 'stopped_impersonation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'user' => new UserResource($impersonator->load('roles', 'team')),
            'token' => $newToken,
        ]);
    }
}

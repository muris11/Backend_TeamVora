<?php

use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\SseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\BillItemController;
use App\Http\Controllers\Api\CashBookController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DailyLogController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RecurringBillController;
use App\Http\Controllers\Api\SplitBillController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamInvitationController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\AdminTicketController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);

// Public blog
Route::get('blogs/public', [BlogController::class, 'index']);
Route::get('blogs/{slug}', [BlogController::class, 'show'])->where('slug', '^(?!manage$).*');

// Invitation accept (public)
Route::post('invitations/{token}/accept', [TeamInvitationController::class, 'accept']);
Route::get('invitations/{token}', [TeamInvitationController::class, 'show']);

use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\AdminEnvController;
use App\Http\Controllers\Api\AdminPlatformController;

// Public contact form
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:3,1');

// Public platform settings
Route::get('platform-settings', [AdminPlatformController::class, 'getSettings']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('settings', [SettingController::class, 'index']);
    Route::post('settings', [SettingController::class, 'update']);
    Route::get('settings/{group}', [SettingController::class, 'getByGroup']);
    Route::put('settings/{group}', [SettingController::class, 'updateGroup']);
    
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::post('profile/avatar', [AuthController::class, 'updateAvatar']);
    Route::put('password', [AuthController::class, 'updatePassword']);
    Route::post('impersonate/{userId}', [AuthController::class, 'impersonate']);
    Route::post('stop-impersonation', [AuthController::class, 'stopImpersonation']);

    // Teams
    Route::put('teams/settings', [TeamController::class, 'updateSettings']);
    Route::post('teams/logo', [TeamController::class, 'uploadLogo']);
    Route::apiResource('teams', TeamController::class);
    Route::get('teams/{team}/members', [TeamController::class, 'members']);
    Route::post('teams/{team}/invite', [TeamController::class, 'invite']);
    Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember']);
    Route::post('teams/switch', [TeamController::class, 'switchTeam']);

    // Team members shortcut (for member/lead pages that don't have team_id in URL)
    Route::get('team-members', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (! $user->team_id) {
            return \App\Http\Resources\UserResource::collection(collect());
        }
        $members = \App\Models\User::where('team_id', $user->team_id)->with('roles')->get();
        return \App\Http\Resources\UserResource::collection($members);
    });
    Route::get('team/members', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        if (! $user->team_id) {
            return \App\Http\Resources\UserResource::collection(collect());
        }
        $members = \App\Models\User::where('team_id', $user->team_id)->with('roles')->get();
        return \App\Http\Resources\UserResource::collection($members);
    });

    // Dashboard
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('dashboard/member', [DashboardController::class, 'member']);

    // Email Settings (Superadmin only)
    Route::middleware([\App\Http\Middleware\CheckRole::class.':super_admin'])->group(function () {
        Route::get('/email-settings', [\App\Http\Controllers\Api\EmailTemplateController::class, 'getSettings']);
        Route::post('/email-settings', [\App\Http\Controllers\Api\EmailTemplateController::class, 'updateSettings']);
        Route::get('/email-settings/preview', [\App\Http\Controllers\Api\EmailTemplateController::class, 'getPreview']);
        Route::get('/email-config', [\App\Http\Controllers\Api\EmailConfigController::class, 'index']);
    });

    // Cash Book
    Route::get('cash-books', [CashBookController::class, 'index']);
    Route::post('cash-books', [CashBookController::class, 'store']);
    Route::get('cash-books/{cashBook}', [CashBookController::class, 'show']);
    Route::put('cash-books/{cashBook}', [CashBookController::class, 'update']);
    Route::delete('cash-books/{cashBook}', [CashBookController::class, 'destroy']);
    Route::get('cash-books/{cashBook}/history', [CashBookController::class, 'history']);

    // Split Bills
    Route::get('split-bills', [SplitBillController::class, 'index']);
    Route::post('split-bills', [SplitBillController::class, 'store']);
    Route::get('split-bills/{splitBill}', [SplitBillController::class, 'show']);
    Route::put('split-bills/{splitBill}', [SplitBillController::class, 'update']);
    Route::delete('split-bills/{splitBill}', [SplitBillController::class, 'destroy']);

    // Bill Items
    Route::post('bill-items/{billItem}/pay', [BillItemController::class, 'pay']);
    Route::put('bill-items/{billItem}/verify', [BillItemController::class, 'verify']);

    // Recurring Bills
    Route::get('recurring-bills', [RecurringBillController::class, 'index']);
    Route::post('recurring-bills', [RecurringBillController::class, 'store']);
    Route::get('recurring-bills/{recurringBill}', [RecurringBillController::class, 'show']);
    Route::put('recurring-bills/{recurringBill}', [RecurringBillController::class, 'update']);
    Route::delete('recurring-bills/{recurringBill}', [RecurringBillController::class, 'destroy']);
    Route::post('recurring-bills/{recurringBill}/generate', [RecurringBillController::class, 'generate']);
    Route::get('recurring-bills/{recurringBill}/history', [RecurringBillController::class, 'history']);
    Route::post('recurring-bills/{recurringBill}/toggle-active', [RecurringBillController::class, 'toggleActive']);

    // Tasks
    Route::get('tasks', [TaskController::class, 'index']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/{task}', [TaskController::class, 'show']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::patch('tasks/reorder', [TaskController::class, 'reorder']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

    // Daily Logs
    Route::get('daily-logs', [DailyLogController::class, 'index']);
    Route::post('daily-logs', [DailyLogController::class, 'store']);
    Route::get('daily-logs/export', [DailyLogController::class, 'exportData']);
    Route::get('daily-logs/{dailyLog}', [DailyLogController::class, 'show']);
    Route::put('daily-logs/{dailyLog}', [DailyLogController::class, 'update']);
    Route::delete('daily-logs/{dailyLog}', [DailyLogController::class, 'destroy']);

    // Media
    Route::get('media', [MediaController::class, 'index']);
    Route::get('media/documents', [MediaController::class, 'documents']);
    Route::get('media/gallery', [MediaController::class, 'gallery']);
    Route::post('media', [MediaController::class, 'store']);
    Route::put('media/{media}', [MediaController::class, 'update']);
    Route::delete('media/{media}', [MediaController::class, 'destroy']);

    // Members & Roles
    Route::get('members', [MemberController::class, 'index']);
    Route::post('members', [MemberController::class, 'store']);
    Route::put('members/{user}', [MemberController::class, 'update']);
    Route::delete('members/{user}', [MemberController::class, 'destroy']);
    Route::put('members/{user}/role', [MemberController::class, 'updateRole']);
    Route::put('members/{user}/permissions', [MemberController::class, 'updatePermissions']);
    Route::put('roles/{role}/permissions', [MemberController::class, 'updateRolePermissions']);

    // Blogs
    Route::get('blogs/manage', [BlogController::class, 'manage']);
    Route::post('blogs', [BlogController::class, 'store']);
    Route::put('blogs/{blog}', [BlogController::class, 'update']);
    Route::delete('blogs/{blog}', [BlogController::class, 'destroy']);

    // Team Invitations
    Route::post('teams/{team}/invitations', [TeamInvitationController::class, 'send']);
    Route::get('teams/{team}/invitations', [TeamInvitationController::class, 'list']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Team member update (team leader can edit members)
    Route::put('teams/{team}/members/{user}/update', [TeamController::class, 'updateMember']);

    // Lead Tickets
    Route::get('lead/tickets', [TicketController::class, 'index']);
    Route::post('lead/tickets', [TicketController::class, 'store']);
    Route::get('lead/tickets/{ticket}', [TicketController::class, 'show']);

    // Contact Messages (admin only)
    Route::middleware([\App\Http\Middleware\CheckRole::class.':super_admin'])->group(function () {
        Route::get('contact', [ContactController::class, 'index']);
        Route::post('contact/{id}/read', [ContactController::class, 'markRead']);

        // Platform Settings (super_admin only)
        Route::get('admin/platform-settings', [AdminPlatformController::class, 'getSettings']);
        Route::put('admin/platform-settings', [AdminPlatformController::class, 'updateSettings']);
        Route::post('admin/platform-settings/test-email', [AdminPlatformController::class, 'testEmail']);
        Route::get('admin/system-status', [AdminPlatformController::class, 'getSystemStatus']);

        // Admin Tickets
        Route::get('admin/tickets', [AdminTicketController::class, 'index']);
        Route::get('admin/tickets/{ticket}', [AdminTicketController::class, 'show']);
        Route::put('admin/tickets/{ticket}/status', [AdminTicketController::class, 'updateStatus']);

        // Admin stats
        Route::get('admin/stats', function () {
            return response()->json([
                'data' => [
                    'teams_count' => \App\Models\Team::count(),
                    'users_count' => \App\Models\User::count(),
                    'blogs_count' => \App\Models\Blog::count(),
                    'recent_activity' => \Spatie\Activitylog\Models\Activity::latest()->take(10)->get(),
                ],
            ]);
        });

        // .env Config (super_admin only)
        Route::get('admin/env-config', [AdminEnvController::class, 'index']);
        Route::put('admin/env-config', [AdminEnvController::class, 'update']);
    });
});

// SSE Stream (outside auth middleware - uses query token)
Route::get('events/stream', [SseController::class, 'stream'])->middleware('throttle:300,1');
Route::options('events/stream', [SseController::class, 'options']);

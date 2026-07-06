<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillItem;
use App\Models\CashBook;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $user = $request->user();

        $cashIn = CashBook::where('team_id', $user->team_id)->where('type', 'in')->sum('amount');
        $cashOut = CashBook::where('team_id', $user->team_id)->where('type', 'out')->sum('amount');
        $currentBalance = $cashIn - $cashOut;
        $monthlyExpense = CashBook::where('team_id', $user->team_id)
            ->where('type', 'out')
            ->whereMonth('date', date('m'))
            ->whereYear('date', date('Y'))
            ->sum('amount');

        $unpaidBills = BillItem::with(['bill:id,title,due_date', 'user:id,name'])
            ->where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->get();

        $activeTasks = Task::with('assignee:id,name')
            ->where('team_id', $user->team_id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        $recentLogs = \App\Models\DailyLog::with('user:id,name,avatar_path')
            ->where('team_id', $user->team_id)
            ->orderBy('log_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $upcomingRecurringBills = \App\Models\RecurringBill::where('team_id', $user->team_id)
            ->where('is_active', true)
            ->where('next_date', '>=', date('Y-m-d'))
            ->orderBy('next_date', 'asc')
            ->take(3)
            ->get();

        $teamMembersCount = \App\Models\User::where('team_id', $user->team_id)->count();

        return response()->json([
            'finance' => [
                'balance' => (float) $currentBalance,
                'monthly_expense' => (float) $monthlyExpense,
                'total_in' => (float) $cashIn,
                'total_out' => (float) $cashOut,
            ],
            'unpaid_bills' => $unpaidBills,
            'active_tasks' => $activeTasks,
            'recent_logs' => $recentLogs,
            'upcoming_recurring_bills' => $upcomingRecurringBills,
            'team_members_count' => $teamMembersCount,
        ]);
    }

    public function member(Request $request)
    {
        $user = $request->user();

        $cashIn = CashBook::where('type', 'in')
            ->where('team_id', $user->team_id)
            ->sum('amount');
        $cashOut = CashBook::where('type', 'out')
            ->where('team_id', $user->team_id)
            ->sum('amount');

        $unpaidBills = BillItem::with('bill:id,title,due_date')
            ->where('user_id', $user->id)
            ->where('status', 'unpaid')
            ->get();

        $activeTasks = Task::with('assignee:id,name')
            ->where('assignee_id', $user->id)
            ->whereIn('status', ['todo', 'in_progress'])
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        return response()->json([
            'finance' => [
                'balance' => (float) ($cashIn - $cashOut),
                'monthly_expense' => (float) CashBook::where('type', 'out')
                    ->where('team_id', $user->team_id)
                    ->whereMonth('date', date('m'))
                    ->whereYear('date', date('Y'))
                    ->sum('amount'),
                'total_in' => (float) $cashIn,
                'total_out' => (float) $cashOut,
            ],
            'unpaid_bills' => $unpaidBills,
            'active_tasks' => $activeTasks,
        ]);
    }
}

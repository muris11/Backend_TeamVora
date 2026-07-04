<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RecurringBillResource;
use App\Models\RecurringBill;
use App\Services\RecurringBillService;
use Illuminate\Http\Request;

class RecurringBillController extends Controller
{
    public function __construct(protected RecurringBillService $service) {}

    public function index(Request $request)
    {
        $query = RecurringBill::withCount('generations');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active' ? 'active' : '!=', 'active');
        }
        if ($request->filled('frequency')) {
            $query->where('frequency', $request->frequency);
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(15);
        return RecurringBillResource::collection($bills);
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom_days',
            'interval_days' => 'nullable|integer|min:1',
            'due_day' => 'nullable|integer|between:1,28',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'notify_days_before_due' => 'nullable|integer|min:0|max:30',
        ]);

        $validated['creator_id'] = $request->user()->id;
        $validated['team_id'] = $request->user()->team_id;
        $validated['status'] = 'active';
        $validated['next_generation_at'] = $this->service->computeDueDate(RecurringBill::make($validated));

        $bill = RecurringBill::create($validated);

        return new RecurringBillResource($bill->load('creator'));
    }

    public function show(RecurringBill $recurringBill)
    {
        $recurringBill->loadCount('generations')->load('creator:id,name');

        $generations = $recurringBill->generations()
            ->with(['splitBill.items.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return (new RecurringBillResource($recurringBill))->additional([
            'generations' => $generations,
        ]);
    }

    public function update(Request $request, RecurringBill $recurringBill)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom_days',
            'interval_days' => 'nullable|integer|min:1',
            'due_day' => 'nullable|integer|between:1,28',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'notify_days_before_due' => 'nullable|integer|min:0|max:30',
        ]);

        $recurringBill->update($validated);

        return new RecurringBillResource($recurringBill->fresh());
    }

    public function destroy(Request $request, RecurringBill $recurringBill)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $recurringBill->update(['status' => 'ended']);
        return response()->json(['message' => 'Tagihan berulang dinonaktifkan.']);
    }

    public function generate(Request $request, RecurringBill $recurringBill)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($recurringBill->status !== 'active') {
            return response()->json(['message' => 'Tagihan tidak aktif.'], 422);
        }

        $result = $this->service->generateCycle($recurringBill);

        if (! $result) {
            return response()->json(['message' => 'Tidak ada tagihan untuk siklus ini.']);
        }

        return response()->json(['message' => 'Tagihan berhasil dibuat.']);
    }

    public function history(RecurringBill $recurringBill)
    {
        $generations = $recurringBill->generations()
            ->with(['splitBill.items.user:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'bill' => $recurringBill->only(['id', 'title', 'amount', 'frequency']),
            'generations' => $generations,
        ]);
    }

    public function toggleActive(Request $request, RecurringBill $recurringBill)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $newStatus = $recurringBill->status === 'active' ? 'paused' : 'active';
        $recurringBill->update(['status' => $newStatus]);

        return new RecurringBillResource($recurringBill->fresh());
    }
}

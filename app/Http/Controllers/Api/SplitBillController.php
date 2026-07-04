<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SplitBillResource;
use App\Models\SplitBill;
use App\Models\User;
use App\Notifications\BillCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SplitBillController extends Controller
{
    public function index(Request $request)
    {
        $query = SplitBill::with(['creator:id,name', 'items.user:id,name']);

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        if (! $request->user()->hasPermissionTo('write_split_bill')) {
            $query->whereHas('items', fn ($q) => $q->where('user_id', $request->user()->id));
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(10);

        return SplitBillResource::collection($bills)->additional([
            'users' => User::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasPermissionTo('write_split_bill')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'total_amount' => 'required|numeric|min:1',
            'due_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.user_id' => 'required|exists:users,id',
            'items.*.amount' => 'required|numeric|min:1',
        ]);

        $sum = collect($validated['items'])->sum('amount');
        if (abs($sum - $validated['total_amount']) > 0.01) {
            return response()->json(['message' => 'Total item tidak sama dengan Total Amount.'], 422);
        }

        DB::beginTransaction();
        try {
            $bill = SplitBill::create([
                'creator_id' => $request->user()->id,
                'team_id' => $request->user()->team_id,
                'title' => $validated['title'],
                'total_amount' => $validated['total_amount'],
                'due_date' => $validated['due_date'],
                'status' => 'active',
            ]);

            foreach ($validated['items'] as $item) {
                $billItem = $bill->items()->create([
                    'user_id' => $item['user_id'],
                    'amount' => $item['amount'],
                    'status' => 'unpaid',
                ]);
                $billItem->user->notify(new BillCreatedNotification($bill));
            }

            DB::commit();

            return new SplitBillResource($bill->load(['creator:id,name', 'items.user:id,name']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat tagihan: ' . $e->getMessage()], 500);
        }
    }

    public function show(Request $request, SplitBill $splitBill)
    {
        $user = $request->user();
        if (! $user->isSuperAdmin() && $splitBill->team_id !== $user->team_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        $splitBill->load(['creator:id,name', 'items.user:id,name', 'items.verifier:id,name']);
        return new SplitBillResource($splitBill);
    }

    public function update(Request $request, SplitBill $splitBill)
    {
        if (! $request->user()->hasPermissionTo('write_split_bill')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'total_amount' => 'sometimes|numeric|min:1',
            'due_date' => 'sometimes|date|after_or_equal:today',
            'description' => 'nullable|string',
        ]);

        $splitBill->update($validated);
        return new SplitBillResource($splitBill->fresh()->load(['creator:id,name', 'items.user:id,name']));
    }

    public function destroy(Request $request, SplitBill $splitBill)
    {
        if (! $request->user()->hasPermissionTo('write_split_bill')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $splitBill->items()->delete();
        $splitBill->delete();
        return response()->json(['message' => 'Tagihan berhasil dihapus.']);
    }
}

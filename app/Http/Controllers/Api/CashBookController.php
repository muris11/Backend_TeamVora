<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CashBookResource;
use App\Models\CashBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CashBookController extends Controller
{
    public function index(Request $request)
    {
        $query = CashBook::with('createdBy:id,name');

        if (! $request->user()->isSuperAdmin()) {
            $query->where('team_id', $request->user()->team_id);
        }

        $cashBooks = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $totalQuery = CashBook::where('type', 'in');
        $totalOutQuery = CashBook::where('type', 'out');
        if (! $request->user()->isSuperAdmin()) {
            $totalQuery->where('team_id', $request->user()->team_id);
            $totalOutQuery->where('team_id', $request->user()->team_id);
        }
        $totalIn = $totalQuery->sum('amount');
        $totalOut = $totalOutQuery->sum('amount');

        return CashBookResource::collection($cashBooks)->additional([
            'summary' => [
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'balance' => (float) ($totalIn - $totalOut),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:1',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $path = $file->storeAs(
                    'kas/' . date('Y/m'),
                    time() . '_' . $file->getClientOriginalName(),
                    's3'
                );
            }

            $cashBook = CashBook::create([
                'created_by' => $request->user()->id,
                'team_id' => $request->user()->team_id,
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'title' => $validated['category'],
                'description' => $validated['description'] ?? '',
                'date' => $validated['transaction_date'],
                'attachment_path' => $path ? Storage::disk('s3')->url($path) : null,
            ]);

            DB::commit();

            return new CashBookResource($cashBook->load('createdBy'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    public function show(CashBook $cashBook)
    {
        return new CashBookResource($cashBook->load('createdBy'));
    }

    public function update(Request $request, CashBook $cashBook)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:in,out',
            'amount' => 'sometimes|numeric|min:1',
            'category' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'transaction_date' => 'sometimes|date',
        ]);

        $cashBook->update([
            'type' => $validated['type'] ?? $cashBook->type,
            'amount' => $validated['amount'] ?? $cashBook->amount,
            'title' => $validated['category'] ?? $cashBook->title,
            'description' => $validated['description'] ?? $cashBook->description,
            'date' => $validated['transaction_date'] ?? $cashBook->date,
        ]);

        return new CashBookResource($cashBook->fresh()->load('createdBy'));
    }

    public function destroy(Request $request, CashBook $cashBook)
    {
        if (! $request->user()->hasAnyRole(['super_admin', 'team_leader'])) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $cashBook->delete();
        return response()->json(['message' => 'Catatan kas berhasil dihapus.']);
    }

    public function history(CashBook $cashBook)
    {
        $activities = $cashBook->activities()->with('causer:id,name')->latest()->get();
        return response()->json($activities);
    }
}

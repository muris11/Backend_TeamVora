<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillItemResource;
use App\Models\BillItem;
use App\Models\CashBook;
use App\Notifications\BillVerifiedNotification;
use App\Notifications\ProofUploadedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BillItemController extends Controller
{
    public function pay(Request $request, BillItem $billItem)
    {
        if ($request->user()->id !== $billItem->user_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $file = $request->file('proof_file');
            $path = $file->storeAs(
                'bills/' . date('Y/m'),
                time() . '_' . $file->getClientOriginalName(),
                'public'
            );

            $billItem->update([
                'status' => 'pending_verification',
                'proof_path' => Storage::disk('public')->url($path),
            ]);

            $billItem->splitBill->creator->notify(new ProofUploadedNotification($billItem, $request->user()));

            return new BillItemResource($billItem->fresh());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengunggah: ' . $e->getMessage()], 500);
        }
    }

    public function verify(Request $request, BillItem $billItem)
    {
        if (! $request->user()->hasPermissionTo('verify_split_bill')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid',
        ]);

        DB::beginTransaction();
        try {
            $billItem->update([
                'status' => $validated['status'],
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
            ]);

            $billItem->user->notify(new BillVerifiedNotification($billItem, $validated['status']));

            if ($validated['status'] === 'paid') {
                CashBook::create([
                    'created_by' => $request->user()->id,
                    'type' => 'in',
                    'amount' => $billItem->amount,
                    'title' => 'Pembayaran Tagihan: ' . $billItem->splitBill->title,
                    'description' => 'Pembayaran dari ' . $billItem->user->name,
                    'date' => now()->toDateString(),
                    'attachment_path' => $billItem->proof_path,
                ]);
            }

            $allPaid = $billItem->splitBill->items()->where('status', '!=', 'paid')->doesntExist();
            if ($allPaid) {
                $billItem->splitBill->update(['status' => 'completed']);
            }

            DB::commit();

            return new BillItemResource($billItem->fresh());
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal verifikasi: ' . $e->getMessage()], 500);
        }
    }
}

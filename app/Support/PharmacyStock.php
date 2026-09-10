<?php

namespace App\Support;

use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class PharmacyStock
{
    /**
     * Receive a purchased line into stock: creates (or tops up) the batch and
     * writes a +ve ledger movement.
     */
    public function receive(PurchaseItem $item, int $branchId): MedicineBatch
    {
        $qty = $item->quantity + $item->free_quantity;

        $batch = MedicineBatch::withoutTenantScope()->firstOrNew([
            'branch_id' => $branchId,
            'medicine_id' => $item->medicine_id,
            'batch_number' => $item->batch_number,
        ]);

        $batch->fill([
            'hospital_id' => $item->hospital_id,
            'purchase_item_id' => $item->id,
            'expiry_date' => $item->expiry_date,
            'mrp' => $item->mrp,
            'purchase_price' => $item->purchase_price,
            'sale_price' => $item->sale_price,
            'quantity_received' => ($batch->quantity_received ?? 0) + $qty,
            'quantity_available' => ($batch->quantity_available ?? 0) + $qty,
        ])->save();

        $this->log($batch, 'purchase', $qty, $item->purchase, 'GRN '.$item->purchase->purchase_no);

        return $batch;
    }

    /**
     * Allocate `quantity` units of a medicine from the branch's batches, FEFO.
     * Returns [['batch' => MedicineBatch, 'quantity' => int], ...].
     *
     * @throws ValidationException when stock is insufficient
     */
    public function allocate(Medicine $medicine, int $branchId, int $quantity): Collection
    {
        $batches = MedicineBatch::withoutTenantScope()
            ->where('branch_id', $branchId)
            ->where('medicine_id', $medicine->id)
            ->dispensable()
            ->lockForUpdate()
            ->get();

        $remaining = $quantity;
        $plan = collect();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }
            $take = min($remaining, $batch->quantity_available);
            $plan->push(['batch' => $batch, 'quantity' => $take]);
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => "Not enough stock for {$medicine->display_name} (short by {$remaining}).",
            ]);
        }

        return $plan;
    }

    /** Apply an allocation plan: decrement batches and write -ve movements. */
    public function dispense(Collection $plan, Model $reference, ?string $note = null): void
    {
        foreach ($plan as $row) {
            /** @var MedicineBatch $batch */
            $batch = $row['batch'];
            $batch->decrement('quantity_available', $row['quantity']);
            $this->log($batch->fresh(), 'sale', -$row['quantity'], $reference, $note);
        }
    }

    public function adjust(MedicineBatch $batch, int $delta, string $type, ?string $note = null): void
    {
        $new = $batch->quantity_available + $delta;
        if ($new < 0) {
            throw ValidationException::withMessages(['quantity' => 'Adjustment would make stock negative.']);
        }

        $batch->update(['quantity_available' => $new]);
        $this->log($batch, $type, $delta, null, $note);
    }

    protected function log(MedicineBatch $batch, string $type, int $qty, ?Model $reference, ?string $note): void
    {
        StockMovement::create([
            'hospital_id' => $batch->hospital_id,
            'branch_id' => $batch->branch_id,
            'medicine_id' => $batch->medicine_id,
            'medicine_batch_id' => $batch->id,
            'type' => $type,
            'quantity' => $qty,
            'balance_after' => $batch->quantity_available,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'note' => $note,
        ]);
    }
}

<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'batch_id'         => $this->batch_id,
            'payment_month'    => $this->payment_month?->format('Y-m'),
            'amount'           => (float) $this->amount,
            'status'           => $this->status?->value,
            'status_label'     => $this->status?->label(),
            'rejection_reason' => $this->rejection_reason,
            'notes'            => $this->notes,
            'proof_url'        => $this->proof_path
                ? route('api.payments.proof', $this->id)
                : null,
            'approved_at'      => $this->approved_at?->toISOString(),
            'created_at'       => $this->created_at->toISOString(),
            'updated_at'       => $this->updated_at->toISOString(),
            'resident'         => $this->whenLoaded('resident', fn() => $this->resident ? [
                'id'       => $this->resident->id,
                'fullname' => $this->resident->fullname,
                'block'    => $this->resident->block ? [
                    'id'   => $this->resident->block->id,
                    'name' => $this->resident->block->name,
                ] : null,
                'unit'     => $this->resident->unit ? [
                    'id'          => $this->resident->unit->id,
                    'unit_number' => $this->resident->unit->unit_number,
                ] : null,
            ] : null),
            'payment_method'   => $this->whenLoaded('paymentMethod', fn() => $this->paymentMethod ? [
                'id'    => $this->paymentMethod->id,
                'name'  => $this->paymentMethod->name,
                'label' => $this->paymentMethod->label,
            ] : null),
            'submitted_by'     => $this->whenLoaded('submittedBy', fn() => $this->submittedBy ? [
                'id'   => $this->submittedBy->id,
                'name' => $this->submittedBy->name,
            ] : null),
            'approved_by'      => $this->whenLoaded('approvedBy', fn() => $this->approvedBy ? [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ] : null),
        ];
    }
}

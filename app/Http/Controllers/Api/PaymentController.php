<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ReviewPaymentRequest;
use App\Http\Requests\API\UploadPaymentProofRequest;
use App\Http\Resources\API\PaymentResource;
use App\Models\PaymentRecord;
use App\Enums\PaymentStatus;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        if (!$request->user()->can('payments.view')) {
            return $this->forbidden();
        }

        $query = PaymentRecord::with(['resident.block', 'resident.unit', 'paymentMethod', 'submittedBy', 'approvedBy']);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by resident
        if ($residentId = $request->input('resident_id')) {
            $query->where('resident_id', $residentId);
        }

        // Filter by month (e.g. "2025-03")
        if ($month = $request->input('month')) {
            $query->whereYear('payment_month', substr($month, 0, 4))
                  ->whereMonth('payment_month', substr($month, 5, 2));
        }

        // Residents can only see their own payments
        $user = $request->user();
        if ($user->isResident()) {
            $resident = $user->resolveResident();
            if ($resident) {
                $query->where('resident_id', $resident->id);
            } else {
                return $this->success([], 'No payment records found');
            }
        }

        $query->orderByDesc('payment_month')->orderByDesc('created_at');

        $paginator = $query->paginate($request->input('per_page', 15));

        return $this->paginated($paginator, PaymentResource::collection($paginator), 'Payments fetched successfully');
    }

    public function show(Request $request, PaymentRecord $payment): JsonResponse
    {
        if (!$request->user()->can('payments.view')) {
            return $this->forbidden();
        }

        // Prevent IDOR: residents can only see their own payment
        if ($request->user()->isResident()) {
            $resident = $request->user()->resolveResident();
            if (!$resident || $payment->resident_id !== $resident->id) {
                return $this->forbidden();
            }
        }

        $payment->load(['resident.block', 'resident.unit', 'paymentMethod', 'submittedBy', 'approvedBy']);

        return $this->success(new PaymentResource($payment), 'Payment fetched successfully');
    }

    public function uploadProof(UploadPaymentProofRequest $request): JsonResponse
    {
        $data = $request->validated();

        $file     = $request->file('proof');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs('payments/proofs', $filename, 'local');

        $payment = PaymentRecord::create([
            'resident_id'       => $data['resident_id'],
            'payment_month'     => $data['payment_month'] . '-01',
            'amount'            => $data['amount'],
            'payment_method_id' => $data['payment_method_id'],
            'proof_path'        => $path,
            'notes'             => $data['notes'] ?? null,
            'status'            => PaymentStatus::Pending,
            'submitted_by'      => $request->user()->id,
        ]);

        $payment->load(['resident.block', 'resident.unit', 'paymentMethod', 'submittedBy']);

        return $this->created(new PaymentResource($payment), 'Payment proof uploaded successfully');
    }

    public function review(ReviewPaymentRequest $request, PaymentRecord $payment): JsonResponse
    {
        if ($payment->status !== PaymentStatus::Pending) {
            return $this->error('Only pending payments can be reviewed.', 422);
        }

        $action = $request->input('action');

        if ($action === 'approve') {
            $payment->update([
                'status'      => PaymentStatus::Approved,
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);
            $message = 'Payment approved successfully';
        } else {
            $payment->update([
                'status'           => PaymentStatus::Rejected,
                'rejection_reason' => $request->input('rejection_reason'),
                'approved_by'      => $request->user()->id,
                'approved_at'      => now(),
            ]);
            $message = 'Payment rejected successfully';
        }

        $payment->load(['resident.block', 'resident.unit', 'paymentMethod', 'submittedBy', 'approvedBy']);

        return $this->success(new PaymentResource($payment), $message);
    }

    /**
     * Serve payment proof file (authenticated).
     */
    public function proof(Request $request, PaymentRecord $payment): mixed
    {
        if (!$request->user()->can('payments.view')) {
            return $this->forbidden();
        }

        if ($request->user()->isResident()) {
            $resident = $request->user()->resolveResident();
            if (!$resident || $payment->resident_id !== $resident->id) {
                return $this->forbidden();
            }
        }

        if (!$payment->proof_path || !Storage::disk('local')->exists($payment->proof_path)) {
            return $this->notFound('Proof file not found');
        }

        return Storage::disk('local')->response($payment->proof_path);
    }
}

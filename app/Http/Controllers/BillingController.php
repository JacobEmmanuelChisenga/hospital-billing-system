<?php

namespace App\Http\Controllers;

use App\Enums\BillStatus;
use App\Enums\PatientStatus;
use App\Http\Requests\CollectCashPaymentRequest;
use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\VoidBillRequest;
use App\Models\Bill;
use App\Models\Patient;
use App\Services\BillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private BillService $billService,
    ) {}

    /**
     * Search patients and list today's posted bills.
     */
    public function index(Request $request): View
    {
        $todaysBills = Bill::query()
            ->with(['patient', 'createdBy'])
            ->posted()
            ->whereDate('visit_date', today())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $outstandingCashBills = Bill::query()
            ->with(['patient', 'createdBy', 'visit'])
            ->outstandingCash()
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('billing.index', [
            'todaysBills' => $todaysBills,
            'outstandingCashBills' => $outstandingCashBills,
        ]);
    }

    /**
     * Bill form for a selected patient — shows balance and charge fields.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->filled('patient_id')) {
            return redirect()
                ->route('billing.index')
                ->with('error', 'Please search for and select a patient to bill.');
        }

        $patient = Patient::query()
            ->with(['company', 'principalMember'])
            ->where('status', PatientStatus::Active)
            ->findOrFail($request->integer('patient_id'));

        return view('billing.create', [
            'patient' => $patient,
            'lowBalanceThreshold' => config('hospital.low_balance_threshold'),
            'availableBalance' => (float) $patient->effectiveBalance(),
        ]);
    }

    /**
     * Post the bill, deduct the payer balance, and redirect to the receipt.
     */
    public function store(StoreBillRequest $request): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($request->input('patient_id'));

        $bill = $this->billService->post(
            $patient,
            array_merge(
                $request->safe()->only([
                    'visit_date',
                    'visit_type',
                    'ward_bed',
                    'notes',
                ]),
                $request->chargeInputs(),
            ),
            $request->user(),
        );

        return redirect()
            ->route('billing.receipt', $bill)
            ->with('success', 'Bill posted successfully. Balance has been deducted.');
    }

    /**
     * Bill details with receipt actions and void form.
     */
    public function show(Bill $bill): View
    {
        $bill->load(['patient', 'accountPatient', 'company', 'createdBy', 'voidedBy', 'paidBy', 'visit']);

        return view('billing.show', [
            'bill' => $bill,
            'paymentMethods' => \App\Enums\PaymentMethod::cases(),
        ]);
    }

    /**
     * Print-friendly receipt (opens in browser print dialog).
     */
    public function receipt(Bill $bill): View
    {
        $bill->load(['patient', 'accountPatient', 'company', 'createdBy', 'paidBy']);

        return view('billing.receipt', [
            'bill' => $bill,
        ]);
    }

    /**
     * Record immediate payment for a casual caller bill.
     */
    public function collectPayment(CollectCashPaymentRequest $request, Bill $bill): RedirectResponse
    {
        try {
            $bill = $this->billService->collectCashPayment(
                $bill,
                \App\Enums\PaymentMethod::from($request->input('payment_method')),
                $request->user(),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('billing.receipt', $bill)
            ->with('success', 'Payment recorded. Receipt is ready to print.');
    }

    /**
     * Void a posted bill and restore the payer balance.
     */
    public function void(VoidBillRequest $request, Bill $bill): RedirectResponse
    {
        if ($bill->status !== BillStatus::Posted) {
            return back()->with('error', 'Only posted bills can be voided.');
        }

        $this->billService->void(
            $bill,
            $request->input('void_reason'),
            $request->user(),
        );

        return redirect()
            ->route('billing.show', $bill)
            ->with('success', $bill->isCashBill()
                ? 'Bill voided.'
                : 'Bill voided. Payer balance has been restored.');
    }
}

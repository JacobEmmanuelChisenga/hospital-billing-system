<?php

namespace App\Http\Controllers;

use App\Enums\PatientStatus;
use App\Enums\PatientType;
use App\Enums\PaymentMethod;
use App\Http\Requests\ReverseDepositRequest;
use App\Http\Requests\StoreDepositRequest;
use App\Models\Deposit;
use App\Models\Patient;
use App\Services\DepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepositController extends Controller
{
    public function __construct(
        private DepositService $depositService,
    ) {}

    /**
     * List member deposits with search and date filters.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $fromDate = $request->string('from_date')->toString();
        $toDate = $request->string('to_date')->toString();

        $deposits = Deposit::query()
            ->with(['patient', 'createdBy'])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('patient', fn ($q) => $q->search($search));
            })
            ->when($status === 'active', fn ($q) => $q->active())
            ->when($status === 'reversed', fn ($q) => $q->whereNotNull('reversed_at'))
            ->when($fromDate !== '', fn ($q) => $q->whereDate('deposit_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($q) => $q->whereDate('deposit_date', '<=', $toDate))
            ->orderByDesc('deposit_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('deposits.index', compact('deposits', 'search', 'status', 'fromDate', 'toDate'));
    }

    /**
     * Form to load a deposit into a member account.
     */
    public function create(Request $request): View
    {
        $selectedPatientId = $request->filled('patient_id') ? $request->integer('patient_id') : null;

        $members = Patient::query()
            ->where('type', PatientType::Member)
            ->where('status', PatientStatus::Active)
            ->orderBy('name')
            ->get();

        return view('deposits.create', [
            'members' => $members,
            'selectedPatientId' => $selectedPatientId,
            'largeDepositThreshold' => config('hospital.large_deposit_threshold'),
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }

    /**
     * Save a member deposit and increase the member balance.
     */
    public function store(StoreDepositRequest $request): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($request->input('patient_id'));

        $deposit = $this->depositService->record(
            $patient,
            $request->safe()->only(['amount', 'payment_method', 'deposit_date', 'reference', 'notes']),
            $request->user(),
        );

        return redirect()
            ->route('deposits.show', $deposit)
            ->with('success', 'Deposit loaded successfully. Member balance updated.');
    }

    /**
     * Deposit receipt and reversal option.
     */
    public function show(Deposit $deposit): View
    {
        $deposit->load(['patient', 'createdBy', 'reversedBy']);

        return view('deposits.show', [
            'deposit' => $deposit,
        ]);
    }

    /**
     * Printable deposit receipt.
     */
    public function receipt(Deposit $deposit): View
    {
        $deposit->load(['patient', 'createdBy']);

        return view('deposits.receipt', [
            'deposit' => $deposit,
        ]);
    }

    /**
     * Reverse a deposit and deduct the amount from the member balance.
     */
    public function reverse(ReverseDepositRequest $request, Deposit $deposit): RedirectResponse
    {
        $this->depositService->reverse(
            $deposit,
            $request->input('reversal_reason'),
            $request->user(),
        );

        return redirect()
            ->route('deposits.show', $deposit)
            ->with('success', 'Deposit reversed. Member balance has been adjusted.');
    }
}

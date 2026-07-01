<?php

namespace App\Http\Controllers;

use App\Enums\PatientStatus;
use App\Enums\PatientType;
use App\Enums\PaymentMethod;
use App\Http\Requests\StoreMembershipFeeRequest;
use App\Models\MembershipFee;
use App\Models\Patient;
use App\Services\MembershipFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MembershipFeeController extends Controller
{
    public function __construct(
        private MembershipFeeService $membershipFeeService,
    ) {}

    /**
     * List membership payments with search and expiry filters.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();
        $fromDate = $request->string('from_date')->toString();
        $toDate = $request->string('to_date')->toString();

        $fees = MembershipFee::query()
            ->with(['patient', 'principalPatient', 'createdBy'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('patient', fn ($p) => $p->search($search))
                        ->orWhereHas('principalPatient', fn ($p) => $p->search($search));
                });
            })
            ->when($status === 'active', fn ($q) => $q->whereDate('expiry_date', '>=', today()))
            ->when($status === 'expired', fn ($q) => $q->whereDate('expiry_date', '<', today()))
            ->when($status === 'expiring', fn ($q) => $q
                ->whereDate('expiry_date', '>=', today())
                ->whereDate('expiry_date', '<=', today()->addDays(30)))
            ->when($fromDate !== '', fn ($q) => $q->whereDate('payment_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($q) => $q->whereDate('payment_date', '<=', $toDate))
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('membership-fees.index', compact('fees', 'search', 'status', 'fromDate', 'toDate'));
    }

    /**
     * Form to record a membership payment for a member or dependant.
     */
    public function create(Request $request): View
    {
        $selectedPatientId = $request->filled('patient_id')
            ? $request->integer('patient_id')
            : null;

        $patients = Patient::query()
            ->whereIn('type', [PatientType::Member, PatientType::Dependant])
            ->where('status', PatientStatus::Active)
            ->with(['principalMember.membership', 'membership'])
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('membership-fees.create', [
            'patients' => $patients,
            'selectedPatientId' => $selectedPatientId,
            'paymentMethods' => PaymentMethod::options(),
        ]);
    }

    /**
     * Save a membership payment and activate / renew membership.
     */
    public function store(StoreMembershipFeeRequest $request): RedirectResponse
    {
        $holder = Patient::query()->findOrFail($request->integer('patient_id'));

        $fee = $this->membershipFeeService->record(
            $holder,
            $request->safe()->only(['amount', 'payment_method', 'reference', 'payment_date', 'expiry_date', 'notes']),
            $request->user(),
        );

        return redirect()
            ->route('membership-fees.show', $fee)
            ->with('success', 'Membership payment recorded. Membership has been activated.');
    }

    /**
     * Membership payment detail.
     */
    public function show(MembershipFee $membershipFee): View
    {
        $membershipFee->load(['patient.membership', 'principalPatient.membership', 'createdBy']);

        return view('membership-fees.show', [
            'fee' => $membershipFee,
        ]);
    }

    /**
     * Printable membership payment receipt.
     */
    public function receipt(MembershipFee $membershipFee): View
    {
        $membershipFee->load(['patient.membership', 'principalPatient.membership', 'createdBy']);

        return view('membership-fees.receipt', [
            'fee' => $membershipFee,
        ]);
    }
}

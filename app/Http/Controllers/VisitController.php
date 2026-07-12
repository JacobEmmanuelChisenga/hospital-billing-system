<?php

namespace App\Http\Controllers;

use App\Enums\PatientStatus;
use App\Enums\VisitStatus;
use App\Http\Requests\PostVisitBillRequest;
use App\Http\Requests\StoreChargeLineRequest;
use App\Http\Requests\StoreVisitRequest;
use App\Models\BillableService;
use App\Models\ChargeLine;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function __construct(
        private VisitService $visitService,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->toString();

        $visits = Visit::query()
            ->with(['patient', 'openedBy', 'bill'])
            ->when($search !== '', fn ($q) => $q->whereHas('patient', fn ($p) => $p->search($search)))
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('visits.index', [
            'visits' => $visits,
            'search' => $search,
            'status' => $status,
            'visitStatuses' => VisitStatus::cases(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->filled('patient_id')) {
            return view('visits.create-select', [
                'patients' => Patient::query()
                    ->where('status', PatientStatus::Active)
                    ->orderBy('name')
                    ->get(),
            ]);
        }

        $patient = Patient::query()
            ->with(['company', 'principalMember'])
            ->findOrFail($request->integer('patient_id'));

        if ($patient->openVisit()) {
            return redirect()
                ->route('visits.show', $patient->openVisit())
                ->with('info', 'This patient already has an open visit.');
        }

        return view('visits.create', compact('patient'));
    }

    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $patient = Patient::query()->findOrFail($request->integer('patient_id'));

        $visit = $this->visitService->open(
            $patient,
            $request->validated(),
            $request->user(),
        );

        return redirect()
            ->route('visits.show', $visit)
            ->with('success', 'Visit opened. Patient is waiting for the consultant.');
    }

    public function show(Visit $visit): View
    {
        $visit->load(['patient.company', 'patient.membership', 'patient.principalMember.membership', 'chargeLines.billableService', 'chargeLines.recordedBy', 'clinicalNote.recordedBy', 'bill', 'openedBy']);

        // Dependant visits can be stuck on Awaiting Payment even when the
        // principal already has active membership and balance — release them.
        $visit = $this->visitService->releaseIfPaymentSatisfied($visit);

        return view('visits.show', [
            'visit' => $visit,
            'billableServices' => BillableService::query()
                ->active()
                ->orderBy('category')
                ->orderBy('name')
                ->get(),
            'lowBalanceThreshold' => config('hospital.low_balance_threshold'),
            'availableBalance' => (float) $visit->patient->effectiveBalance(),
        ]);
    }

    public function storeCharge(StoreChargeLineRequest $request, Visit $visit): RedirectResponse
    {
        $this->visitService->addCharge($visit, $request->validated(), $request->user());

        return back()->with('success', 'Charge added.');
    }

    public function destroyCharge(Visit $visit, ChargeLine $chargeLine): RedirectResponse
    {
        if ($chargeLine->visit_id !== $visit->id) {
            abort(404);
        }

        $this->visitService->removeCharge($chargeLine);

        return back()->with('success', 'Charge removed.');
    }

    public function postBill(PostVisitBillRequest $request, Visit $visit): RedirectResponse
    {
        try {
            $visit = $this->visitService->postBill(
                $visit,
                $request->user(),
                $request->boolean('confirm_insufficient_balance'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $visit->loadMissing('patient', 'bill');

        if ($visit->patient->isCashPatient()) {
            return redirect()
                ->route('billing.show', $visit->bill)
                ->with('success', 'Charges posted. Send the patient to Accounts for payment.');
        }

        return redirect()
            ->route('billing.receipt', $visit->bill)
            ->with('success', 'Bill posted and visit completed. Balance deducted.');
    }

    public function cancel(Visit $visit): RedirectResponse
    {
        $this->visitService->cancel($visit, request()->user());

        return redirect()
            ->route('visits.index')
            ->with('success', 'Visit cancelled.');
    }
}

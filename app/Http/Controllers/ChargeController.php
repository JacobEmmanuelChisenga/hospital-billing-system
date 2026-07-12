<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Registry Clerk charge workflow — filtered visit lists for posting services.
 */
class ChargeController extends Controller
{
    public function pending(Request $request): View
    {
        return $this->renderList(
            $request,
            title: 'Pending Charges',
            description: 'Patients seen by the consultant who are waiting for services to be charged.',
            status: VisitStatus::AwaitingBilling,
            requireCharges: false,
        );
    }

    public function post(Request $request): View
    {
        return $this->renderList(
            $request,
            title: 'Post Charges',
            description: 'Select services and post charges to complete each visit.',
            status: VisitStatus::AwaitingBilling,
            requireCharges: null,
        );
    }

    public function history(Request $request): View
    {
        return $this->renderList(
            $request,
            title: 'Charge History',
            description: 'Completed visits with posted charges.',
            status: VisitStatus::Completed,
            requireCharges: null,
        );
    }

    private function renderList(
        Request $request,
        string $title,
        string $description,
        VisitStatus $status,
        ?bool $requireCharges,
    ): View {
        $search = $request->string('search')->trim()->toString();

        $visits = Visit::query()
            ->with(['patient', 'openedBy', 'bill', 'chargeLines', 'clinicalNote'])
            ->when($search !== '', fn ($query) => $query->whereHas('patient', fn ($patient) => $patient->search($search)))
            ->where('status', $status)
            ->when($requireCharges === false, fn ($query) => $query->whereDoesntHave('chargeLines'))
            ->when($requireCharges === true, fn ($query) => $query->whereHas('chargeLines'))
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('charges.index', [
            'visits' => $visits,
            'search' => $search,
            'pageTitle' => $title,
            'pageDescription' => $description,
        ]);
    }
}

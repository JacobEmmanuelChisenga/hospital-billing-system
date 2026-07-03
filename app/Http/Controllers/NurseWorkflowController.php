<?php

namespace App\Http\Controllers;

use App\Enums\VisitStatus;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Nurse workflow — consultation queue, active visits, and history.
 */
class NurseWorkflowController extends Controller
{
    public function queue(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $visits = Visit::query()
            ->with(['patient.membership', 'patient.principalMember'])
            ->whereDate('visit_date', today())
            ->where('status', VisitStatus::ReadyForConsultation)
            ->when($search !== '', fn ($query) => $query->whereHas('patient', fn ($patient) => $patient->search($search)))
            ->orderBy('id')
            ->get();

        return view('nurse.queue', [
            'visits' => $visits,
            'search' => $search,
        ]);
    }

    public function active(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $visits = Visit::query()
            ->with(['patient.membership', 'patient.principalMember', 'clinicalNote'])
            ->whereDate('visit_date', today())
            ->where('status', VisitStatus::ReadyForConsultation)
            ->when($search !== '', fn ($query) => $query->whereHas('patient', fn ($patient) => $patient->search($search)))
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('nurse.active', [
            'visits' => $visits,
            'search' => $search,
        ]);
    }

    public function consultations(Request $request): View
    {
        $period = $request->string('period', 'today')->toString();
        $search = $request->string('search')->trim()->toString();

        $visits = Visit::query()
            ->with(['patient', 'clinicalNote.recordedBy'])
            ->whereHas('clinicalNote')
            ->when($search !== '', fn ($query) => $query->whereHas('patient', fn ($patient) => $patient->search($search)))
            ->when($period === 'today', fn ($query) => $query->whereDate('visit_date', today()))
            ->when($period === 'yesterday', fn ($query) => $query->whereDate('visit_date', today()->subDay()))
            ->when($period === 'week', fn ($query) => $query->whereDate('visit_date', '>=', today()->subDays(6)))
            ->orderByDesc('visit_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('nurse.consultations', [
            'visits' => $visits,
            'search' => $search,
            'period' => $period,
        ]);
    }
}

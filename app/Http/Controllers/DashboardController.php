<?php

namespace App\Http\Controllers;

use App\Enums\PatientStatus;
use App\Models\Bill;
use App\Models\Deposit;
use App\Models\Patient;
use Illuminate\View\View;

/**
 * Main landing page after login. Module summaries will be added in later phases.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'activePatientCount' => Patient::query()
                ->where('status', PatientStatus::Active)
                ->count(),
            'todaysDepositsTotal' => Deposit::query()
                ->active()
                ->whereDate('deposit_date', today())
                ->sum('amount'),
            'todaysDepositsCount' => Deposit::query()
                ->active()
                ->whereDate('deposit_date', today())
                ->count(),
            'todaysBillsTotal' => Bill::query()
                ->posted()
                ->whereDate('visit_date', today())
                ->sum('total_amount'),
            'todaysBillsCount' => Bill::query()
                ->posted()
                ->whereDate('visit_date', today())
                ->count(),
        ]);
    }
}

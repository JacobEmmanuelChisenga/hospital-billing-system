<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\View\View;

/**
 * Main landing page after login — role-specific operational dashboards.
 */
class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService,
    ) {}

    public function __invoke(): View
    {
        $user = auth()->user();

        if ($user->isRegistryClerk()) {
            return view('dashboard.registry', $this->dashboardService->registry());
        }

        if ($user->isNurse()) {
            return view('dashboard.nurse', $this->dashboardService->nurse());
        }

        if ($user->isAccountsStaff()) {
            return view('dashboard.accounts', $this->dashboardService->accounts());
        }

        return view('dashboard.admin', $this->dashboardService->admin());
    }
}

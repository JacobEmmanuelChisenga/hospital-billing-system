<?php

namespace App\Http\Controllers;

use App\Enums\AuditActionType;
use App\Http\Requests\ReverseDepositRequest;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\StoreCompanyDepositRequest;
use App\Models\Company;
use App\Models\CompanyDeposit;
use App\Services\AuditLogger;
use App\Services\CompanyDepositService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyAccountController extends Controller
{
    public function __construct(
        private CompanyDepositService $companyDepositService,
    ) {}

    /**
     * List all company accounts and their current pool balances.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $companies = Company::query()
            ->withCount('patients')
            ->when($search !== '', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($search).'%']))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('company-accounts.index', compact('companies', 'search'));
    }

    /**
     * Form to create a company account before registering company patients.
     */
    public function create(): View
    {
        return view('company-accounts.create');
    }

    /**
     * Save a company account. Company deposits are recorded separately.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $company = Company::query()->create($this->companyAttributes($request) + [
            'balance' => 0,
            'status' => 'active',
        ]);

        AuditLogger::log(
            AuditActionType::CompanyCreated,
            "Created company account: {$company->name}.",
            $company,
            ['company_id' => $company->id],
        );

        return redirect()
            ->route('company-accounts.show', $company)
            ->with('success', 'Company account created successfully.');
    }

    public function edit(Company $company): View
    {
        return view('company-accounts.edit', compact('company'));
    }

    public function update(StoreCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($this->companyAttributes($request));

        AuditLogger::log(
            AuditActionType::CompanyUpdated,
            "Updated company account: {$company->name}.",
            $company,
            ['company_id' => $company->id],
        );

        return redirect()
            ->route('company-accounts.show', $company)
            ->with('success', 'Company account updated successfully.');
    }

    public function suspend(Company $company): RedirectResponse
    {
        $company->update([
            'status' => $company->status === 'active' ? 'suspended' : 'active',
        ]);

        AuditLogger::log(
            AuditActionType::CompanySuspended,
            ucfirst($company->status)." company account: {$company->name}.",
            $company,
            ['company_id' => $company->id, 'status' => $company->status],
        );

        return redirect()
            ->route('company-accounts.show', $company)
            ->with('success', "Company account {$company->status}.");
    }

    private function companyAttributes(StoreCompanyRequest $request): array
    {
        return [
            'name' => $request->input('name'),
            'contact_person' => $request->input('contact_person'),
            'phone' => $request->input('phone'),
            'email' => $request->input('email'),
            'notes' => $request->input('notes'),
        ];
    }

    /**
     * Company account profile — balance, patients, deposit history, load deposit form.
     */
    public function show(Company $company): View
    {
        $company->load([
            'patients' => fn ($q) => $q->orderBy('name'),
            'deposits' => fn ($q) => $q->with('createdBy')->orderByDesc('deposit_date')->limit(20),
        ]);

        return view('company-accounts.show', [
            'company' => $company,
            'largeDepositThreshold' => config('hospital.large_deposit_threshold'),
        ]);
    }

    /**
     * Load a deposit into the company shared pool.
     */
    public function storeDeposit(StoreCompanyDepositRequest $request, Company $company): RedirectResponse
    {
        if ($company->status !== 'active') {
            return back()->with('error', 'Suspended company accounts cannot receive deposits.');
        }

        $deposit = $this->companyDepositService->record(
            $company,
            $request->safe()->only(['amount', 'deposit_date', 'reference', 'notes']),
            $request->user(),
        );

        return redirect()
            ->route('company-accounts.show', $company)
            ->with('success', 'Company deposit loaded successfully. Pool balance updated.');
    }

    /**
     * Reverse a company deposit and reduce the pool balance.
     */
    public function reverseDeposit(ReverseDepositRequest $request, CompanyDeposit $companyDeposit): RedirectResponse
    {
        $this->companyDepositService->reverse(
            $companyDeposit,
            $request->input('reversal_reason'),
            $request->user(),
        );

        return redirect()
            ->route('company-accounts.show', $companyDeposit->company_id)
            ->with('success', 'Company deposit reversed. Pool balance has been adjusted.');
    }
}

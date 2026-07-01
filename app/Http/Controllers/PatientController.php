<?php

namespace App\Http\Controllers;

use App\Enums\AuditActionType;
use App\Enums\MembershipStatus;
use App\Enums\PatientStatus;
use App\Enums\PatientType;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Company;
use App\Models\Membership;
use App\Models\Patient;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * List patients with search and filters for Accounts staff.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();

        $patients = Patient::query()
            ->with(['company', 'principalMember', 'membership'])
            ->search($search)
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('patients.index', [
            'patients' => $patients,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'patientTypes' => PatientType::cases(),
            'patientStatuses' => PatientStatus::cases(),
        ]);
    }

    /**
     * Show the registration form for a new member, dependant, or company patient.
     */
    public function create(): View
    {
        return view('patients.create', $this->formData());
    }

    /**
     * Save a newly registered patient and create a company record when needed.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $type = PatientType::from($request->input('type'));
        $patient = Patient::query()->create($this->buildPatientAttributes($request->validated(), $type));

        $patient->forceFill([
            'patient_number' => $this->patientNumber($patient),
        ])->save();

        if ($patient->isMember()) {
            $membershipNumber = $this->membershipNumber($patient);

            Membership::query()->create([
                'patient_id' => $patient->id,
                'membership_number' => $membershipNumber,
                'status' => MembershipStatus::PendingPayment,
                'start_date' => null,
                'expiry_date' => null,
            ]);

            $patient->forceFill(['hc_number' => $membershipNumber])->save();
        }

        AuditLogger::log(
            AuditActionType::PatientCreated,
            "Registered {$patient->type->label()}: {$patient->name}.",
            $patient,
        );

        if ($patient->isMember() || $patient->isDependant()) {
            AuditLogger::log(
                AuditActionType::MembershipRegistered,
                "Membership registered for {$patient->name} — pending Accounts payment.",
                $patient,
            );
        }

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Patient registered successfully.');
    }

    /**
     * Patient profile — details, effective balance, and recent activity.
     */
    public function show(Patient $patient): View
    {
        $patient->load([
            'company',
            'principalMember',
            'dependants',
            'membership',
            'deposits' => fn ($query) => $query->latest()->limit(5),
            'bills' => fn ($query) => $query->latest()->limit(5),
        ]);

        return view('patients.show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Edit an existing patient record.
     */
    public function edit(Patient $patient): View
    {
        $patient->load('membership');

        return view('patients.edit', array_merge(
            ['patient' => $patient],
            $this->formData(),
        ));
    }

    /**
     * Update patient details. Patient type cannot be changed after registration.
     */
    public function update(UpdatePatientRequest $request, Patient $patient): RedirectResponse
    {
        $patient->update(
            $this->buildPatientAttributes($request->validated(), $patient->type, $patient)
        );

        AuditLogger::log(
            AuditActionType::PatientUpdated,
            "Updated patient record: {$patient->name}.",
            $patient,
        );

        return redirect()
            ->route('patients.show', $patient)
            ->with('success', 'Patient updated successfully.');
    }

    /**
     * Data shared by the create and edit forms.
     */
    private function formData(): array
    {
        return [
            'companies' => Company::query()->orderBy('name')->get(),
            'principalMembers' => Patient::query()
                ->where('type', PatientType::Member)
                ->where('status', PatientStatus::Active)
                ->with('membership')
                ->orderBy('name')
                ->get(),
            'patientTypes' => PatientType::cases(),
            'patientStatuses' => PatientStatus::cases(),
        ];
    }

    /**
     * Turn validated form input into database-ready patient attributes.
     */
    private function buildPatientAttributes(array $data, PatientType $type, ?Patient $existing = null): array
    {
        $attributes = [
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'name' => $this->displayName($data),
            'gender' => $data['gender'],
            'date_of_birth' => $data['date_of_birth'],
            'hc_number' => $existing?->hc_number,
            'man_number' => $data['man_number'] ?? null,
            'department' => $data['department'] ?? null,
            'employment_status' => $data['employment_status'] ?? null,
            'file_number' => $data['file_number'] ?? null,
            'nrc_number' => $data['nrc_number'] ?? null,
            'nationality' => $data['nationality'],
            'marital_status' => $data['marital_status'],
            'phone_number' => $data['phone_number'] ?? null,
            'alternative_phone' => $data['alternative_phone'] ?? null,
            'email' => $data['email'] ?? null,
            'contact_address' => $data['contact_address'] ?? null,
            'town_city' => $data['town_city'] ?? null,
            'next_of_kin_name' => $data['next_of_kin_name'] ?? null,
            'next_of_kin_phone' => $data['next_of_kin_phone'] ?? null,
            'next_of_kin_relationship' => $data['next_of_kin_relationship'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        if ($existing === null) {
            $attributes['type'] = $type;
            $attributes['balance'] = 0;
            $attributes['status'] = PatientStatus::Active;

            $attributes['membership_status'] = match ($type) {
                PatientType::Member, PatientType::Dependant => MembershipStatus::PendingPayment,
                PatientType::Company => MembershipStatus::NotApplicable,
            };
        } else {
            $attributes['status'] = PatientStatus::from($data['status']);
        }

        // Clear linkage fields first, then set only what applies to this type.
        $attributes['principal_patient_id'] = null;
        $attributes['relationship'] = null;
        $attributes['company_id'] = null;

        if ($type === PatientType::Dependant) {
            $attributes['principal_patient_id'] = $data['principal_patient_id'];
            $attributes['relationship'] = $data['relationship'];
        }

        if ($type === PatientType::Company) {
            $attributes['company_id'] = $this->resolveCompanyId($data, $existing);
        } else {
            $attributes['man_number'] = null;
            $attributes['department'] = null;
            $attributes['employment_status'] = null;
        }

        return $attributes;
    }

    /**
     * Link company patients to an existing company account.
     */
    private function resolveCompanyId(array $data, ?Patient $existing): int
    {
        return (int) $data['company_id'];
    }

    private function displayName(array $data): string
    {
        return collect([
            $data['first_name'],
            $data['middle_name'] ?? null,
            $data['last_name'],
        ])->filter()->implode(' ');
    }

    private function patientNumber(Patient $patient): string
    {
        return 'RR-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);
    }

    private function membershipNumber(Patient $patient): string
    {
        return 'HC-'.str_pad((string) $patient->id, 6, '0', STR_PAD_LEFT);
    }
}

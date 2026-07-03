@php
    $isEdit = isset($patient);
    $selectedType = old('type', $patient->type->value ?? ($preselectedType ?? \App\Enums\PatientType::Member->value));
@endphp

<div
    x-data="{ type: @js($selectedType) }"
    class="space-y-6"
>
    @if ($isEdit)
        {{-- Patient type is fixed after registration to protect billing history. --}}
        <div>
            <x-input-label :value="__('Patient Type')" />
            <p class="mt-1 inline-flex items-center rounded-full bg-hospital-100 px-3 py-1 text-sm font-medium text-hospital-800">
                {{ $patient->type->label() }}
            </p>
        </div>
    @else
        <div>
            <x-input-label for="type" :value="__('Patient Type')" />
            <select
                id="type"
                name="type"
                x-model="type"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                required
            >
                @foreach ($patientTypes as $patientType)
                    <option value="{{ $patientType->value }}" @selected($selectedType === $patientType->value)>
                        {{ $patientType->label() }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('type')" class="mt-2" />
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-3">
        <div>
            <x-input-label for="first_name" :value="__('First Name')" />
            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                :value="old('first_name', $patient->first_name ?? '')" required />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="middle_name" :value="__('Middle Name (Optional)')" />
            <x-text-input id="middle_name" name="middle_name" type="text" class="mt-1 block w-full"
                :value="old('middle_name', $patient->middle_name ?? '')" />
            <x-input-error :messages="$errors->get('middle_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="last_name" :value="__('Last Name')" />
            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                :value="old('last_name', $patient->last_name ?? '')" required />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="gender" :value="__('Gender')" />
            <select id="gender" name="gender" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                <option value="">Select gender...</option>
                @foreach (['female' => 'Female', 'male' => 'Male', 'other' => 'Other'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $patient->gender ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full"
                :value="old('date_of_birth', $isEdit && $patient->date_of_birth ? $patient->date_of_birth->toDateString() : '')" required />
            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nrc_number" :value="__('NRC / Passport Number')" />
            <x-text-input id="nrc_number" name="nrc_number" type="text" class="mt-1 block w-full"
                :value="old('nrc_number', $patient->nrc_number ?? '')" required />
            <x-input-error :messages="$errors->get('nrc_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="nationality" :value="__('Nationality')" />
            <x-text-input id="nationality" name="nationality" type="text" class="mt-1 block w-full"
                :value="old('nationality', $patient->nationality ?? 'Zambian')" required />
            <x-input-error :messages="$errors->get('nationality')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="marital_status" :value="__('Marital Status')" />
            <select id="marital_status" name="marital_status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                <option value="">Select status...</option>
                @foreach (['single' => 'Single', 'married' => 'Married', 'widowed' => 'Widowed', 'divorced' => 'Divorced'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('marital_status', $patient->marital_status ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('marital_status')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="file_number" :value="__('File Number (Optional)')" />
            <x-text-input id="file_number" name="file_number" type="text" class="mt-1 block w-full"
                :value="old('file_number', $patient->file_number ?? '')" />
            <x-input-error :messages="$errors->get('file_number')" class="mt-2" />
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="phone_number" :value="__('Mobile Number')" />
            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full"
                :value="old('phone_number', $patient->phone_number ?? '')" required />
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="alternative_phone" :value="__('Alternative Phone (Optional)')" />
            <x-text-input id="alternative_phone" name="alternative_phone" type="text" class="mt-1 block w-full"
                :value="old('alternative_phone', $patient->alternative_phone ?? '')" />
            <x-input-error :messages="$errors->get('alternative_phone')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email (Optional)')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                :value="old('email', $patient->email ?? '')" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="town_city" :value="__('Town / City')" />
            <x-text-input id="town_city" name="town_city" type="text" class="mt-1 block w-full"
                :value="old('town_city', $patient->town_city ?? '')" required />
            <x-input-error :messages="$errors->get('town_city')" class="mt-2" />
        </div>

        <div class="md:col-span-2">
            <x-input-label for="contact_address" :value="__('Residential Address')" />
            <x-text-input id="contact_address" name="contact_address" type="text" class="mt-1 block w-full"
                :value="old('contact_address', $patient->contact_address ?? '')" required />
            <x-input-error :messages="$errors->get('contact_address')" class="mt-2" />
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
        <p class="text-sm font-medium text-gray-700">
            <i class="fa-solid fa-user-shield mr-1 text-hospital-600"></i> Next of Kin
        </p>
        <div class="mt-4 grid gap-6 md:grid-cols-3">
            <div>
                <x-input-label for="next_of_kin_name" :value="__('Name')" />
                <x-text-input id="next_of_kin_name" name="next_of_kin_name" type="text" class="mt-1 block w-full"
                    :value="old('next_of_kin_name', $patient->next_of_kin_name ?? '')" />
                <x-input-error :messages="$errors->get('next_of_kin_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="next_of_kin_phone" :value="__('Phone')" />
                <x-text-input id="next_of_kin_phone" name="next_of_kin_phone" type="text" class="mt-1 block w-full"
                    :value="old('next_of_kin_phone', $patient->next_of_kin_phone ?? '')" />
                <x-input-error :messages="$errors->get('next_of_kin_phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="next_of_kin_relationship" :value="__('Relationship')" />
                <x-text-input id="next_of_kin_relationship" name="next_of_kin_relationship" type="text" class="mt-1 block w-full"
                    :value="old('next_of_kin_relationship', $patient->next_of_kin_relationship ?? '')" />
                <x-input-error :messages="$errors->get('next_of_kin_relationship')" class="mt-2" />
            </div>
        </div>
    </div>

    {{-- Individual member-only fields --}}
    <div x-show="type === 'member'" x-cloak class="rounded-lg border border-amber-200 bg-amber-50 p-4 space-y-2">
        <p class="text-sm font-medium text-amber-800">
            <i class="fa-solid fa-id-card mr-1"></i> Individual Member
        </p>
        @if ($isEdit && $patient->membership)
            <p class="text-sm text-amber-900">Membership Number: <span class="font-semibold">{{ $patient->membership->membership_number }}</span></p>
        @else
            <p class="text-sm text-amber-900">The system will generate a membership number and mark membership as pending payment.</p>
        @endif
        <p class="text-xs text-amber-800">No company or MAN number is required for individual members.</p>
    </div>

    {{-- Dependant-only fields --}}
    <div x-show="type === 'dependant'" x-cloak class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
        <p class="text-sm font-medium text-gray-700">
            <i class="fa-solid fa-link mr-1 text-hospital-600"></i> Dependant Details
        </p>

        <div>
            <x-input-label for="principal_patient_id" :value="__('Principal Member')" />
            <select id="principal_patient_id" name="principal_patient_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                <option value="">Select principal member...</option>
                @foreach ($principalMembers as $member)
                    <option value="{{ $member->id }}" @selected((string) old('principal_patient_id', $patient->principal_patient_id ?? '') === (string) $member->id)>
                        {{ $member->name }}
                        @if($member->membership)
                            ({{ $member->membership->membership_number }})
                        @endif
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('principal_patient_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="relationship" :value="__('Relationship')" />
            <select id="relationship" name="relationship"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                <option value="">Select relationship...</option>
                @foreach (['Spouse', 'Child', 'Parent', 'Other'] as $relationship)
                    <option value="{{ $relationship }}" @selected(old('relationship', $patient->relationship ?? '') === $relationship)>{{ $relationship }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('relationship')" class="mt-2" />
        </div>
        <p class="text-xs text-gray-500">Dependants are linked to a principal member. They do not use company or MAN number fields.</p>
    </div>

    {{-- Company patient fields --}}
    <div x-show="type === 'company'" x-cloak class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-4">
        <p class="text-sm font-medium text-gray-700">
            <i class="fa-solid fa-building mr-1 text-hospital-600"></i> Company Details
        </p>

        <div>
            <x-input-label for="company_id" :value="__('Company')" />
            <select id="company_id" name="company_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                <option value="">Select company...</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected((string) old('company_id', $patient->company_id ?? '') === (string) $company->id)>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('company_id')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">If the company is missing, ask Accounts to create the company account first.</p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            <div>
                <x-input-label for="man_number" :value="__('Employee MAN Number')" />
                <x-text-input id="man_number" name="man_number" type="text" class="mt-1 block w-full"
                    :value="old('man_number', $patient->man_number ?? '')" />
                <x-input-error :messages="$errors->get('man_number')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="department" :value="__('Department (Optional)')" />
                <x-text-input id="department" name="department" type="text" class="mt-1 block w-full"
                    :value="old('department', $patient->department ?? '')" />
                <x-input-error :messages="$errors->get('department')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="employment_status" :value="__('Employment Status (Optional)')" />
                <x-text-input id="employment_status" name="employment_status" type="text" class="mt-1 block w-full"
                    :value="old('employment_status', $patient->employment_status ?? '')" />
                <x-input-error :messages="$errors->get('employment_status')" class="mt-2" />
            </div>
        </div>
    </div>

    @if ($isEdit)
        <div>
            <x-input-label for="status" :value="__('Status')" />
            <select id="status" name="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                required>
                @foreach ($patientStatuses as $patientStatus)
                    <option value="{{ $patientStatus->value }}" @selected(old('status', $patient->status->value) === $patientStatus->value)>
                        {{ $patientStatus->label() }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
            <p class="mt-1 text-xs text-gray-500">Inactive patients remain in the system but are hidden from normal billing workflows.</p>
        </div>
    @endif

    <div>
        <x-input-label for="notes" :value="__('Notes')" />
        <textarea id="notes" name="notes" rows="3"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old('notes', $patient->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>

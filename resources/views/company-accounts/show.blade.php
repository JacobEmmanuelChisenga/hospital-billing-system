<x-app-layout>
    <x-slot name="header">
        <x-page-header title="{{ $company->name }}" subtitle="Company deposit pool and linked patients.">
            <x-slot name="actions">
                <a href="{{ route('company-accounts.edit', $company) }}" class="btn-primary">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </a>
                @php($toggleMessage = $company->status === 'active' ? 'Suspend this company account?' : 'Reactivate this company account?')
                <form method="POST" action="{{ route('company-accounts.suspend', $company) }}"
                      onsubmit="return confirm('{{ $toggleMessage }}');">
                    @csrf
                    <button type="submit" class="btn-secondary">
                        {{ $company->status === 'active' ? 'Suspend' : 'Reactivate' }}
                    </button>
                </form>
                <a href="{{ route('company-accounts.index') }}" class="btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> All Companies
                </a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card card-body border-hospital-200 bg-hospital-50 lg:col-span-1">
            <p class="section-subtitle text-hospital-700">Pool Balance</p>
            <p class="mt-2 text-3xl font-bold text-hospital-900">K {{ number_format((float) $company->balance, 2) }}</p>
            <p class="mt-2 text-sm text-hospital-700">{{ $company->patients->count() }} linked patient(s)</p>
            <p class="mt-2">
                @if ($company->status === 'active')
                    <span class="badge badge-success">{{ ucfirst($company->status) }}</span>
                @else
                    <span class="badge badge-danger">{{ ucfirst($company->status) }}</span>
                @endif
            </p>
            @if ($company->contact_person || $company->phone || $company->email)
                <dl class="mt-4 space-y-2 text-sm text-hospital-800">
                    @if ($company->contact_person)
                        <div><dt class="text-hospital-600">Contact</dt><dd>{{ $company->contact_person }}</dd></div>
                    @endif
                    @if ($company->phone)
                        <div><dt class="text-hospital-600">Phone</dt><dd>{{ $company->phone }}</dd></div>
                    @endif
                    @if ($company->email)
                        <div><dt class="text-hospital-600">Email</dt><dd>{{ $company->email }}</dd></div>
                    @endif
                </dl>
            @endif
        </div>

        <div class="card card-body lg:col-span-2">
            <h3 class="section-title">Load Company Deposit</h3>
            <p class="section-subtitle">Add funds to this company's shared high-cost pool.</p>

            @if ($company->status === 'active')
                <form method="POST" action="{{ route('company-accounts.deposits.store', $company) }}"
                      x-data="{ amount: @js(old('amount', '')), threshold: @js($largeDepositThreshold) }"
                      class="mt-4 space-y-4">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <x-input-label for="amount" :value="__('Amount (K)')" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01"
                                x-model="amount" class="mt-1 block w-full" :value="old('amount')" required />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="deposit_date" :value="__('Deposit Date')" />
                            <x-text-input id="deposit_date" name="deposit_date" type="date"
                                class="mt-1 block w-full" :value="old('deposit_date', now()->toDateString())" required />
                            <x-input-error :messages="$errors->get('deposit_date')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="reference" :value="__('Reference')" />
                        <x-text-input id="reference" name="reference" type="text" class="mt-1 block w-full" :value="old('reference')" />
                        <x-input-error :messages="$errors->get('reference')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Notes')" />
                        <textarea id="notes" name="notes" rows="2" class="form-input mt-1">{{ old('notes') }}</textarea>
                    </div>

                    <div x-show="parseFloat(amount) >= threshold" x-cloak
                         class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-medium text-amber-800">Large deposit — please confirm</p>
                        <label class="mt-2 flex items-start gap-2">
                            <input type="checkbox" name="confirm_large_deposit" value="1"
                                @checked(old('confirm_large_deposit'))
                                class="mt-1 rounded border-slate-300 text-hospital-600 focus:ring-hospital-500">
                            <span class="text-sm text-amber-900">I confirm this large deposit amount is correct.</span>
                        </label>
                        <x-input-error :messages="$errors->get('confirm_large_deposit')" class="mt-2" />
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-money-bill-wave"></i> Load Deposit
                    </button>
                </form>
            @else
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    This company account is suspended. Reactivate it before receiving company deposits.
                </div>
            @endif
        </div>
    </div>

    @if ($company->patients->isNotEmpty())
        <x-data-panel title="Company Patients" class="mt-6">
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>HC Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($company->patients as $patient)
                            <tr>
                                <td>
                                    <a href="{{ route('patients.show', $patient) }}" class="action-link">{{ $patient->name }}</a>
                                </td>
                                <td>{{ $patient->hc_number ?? '—' }}</td>
                                <td>{{ $patient->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        </x-data-panel>
    @endif

    <x-data-panel title="Deposit History" class="mt-6">
        @if ($company->deposits->isEmpty())
            <p class="text-sm text-slate-500">No deposits loaded yet.</p>
        @else
            <x-table-scroll>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                            <th>Loaded By</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($company->deposits as $deposit)
                            <tr>
                                <td class="whitespace-nowrap">{{ $deposit->deposit_date->format('d M Y') }}</td>
                                <td>{{ $deposit->reference ?? '—' }}</td>
                                <td class="text-right font-medium">K {{ number_format((float) $deposit->amount, 2) }}</td>
                                <td>{{ $deposit->createdBy->name }}</td>
                                <td>
                                    @if ($deposit->isReversed())
                                        <span class="badge badge-danger">Reversed</span>
                                    @else
                                        <span class="badge badge-success">Active</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if (! $deposit->isReversed())
                                        <button type="button"
                                            x-data=""
                                            x-on:click="$dispatch('open-reverse-modal', { id: {{ $deposit->id }}, amount: '{{ number_format((float) $deposit->amount, 2) }}' })"
                                            class="action-link text-red-600">
                                            Reverse
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400" title="{{ $deposit->reversal_reason }}">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-table-scroll>
        @endif
    </x-data-panel>

    <div x-data="{ open: false, depositId: null, amount: '' }"
         x-on:open-reverse-modal.window="open = true; depositId = $event.detail.id; amount = $event.detail.amount"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="open = false" class="card card-body w-full max-w-md shadow-xl">
            <h3 class="section-title text-red-800">Reverse Company Deposit</h3>
            <p class="section-subtitle">This will deduct K <span x-text="amount"></span> from the company pool.</p>
            <form :action="'/company-deposits/' + depositId + '/reverse'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-input-label for="company_reversal_reason" :value="__('Reason')" />
                    <textarea id="company_reversal_reason" name="reversal_reason" rows="3" required
                        class="form-input mt-1"
                        placeholder="Reason for reversal (min. 10 characters)..."></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-danger">Confirm Reverse</button>
                    <button type="button" @click="open = false" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $company->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Company deposit pool and linked patients.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('company-accounts.edit', $company) }}"
                   class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Edit
                </a>
                @php($toggleMessage = $company->status === 'active' ? 'Suspend this company account?' : 'Reactivate this company account?')
                <form method="POST" action="{{ route('company-accounts.suspend', $company) }}"
                      onsubmit="return confirm('{{ $toggleMessage }}');">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ $company->status === 'active' ? 'Suspend' : 'Reactivate' }}
                    </button>
                </form>
                <a href="{{ route('company-accounts.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> All Companies
                </a>
            </div>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-hospital-200 bg-hospital-50 p-6">
            <p class="text-sm font-medium text-hospital-700">Pool Balance</p>
            <p class="mt-2 text-3xl font-bold text-hospital-900">K {{ number_format((float) $company->balance, 2) }}</p>
            <p class="mt-2 text-sm text-hospital-700">{{ $company->patients->count() }} linked patient(s)</p>
            <p class="mt-2">
                <span @class([
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    'bg-green-100 text-green-800' => $company->status === 'active',
                    'bg-red-100 text-red-800' => $company->status === 'suspended',
                ])>
                    {{ ucfirst($company->status) }}
                </span>
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

        {{-- Load company deposit form --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="text-base font-semibold text-gray-800">Load Company Deposit</h3>
            <p class="mt-1 text-sm text-gray-500">Add funds to this company's shared high-cost pool.</p>

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
                        <textarea id="notes" name="notes" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">{{ old('notes') }}</textarea>
                    </div>

                    <div x-show="parseFloat(amount) >= threshold" x-cloak
                         class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-medium text-amber-800">Large deposit — please confirm</p>
                        <label class="mt-2 flex items-start gap-2">
                            <input type="checkbox" name="confirm_large_deposit" value="1"
                                @checked(old('confirm_large_deposit'))
                                class="mt-1 rounded border-gray-300 text-hospital-600 focus:ring-hospital-500">
                            <span class="text-sm text-amber-900">I confirm this large deposit amount is correct.</span>
                        </label>
                        <x-input-error :messages="$errors->get('confirm_large_deposit')" class="mt-2" />
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
                        <i class="fa-solid fa-money-bill-wave mr-2"></i> Load Deposit
                    </button>
                </form>
            @else
                <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                    This company account is suspended. Reactivate it before receiving company deposits.
                </div>
            @endif
        </div>
    </div>

    {{-- Linked company patients --}}
    @if ($company->patients->isNotEmpty())
        <div class="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold text-gray-800">Company Patients</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-gray-500">
                            <th class="pb-2 font-medium">Name</th>
                            <th class="pb-2 font-medium">HC Number</th>
                            <th class="pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($company->patients as $patient)
                            <tr>
                                <td class="py-2">
                                    <a href="{{ route('patients.show', $patient) }}" class="text-hospital-700 hover:underline">{{ $patient->name }}</a>
                                </td>
                                <td class="py-2 text-gray-700">{{ $patient->hc_number ?? '—' }}</td>
                                <td class="py-2 text-gray-700">{{ $patient->status->label() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Deposit history --}}
    <div class="mt-6 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-gray-800">Deposit History</h3>
        @if ($company->deposits->isEmpty())
            <p class="mt-3 text-sm text-gray-500">No deposits loaded yet.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-gray-500">
                            <th class="pb-2 font-medium">Date</th>
                            <th class="pb-2 font-medium">Reference</th>
                            <th class="pb-2 font-medium text-right">Amount</th>
                            <th class="pb-2 font-medium">Loaded By</th>
                            <th class="pb-2 font-medium">Status</th>
                            <th class="pb-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($company->deposits as $deposit)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $deposit->deposit_date->format('d M Y') }}</td>
                                <td class="py-2 text-gray-700">{{ $deposit->reference ?? '—' }}</td>
                                <td class="py-2 text-right font-medium">K {{ number_format((float) $deposit->amount, 2) }}</td>
                                <td class="py-2 text-gray-700">{{ $deposit->createdBy->name }}</td>
                                <td class="py-2">
                                    @if ($deposit->isReversed())
                                        <span class="text-xs font-medium text-red-700">Reversed</span>
                                    @else
                                        <span class="text-xs font-medium text-green-700">Active</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    @if (! $deposit->isReversed())
                                        <button type="button"
                                            x-data=""
                                            x-on:click="$dispatch('open-reverse-modal', { id: {{ $deposit->id }}, amount: '{{ number_format((float) $deposit->amount, 2) }}' })"
                                            class="text-red-600 hover:text-red-800 text-xs">
                                            Reverse
                                        </button>
                                    @else
                                        <span class="text-xs text-gray-400" title="{{ $deposit->reversal_reason }}">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Reverse deposit modal (Alpine) --}}
    <div x-data="{ open: false, depositId: null, amount: '' }"
         x-on:open-reverse-modal.window="open = true; depositId = $event.detail.id; amount = $event.detail.amount"
         x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div @click.outside="open = false" class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-semibold text-red-800">Reverse Company Deposit</h3>
            <p class="mt-2 text-sm text-gray-600">This will deduct K <span x-text="amount"></span> from the company pool.</p>
            <form :action="'/company-deposits/' + depositId + '/reverse'" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <x-input-label for="company_reversal_reason" :value="__('Reason')" />
                    <textarea id="company_reversal_reason" name="reversal_reason" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500"
                        placeholder="Reason for reversal (min. 10 characters)..."></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirm Reverse</button>
                    <button type="button" @click="open = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>

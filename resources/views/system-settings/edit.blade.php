<x-app-layout>
    <x-slot name="header">
        <x-page-header title="System Settings" subtitle="Hospital branding and billing thresholds used across the application." />
    </x-slot>

    <x-flash-messages />

    <div class="card card-body max-w-3xl">
        <form method="POST" action="{{ route('system-settings.update') }}" class="space-y-8">
            @csrf
            @method('PATCH')

            <div>
                <h3 class="section-title">Hospital Branding</h3>
                <div class="mt-4 grid gap-6">
                    <div>
                        <x-input-label for="name" :value="__('Hospital Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $settings['name'])" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="section" :value="__('Section Name')" />
                            <x-text-input id="section" name="section" type="text" class="mt-1 block w-full"
                                :value="old('section', $settings['section'])" required />
                            <x-input-error :messages="$errors->get('section')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="system_name" :value="__('System Name')" />
                            <x-text-input id="system_name" name="system_name" type="text" class="mt-1 block w-full"
                                :value="old('system_name', $settings['system_name'])" required />
                            <x-input-error :messages="$errors->get('system_name')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-8">
                <h3 class="section-title">Session &amp; Billing Thresholds</h3>
                <p class="section-subtitle">These values affect deposit confirmations, billing warnings, and the login screen.</p>
                <div class="mt-4 grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-input-label for="session_lifetime_minutes" :value="__('Session Lifetime (minutes)')" />
                        <x-text-input id="session_lifetime_minutes" name="session_lifetime_minutes" type="number" min="5" max="480"
                            class="mt-1 block w-full" :value="old('session_lifetime_minutes', $settings['session_lifetime_minutes'])" required />
                        <x-input-error :messages="$errors->get('session_lifetime_minutes')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="large_deposit_threshold" :value="__('Large Deposit Threshold (K)')" />
                        <x-text-input id="large_deposit_threshold" name="large_deposit_threshold" type="number" step="0.01" min="0"
                            class="mt-1 block w-full" :value="old('large_deposit_threshold', $settings['large_deposit_threshold'])" required />
                        <x-input-error :messages="$errors->get('large_deposit_threshold')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="low_balance_threshold" :value="__('Low Balance Warning (K)')" />
                        <x-text-input id="low_balance_threshold" name="low_balance_threshold" type="number" step="0.01" min="0"
                            class="mt-1 block w-full" :value="old('low_balance_threshold', $settings['low_balance_threshold'])" required />
                        <x-input-error :messages="$errors->get('low_balance_threshold')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</x-app-layout>

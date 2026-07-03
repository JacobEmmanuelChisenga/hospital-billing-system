<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Receipts</h2>
            <p class="mt-1 text-sm text-gray-500">View and print bills posted by the Registry Clerk from patient visits.</p>
        </div>
    </x-slot>

    <x-flash-messages />

    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold text-gray-800">Today's Posted Bills</h3>
        @if ($todaysBills->isEmpty())
            <p class="mt-4 text-sm text-gray-500">No bills posted today.</p>
        @else
            <div class="mt-4 table-scroll -mx-6 px-6 sm:mx-0 sm:px-0">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-gray-500">
                            <th class="pb-2 font-medium">Time</th>
                            <th class="pb-2 font-medium">Patient</th>
                            <th class="pb-2 font-medium">Visit</th>
                            <th class="pb-2 font-medium text-right">Amount</th>
                            <th class="pb-2 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($todaysBills as $bill)
                            <tr>
                                <td class="py-2 text-gray-700">{{ $bill->created_at->format('H:i') }}</td>
                                <td class="py-2 font-medium">{{ $bill->patient->name }}</td>
                                <td class="py-2 text-gray-700">{{ $bill->visit_type->label() }}</td>
                                <td class="py-2 text-right font-medium">K {{ number_format((float) $bill->total_amount, 2) }}</td>
                                <td class="py-2 text-right space-x-2">
                                    <a href="{{ route('billing.show', $bill) }}" class="text-hospital-700 hover:underline">View</a>
                                    <a href="{{ route('billing.receipt', $bill) }}" class="text-hospital-700 hover:underline">Receipt</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>

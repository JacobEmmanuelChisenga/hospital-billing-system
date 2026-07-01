<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    // Backward compatible defaults: billing receipts still pass $bill only.
    $receiptBackUrl = $backUrl ?? (isset($bill) ? route('billing.show', $bill) : url()->previous());
    $receiptDocTitle = $receiptTitle ?? (isset($bill) ? 'Receipt #'.$bill->id : 'Receipt');
@endphp
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $receiptDocTitle }} — {{ config('hospital.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">
    <div class="no-print bg-gray-100 border-b px-4 py-3 flex justify-between items-center">
        <a href="{{ $receiptBackUrl }}" class="text-sm text-hospital-700 hover:underline">&larr; Back</a>
        <button onclick="window.print()" class="rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
            <i class="fa-solid fa-print mr-1"></i> Print
        </button>
    </div>

    <div class="max-w-lg mx-auto p-8">
        @yield('content')
    </div>

    @if (session('success'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>

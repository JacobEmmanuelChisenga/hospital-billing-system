<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Report' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 24px; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #0f4c45; }
        h2 { font-size: 14px; margin: 18px 0 8px; color: #111827; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
        .grid { width: 100%; margin-bottom: 16px; }
        .grid td { padding: 8px 10px; border: 1px solid #e5e7eb; }
        .grid .label { background: #f9fafb; width: 40%; font-weight: bold; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        table.data th { background: #f3f4f6; font-size: 10px; text-transform: uppercase; }
        table.data td.num { text-align: right; }
        .totals { margin-top: 12px; font-weight: bold; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 10px; margin-bottom: 14px; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#6b7280;">{{ config('hospital.name') }}</div>
        <h1>{{ $title ?? 'Report' }}</h1>
        @isset($subtitle)
            <div class="muted">{{ $subtitle }}</div>
        @endisset
        <div class="meta">
            Period: {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}
            &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }}
        </div>
    </div>

    @yield('content')
</body>
</html>

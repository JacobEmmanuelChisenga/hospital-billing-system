@if (session('success'))
    <div class="alert-success">
        <i class="fa-solid fa-circle-check mt-0.5 shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert-error">
        <i class="fa-solid fa-circle-exclamation mt-0.5 shrink-0"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

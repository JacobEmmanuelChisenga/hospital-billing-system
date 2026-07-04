@php
    $isEdit = isset($service);
    $formAction = $isEdit
        ? route('billable-services.update', $service)
        : route('billable-services.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-6">
    @csrf
    @if ($isEdit)
        @method('PATCH')
    @endif

    <div>
        <x-input-label for="name" :value="__('Service Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $service->name ?? '')" required />
        <p class="form-hint mt-1">Shown to the Registry Clerk when adding charges to a visit.</p>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="category" :value="__('Category')" />
            <select id="category" name="category" required class="form-input mt-1">
                @foreach ($categories as $categoryOption)
                    <option value="{{ $categoryOption->value }}" @selected(old('category', $service->category->value ?? '') === $categoryOption->value)>
                        {{ $categoryOption->label() }}
                    </option>
                @endforeach
            </select>
            <p class="form-hint mt-1">Maps charges to the correct section on the bill.</p>
            <x-input-error :messages="$errors->get('category')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="price" :value="__('Price (K)')" />
            <x-text-input id="price" name="price" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                :value="old('price', $service->price ?? '')" required />
            <x-input-error :messages="$errors->get('price')" class="mt-2" />
        </div>
    </div>

    <div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="is_active" value="1"
                class="rounded border-slate-300 text-hospital-600 focus:ring-hospital-500"
                @checked(old('is_active', $service->is_active ?? true))>
            Active — available for new charges on visits
        </label>
        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
    </div>

    <div class="panel-footer -mx-6 -mb-6 mt-6 flex items-center gap-3 px-6 py-4">
        <button type="submit" class="btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> {{ $isEdit ? 'Save Changes' : 'Add Service' }}
        </button>
        <a href="{{ route('billable-services.index') }}" class="btn-ghost">Cancel</a>
    </div>
</form>

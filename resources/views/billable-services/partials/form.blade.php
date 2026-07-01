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
        <p class="mt-1 text-xs text-gray-500">Shown to the Registry Clerk when adding charges to a visit.</p>
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <x-input-label for="category" :value="__('Category')" />
            <select id="category" name="category" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-hospital-500 focus:ring-hospital-500">
                @foreach ($categories as $categoryOption)
                    <option value="{{ $categoryOption->value }}" @selected(old('category', $service->category->value ?? '') === $categoryOption->value)>
                        {{ $categoryOption->label() }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Maps charges to the correct section on the bill.</p>
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
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1"
                class="rounded border-gray-300 text-hospital-600 focus:ring-hospital-500"
                @checked(old('is_active', $service->is_active ?? true))>
            Active — available for new charges on visits
        </label>
        <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
    </div>

    <div class="flex items-center gap-3 border-t border-gray-100 pt-6">
        <button type="submit" class="inline-flex items-center rounded-lg bg-hospital-700 px-4 py-2 text-sm font-medium text-white hover:bg-hospital-800">
            <i class="fa-solid fa-floppy-disk mr-2"></i> {{ $isEdit ? 'Save Changes' : 'Add Service' }}
        </button>
        <a href="{{ route('billable-services.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</form>

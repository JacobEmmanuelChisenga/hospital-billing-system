<?php

namespace App\Http\Requests;

use App\Enums\ChargeCategory;
use App\Models\BillableService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBillableServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdministrator() ?? false;
    }

    public function rules(): array
    {
        /** @var BillableService $service */
        $service = $this->route('billableService');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('billable_services', 'name')->ignore($service->id),
            ],
            'category' => ['required', Rule::enum(ChargeCategory::class)],
            'price' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

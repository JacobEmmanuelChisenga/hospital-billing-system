<?php

namespace App\Http\Requests;

use App\Models\BillableService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChargeLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageVisits() ?? false;
    }

    public function rules(): array
    {
        return [
            'billable_service_id' => [
                'required',
                Rule::exists(BillableService::class, 'id')->where('is_active', true),
            ],
        ];
    }
}

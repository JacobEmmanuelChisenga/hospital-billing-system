<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClinicalNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canRecordClinicalNotes() ?? false;
    }

    public function rules(): array
    {
        return [
            'complaint' => ['nullable', 'string', 'max:5000'],
            'vitals' => ['nullable', 'string', 'max:2000'],
            'examination_findings' => ['nullable', 'string', 'max:5000'],
            'diagnosis' => ['nullable', 'string', 'max:5000'],
            'treatment_notes' => ['nullable', 'string', 'max:5000'],
            'procedures_performed' => ['nullable', 'string', 'max:5000'],
            'follow_up_instructions' => ['nullable', 'string', 'max:5000'],
        ];
    }
}

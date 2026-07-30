<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(array_column(ProjectStatus::cases(), 'value'))],
            'remarks' => [
                Rule::requiredIf(fn () => in_array($this->input('status'), [
                    ProjectStatus::REVISION_REQUIRED->value,
                    ProjectStatus::REJECTED->value,
                ])),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}

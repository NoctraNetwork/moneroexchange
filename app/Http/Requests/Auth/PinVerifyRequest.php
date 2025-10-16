<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\PinService;

class PinVerifyRequest extends FormRequest
{
    private PinService $pinService;

    public function __construct(PinService $pinService)
    {
        parent::__construct();
        $this->pinService = $pinService;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'pin' => [
                'required',
                'string',
                'min:4',
                'max:8',
                'regex:/^\d+$/',
                function ($attribute, $value, $fail) {
                    if (!$this->pinService->validatePinFormat($value)) {
                        $fail('The PIN must be between 4 and 8 digits.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pin.required' => 'PIN is required.',
            'pin.string' => 'PIN must be a string.',
            'pin.min' => 'PIN must be at least 4 digits.',
            'pin.max' => 'PIN may not be greater than 8 digits.',
            'pin.regex' => 'PIN may only contain digits.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'pin' => 'PIN',
        ];
    }
}


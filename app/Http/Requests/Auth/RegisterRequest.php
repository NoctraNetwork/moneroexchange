<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Services\PinService;

class RegisterRequest extends FormRequest
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
            'username' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9_-]+$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'max:255',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => [
                'required',
                'string',
                'same:password',
            ],
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
                    
                    if ($this->pinService->isCommonPin($value)) {
                        $fail('The PIN is too common. Please choose a different PIN.');
                    }
                },
            ],
            'pin_confirmation' => [
                'required',
                'string',
                'same:pin',
            ],
            'country' => [
                'nullable',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/',
            ],
            'terms' => [
                'required',
                'accepted',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Username is required.',
            'username.string' => 'Username must be a string.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username may not be greater than 255 characters.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'Username may only contain letters, numbers, underscores, and hyphens.',
            
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a string.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password may not be greater than 255 characters.',
            
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.string' => 'Password confirmation must be a string.',
            'password_confirmation.same' => 'Password confirmation does not match.',
            
            'pin.required' => 'PIN is required.',
            'pin.string' => 'PIN must be a string.',
            'pin.min' => 'PIN must be at least 4 digits.',
            'pin.max' => 'PIN may not be greater than 8 digits.',
            'pin.regex' => 'PIN may only contain digits.',
            
            'pin_confirmation.required' => 'PIN confirmation is required.',
            'pin_confirmation.string' => 'PIN confirmation must be a string.',
            'pin_confirmation.same' => 'PIN confirmation does not match.',
            
            'country.nullable' => 'Country must be null or a string.',
            'country.string' => 'Country must be a string.',
            'country.size' => 'Country must be exactly 2 characters.',
            'country.regex' => 'Country must be a valid ISO country code.',
            
            'terms.required' => 'You must accept the terms and conditions.',
            'terms.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'username' => 'username',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'pin' => 'PIN',
            'pin_confirmation' => 'PIN confirmation',
            'country' => 'country',
            'terms' => 'terms and conditions',
        ];
    }
}


<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:128'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $login = $this->input('login');

            if (! $login) {
                return;
            }

            // Check if login is an email format
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                // Validate email exists
                $userExists = \App\Models\User::query()
                    ->where('email', $login)
                    ->exists();

                if (! $userExists) {
                    $validator->errors()->add('login', 'Credenciais inválidas.');
                }

                return;
            }

            $onlyDigits = preg_replace('/\D+/', '', $login);

            // Check if login is cpf_cnpj (11 or 14 digits)
            if (preg_match('/^\d{11}$|^\d{14}$/', $onlyDigits)) {
                // Validate cpf_cnpj exists
                $userExists = \App\Models\User::query()
                    ->where('cpf_cnpj', $onlyDigits)
                    ->exists();

                if (! $userExists) {
                    $validator->errors()->add('login', 'Credenciais inválidas.');
                }

                return;
            }

            // If neither email nor valid cpf_cnpj
            $validator->errors()->add('login', 'Login deve ser um email ou CPF/CNPJ válido.');
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Login (email ou CPF/CNPJ) é obrigatório.',
            'login.max' => 'Login é muito longo.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.max' => 'A senha deve ter no máximo 128 caracteres.',
        ];
    }
}

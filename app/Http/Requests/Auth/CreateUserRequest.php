<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:128'],
            'cpf_cnpj' => ['required', 'string', 'unique:users,cpf_cnpj', $this->validateCpfCnpj()],
            'name' => ['required', 'string', 'max:255'],
            'lojista' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom validation rule for CPF/CNPJ based on type.
     */
    private function validateCpfCnpj(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $onlyDigits = preg_replace('/\D+/', '', $value);
            $is_lojista = $this->boolean('lojista');

            if (!$is_lojista && strlen($onlyDigits) !== 11) {
                $fail('O CPF deve conter 11 dígitos.');

                return;
            }

            if ($is_lojista && strlen($onlyDigits) !== 14) {
                $fail('O CNPJ deve conter 14 dígitos.');

                return;
            }
        };
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email é obrigatório.',
            'email.email' => 'Email deve ser um endereço de email válido.',
            'email.unique' => 'Este email já está registrado.',
            'password.required' => 'Senha é obrigatória.',
            'password.min' => 'Senha deve ter no mínimo 6 caracteres.',
            'password.max' => 'Senha deve ter no máximo 128 caracteres.',
            'cpf_cnpj.required' => 'CPF ou CNPJ é obrigatório.',
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está registrado.',
            'name.required' => 'Nome é obrigatório.',
            'name.max' => 'Nome deve ter no máximo 255 caracteres.',
            'lojista.required_if' => 'Campo lojista é obrigatório para pessoas jurídicas.',
            'lojista.boolean' => 'Campo lojista deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf_cnpj' => preg_replace('/\D+/', '', $this->input('cpf_cnpj', '')),
            'lojista' => $this->input('type') === 'cnpj' ? true : ($this->input('lojista') ?? false),
        ]);
    }
}

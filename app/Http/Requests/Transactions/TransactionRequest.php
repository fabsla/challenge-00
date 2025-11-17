<?php

namespace App\Http\Requests\Transactions;

use App\Helpers\Enums\Transactions\EnumTransactionTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
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
            'account_number'            => ['required', 'string'],
            'agency_number'             => ['required', 'string'],
            'type'                      => ['required', 'string', Rule::enum(EnumTransactionTypes::class)],
            'amount'                    => ['required', 'numeric', 'min:1'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_number.required'           => 'Conta é obrigatório.',
            'account_number.string'             => 'O número da conta deve ser uma string.',
            'agency_number.required'            => 'Agência é obrigatório.',
            'agency_number.string'              => 'O número da agência deve ser uma string.',
            'type.required'                     => 'Tipo de depósito é obrigatório.',
            'type.string'                       => 'Tipo de depósito deve ser uma string.',
            'amount.required'                   => 'Quantidade é obrigatório.',
            'amount.numeric'                    => 'Quantidade deve ser um valor decimal.',
            'amount.min'                        => 'Quantidade deve ser pelo menos R$ 0,01.',
        ];
    }
}

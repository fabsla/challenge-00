<?php

namespace App\Helpers\Accounts;

use App\Models\Account;

class AgencyNumberGenerator
{
    public static function generateAccount()
    {
        /** melhorar isso aqui para um sequencial */
        while (true) {
            $agency_number = AgencyNumberGenerator::generateAgencyNumber();
            $account_number = AgencyNumberGenerator::generateAccountNumber();

            $existing_account = Account::where('agency_number', $agency_number)
                ->where('account_number', $account_number)
                ->exists();

            if (!$existing_account) {
                break;
            }
        }

        return [
            $agency_number,
            $account_number,
        ];
    }
        
    public static function generateAgencyNumber(): string
    {
        // Gera um número de agência aleatório com 4 dígitos
        return str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function generateAccountNumber(): string
    {
        // Gera um número de conta aleatório com 6 dígitos
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
}
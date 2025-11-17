<?php

namespace App\Strategies\Accounts\AccountGetter;

use App\DataTransferObjects\Transactions\TransactionDTO;
use App\DataTransferObjects\Transactions\TransferDTO;
use App\Models\Account;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class AccountGetterInternalStrategy implements AccountGetterInterface
{
    public function execute(TransactionDTO|TransferDTO $dto): Account
    {
        $account =
            Account::where('agency_number', $dto->agency_number)
                ->where('account_number', $dto->account_number)
                ->first();
        
        if (!$account) {
            throw new NotFoundResourceException('Conta não encontrada para o depósito interno.');
        }
            
        return $account;
    }
}
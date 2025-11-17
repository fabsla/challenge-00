<?php

namespace App\Strategies\Accounts\AccountGetter;

use App\DataTransferObjects\Transactions\TransactionDTO;
use App\DataTransferObjects\Transactions\TransferDTO;
use App\Models\Account;

interface AccountGetterInterface
{
    public function execute(TransactionDTO|TransferDTO $dto): Account;
}
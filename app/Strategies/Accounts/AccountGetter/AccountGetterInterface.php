<?php

namespace App\Strategies\Accounts\AccountGetter;

use App\DataTransferObjects\Transactions\TransactionDTO;
use App\Models\Account;

interface AccountGetterInterface
{
    public function execute(TransactionDTO $dto): Account;
}
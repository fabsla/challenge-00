<?php

namespace App\Helpers\Enums\Transactions;

enum EnumTransactionActions: string
{
    case DEPOSIT        = 'DEPOSIT';
    case WITHDRAWAL     = 'WITHDRAWAL';
    case TRANSFER       = 'TRANSFER';
}
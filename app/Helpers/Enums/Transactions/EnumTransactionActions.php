<?php

namespace App\Helpers\Enums\Transactions;

enum EnumTransactionActions: string
{
    case DEPOSIT        = 'DEPOSIT';
    case WITHDRAWAL     = 'WITHDRAWAL';
    case TRANSFER_FROM  = 'TRANSFER_FROM';
    case TRANSFER_TO    = 'TRANSFER_TO';
}
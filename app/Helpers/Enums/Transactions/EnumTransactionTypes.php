<?php

namespace App\Helpers\Enums\Transactions;

enum EnumTransactionTypes: string
{
    case INTERNO    = 'INTERNO';
    case PIX        = 'PIX';
}
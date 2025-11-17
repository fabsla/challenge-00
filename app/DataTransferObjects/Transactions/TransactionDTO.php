<?php

namespace App\DataTransferObjects\Transactions;

use App\Http\Requests\Transactions\TransactionRequest;
use App\Strategies\Accounts\AccountGetter\AccountGetterInterface;
use App\Strategies\Accounts\AccountGetter\AccountGetterInternalStrategy;

class TransactionDTO
{
    public function __construct(
        public string $agency_number,
        public string $account_number,
        public string $type,
        public int $amount,
        public ?AccountGetterInterface $accountGetterStrategy = null,
    ) {
        $this->accountGetterStrategy = self::getStrategy($this->type);
    }

    public static function getStrategy(string $type): AccountGetterInterface
    {
        return match ($type) {
            'INTERNO' => new AccountGetterInternalStrategy(),
            // 'PIX' => new AccountGetterPixStrategy(),
            default => throw new \InvalidArgumentException('Tipo de depósito inválido.'),
        };
    }

    public static function appRequest(TransactionRequest $request): TransactionDTO
    {
        return new self (
            agency_number:          $request->input('agency_number'),
            account_number:         $request->input('account_number'),
            amount:                 $request->input('amount'),
            type:                   $request->input('type'),
            accountGetterStrategy:  self::getStrategy($request->input('type')),
        );
    }
}
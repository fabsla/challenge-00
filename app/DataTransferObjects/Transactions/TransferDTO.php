<?php

namespace App\DataTransferObjects\Transactions;

use App\Http\Requests\Transactions\TransactionRequest;
use App\Http\Requests\Transactions\TransferRequest;
use App\Models\Account;
use App\Strategies\Accounts\AccountGetter\AccountGetterInterface;
use App\Strategies\Accounts\AccountGetter\AccountGetterInternalStrategy;

class TransferDTO
{
    public function __construct(
        public string $origin_account_id,
        public string $agency_number,
        public string $account_number,
        public string $type,
        public int $amount,
        public ?AccountGetterInterface $accountGetterStrategy = null,
    ) {
        $this->accountGetterStrategy = self::getStrategy($this->type);
    }

    private static function getStrategy(string $type): AccountGetterInterface
    {
        return match ($type) {
            'INTERNO' => new AccountGetterInternalStrategy(),
            // 'PIX' => new AccountGetterPixStrategy(),
            default => throw new \InvalidArgumentException('Tipo de operação inválida.'),
        };
    }

    public function getDestinyAccount(): Account
    {
        return $this->accountGetterStrategy->execute($this);
    }

    public function getOriginAccount(): Account
    {
        return Account::findOrFail($this->origin_account_id);
    }

    public static function appRequest(TransferRequest $request): TransferDTO
    {
        return new self (
            origin_account_id:      $request->input('origin_account_id'),
            agency_number:          $request->input('agency_number'),
            account_number:         $request->input('account_number'),
            amount:                 $request->input('amount'),
            type:                   $request->input('type'),
            accountGetterStrategy:  self::getStrategy($request->input('type')),
        );
    }
}
<?php

namespace App\Services\Transactions;

use App\DataTransferObjects\Transactions\TransactionDTO;
use App\Helpers\Enums\Transactions\EnumTransactionActions;
use App\Models\TransactionHistory;
use App\Strategies\Accounts\AccountGetter\AccountGetterInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class TransactionService
{
    /**
     * Realizar depósito em uma conta
     * 
     * @param TransactionDTO $dto
     * @param AccountGetterInterface $strategy
     * @return TransactionHistory $history
     *
     */
    public function deposit(TransactionDTO $dto): TransactionHistory
    {
        if ($dto->amount <= 0) {
            throw new InvalidArgumentException('O valor do depósito deve ser maior que zero.');
        }

        $account = $dto->accountGetterStrategy->execute($dto);

        DB::beginTransaction();
        try {
            $account->balance += $dto->amount;
            $account->save();

            /** registrar histórico */
            $history = TransactionHistory::create([
                'account_id' => $account->id,
                'user_id'    => Auth::id(),
                'type'       => EnumTransactionActions::DEPOSIT->value,
                'amount'     => $dto->amount,
                'description'=> 'Depósito realizado',
            ]);

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException('Erro ao processar o depósito: ' . $e->getMessage(), previous: $e);
        }

        return $history->load(['user', 'account']);
    }
}

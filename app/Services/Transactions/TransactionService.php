<?php

namespace App\Services\Transactions;

use App\DataTransferObjects\Transactions\TransactionDTO;
use App\DataTransferObjects\Transactions\TransferDTO;
use App\Helpers\Enums\Transactions\EnumTransactionActions;
use App\Models\Account;
use App\Models\TransactionHistory;
use App\Services\Authorization\AuthorizationService;
use App\Services\Notification\NotificationService;
use App\Strategies\Accounts\AccountGetter\AccountGetterInterface;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class TransactionService
{
    public function __construct(
        private readonly AuthorizationService $authorizationService,
        private readonly NotificationService $notificationService,
    ) {}
    /**
     * Realizar depósito em uma conta
     * 
     * @param TransactionDTO $dto
     * @param AccountGetterInterface $strategy
     * @return TransactionHistory $history
     *
     */
    public function deposit(Account $account, TransactionDTO $dto): TransactionHistory
    {
        if ($dto->amount <= 0) {
            throw new InvalidArgumentException('O valor do depósito deve ser maior que zero.');
        }

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

    /**
     * Realizar saque em uma conta
     * 
     * @param TransactionDTO $dto
     * @param AccountGetterInterface $strategy
     * @return TransactionHistory $history
     *
     */
    public function withdrawal(Account $account, TransactionDTO $dto): TransactionHistory
    {
        if ($dto->amount <= 0) {
            throw new InvalidArgumentException('O valor do saque deve ser maior que zero.');
        }

        /** necessario ter dinheiro na conta para realizar o saque */
        if ($account->balance < $dto->amount) {
            throw new UnprocessableEntityHttpException('Saldo insuficiente para realizar o saque.', code: 422);
        }

        DB::beginTransaction();
        try {
            $account->balance -= $dto->amount;
            $account->save();

            /** registrar histórico */
            $history = TransactionHistory::create([
                'account_id' => $account->id,
                'user_id'    => Auth::id(),
                'type'       => EnumTransactionActions::WITHDRAWAL->value,
                'amount'     => $dto->amount,
                'description'=> 'Saque realizado',
            ]);

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException('Erro ao processar o saque: ' . $e->getMessage(), previous: $e);
        }

        return $history->load(['user', 'account']);
    }

    public function transfer(Account $origin_account, Account $destiny_account, TransferDTO $dto): array
    {
        if ($dto->amount <= 0) {
            throw new InvalidArgumentException('O valor da transferência deve ser maior que zero.');
        }

        /** necessario ter dinheiro na conta para realizar a transferencia */
        if ($origin_account->balance < $dto->amount) {
            throw new UnprocessableEntityHttpException('Saldo insuficiente para realizar a transferência.', code: 422);
        }

        /** Autorizar a transferência através da API externa */
        $this->authorizationService->authorize();

        DB::beginTransaction();
        try {
            $origin_account->balance -= $dto->amount;
            $origin_account->save();

            $destiny_account->balance += $dto->amount;
            $destiny_account->save();

            /** registrar histórico saque */
            TransactionHistory::create([
                'account_id' => $origin_account->id,
                'user_id'    => Auth::id(),
                'type'       => EnumTransactionActions::TRANSFER_FROM->value,
                'amount'     => $dto->amount,
                'description'=> 'Transferência realizada para a conta ' . $destiny_account->account_number,
            ]);

            /** registrar histórico deposito */
            TransactionHistory::create([
                'account_id' => $destiny_account->id,
                'user_id'    => Auth::id(),
                'type'       => EnumTransactionActions::TRANSFER_TO->value,
                'amount'     => $dto->amount,
                'description'=> 'Transferência realizada da conta ' . $origin_account->account_number,
            ]);
            
            /** notificacao */
            $notification_success = $this->notificationService->notifyTransfer(
                $destiny_account->user,
                $dto->amount,
            );

            if (!$notification_success) {
                throw new RuntimeException('Falha ao enviar notificação de transferência.');
            }

            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw new RuntimeException('Erro ao processar a transferência: ' . $e->getMessage(), previous: $e);
        }

        return [
            'value' => $dto->amount,
            'payer' => $origin_account->user_id,
            'payee' => $destiny_account->user_id,
        ];
    }
}

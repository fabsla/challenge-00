<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\Transactions\TransactionDTO;
use App\Http\Requests\Transactions\TransactionRequest;
use App\Services\Transactions\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TransactionsController extends Controller
{
    public function __construct(
        private readonly TransactionService $depositService,
    ) {}

    public function deposit(TransactionRequest $request): JsonResponse
    {   
        $dto = TransactionDTO::appRequest($request);

        $transaction_history = $this->depositService->deposit(
            account: $dto->getAccount(),
            dto: $dto,
        );

        return response()->json([
            'data' => [
                'transaction_id'    => $transaction_history->id,
                'user_id'           => $transaction_history->user_id,
                'user_name'         => $transaction_history->user->name,
                'amount'            => $transaction_history->amount,
                'type'              => $transaction_history->type,
                'created_at'        => $transaction_history->created_at,
                'account' => [
                    'agency_number'     => $transaction_history->account->agency_number,
                    'account_number'    => $transaction_history->account->account_number,
                ],
            ],
            'message' => 'Depósito realizado com sucesso.',
        ], Response::HTTP_CREATED);
    }

    public function withdrawal(TransactionRequest $request): JsonResponse
    {   
        $dto = TransactionDTO::appRequest($request);

        $transaction_history = $this->depositService->withdrawal(
            account: $dto->getAccount(),
            dto: $dto,
        );

        return response()->json([
            'data' => [
                'transaction_id'    => $transaction_history->id,
                'user_id'           => $transaction_history->user_id,
                'user_name'         => $transaction_history->user->name,
                'amount'            => $transaction_history->amount,
                'type'              => $transaction_history->type,
                'created_at'        => $transaction_history->created_at,
                'account' => [
                    'agency_number'     => $transaction_history->account->agency_number,
                    'account_number'    => $transaction_history->account->account_number,
                ],
            ],
            'message' => 'Depósito realizado com sucesso.',
        ], Response::HTTP_CREATED);
    }

}
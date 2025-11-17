<?php

namespace App\Http\Controllers\Transactions;

use App\Http\Controllers\Controller;
use App\DataTransferObjects\Transactions\TransactionDTO;
use App\DataTransferObjects\Transactions\TransferDTO;
use App\Http\Requests\Transactions\TransactionRequest;
use App\Http\Requests\Transactions\TransferRequest;
use App\Services\Transactions\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

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
            'message' => 'Saque realizado com sucesso.',
        ], Response::HTTP_CREATED);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        /** lojistas não podem realizar transferências, apenas receber */
        if (Auth::user()->lojista) {
            return response()->json([
                'message' => 'Lojistas não podem realizar saques.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $dto = TransferDTO::appRequest($request);

        $transaction_history = $this->depositService->transfer(
            origin_account: $dto->getOriginAccount(),
            destiny_account: $dto->getDestinyAccount(),
            dto: $dto,
        );

        return response()->json([
            'data' => [
                'origin_transaction' => [
                    'transaction_id'    => $transaction_history['origin_history']->id,
                    'user_id'           => $transaction_history['origin_history']->user_id,
                    'user_name'         => $transaction_history['origin_history']->user->name,
                    'amount'            => $transaction_history['origin_history']->amount,
                    'type'              => $transaction_history['origin_history']->type,
                    'created_at'        => $transaction_history['origin_history']->created_at,
                    'account' => [
                        'agency_number'     => $transaction_history['origin_history']->account->agency_number,
                        'account_number'    => $transaction_history['origin_history']->account->account_number,
                    ],
                ],
                'destiny_transaction' => [
                    'transaction_id'    => $transaction_history['destiny_history']->id,
                    'user_id'           => $transaction_history['destiny_history']->user_id,
                    'user_name'         => $transaction_history['destiny_history']->user->name,
                    'amount'            => $transaction_history['destiny_history']->amount,
                    'type'              => $transaction_history['destiny_history']->type,
                    'created_at'        => $transaction_history['destiny_history']->created_at,
                    'account' => [
                        'agency_number'     => $transaction_history['destiny_history']->account->agency_number,
                        'account_number'    => $transaction_history['destiny_history']->account->account_number,
                    ],
                ]
            ],
            'message' => 'Transferência realizada com sucesso.',
        ], Response::HTTP_CREATED);
    }

}
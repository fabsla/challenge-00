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
        $account = $dto->getAccount();

        $transaction_history = $this->depositService->deposit(
            account: $account,
            dto: $dto,
        );

        return response()->json([
            'data' => [
                'transaction_id'    => $transaction_history['id'],
                'user_id'           => $transaction_history['user_id'],
                'amount'            => $transaction_history['amount'],
                'type'              => $transaction_history['type'],
                'created_at'        => $transaction_history['created_at'],
                'account' => [
                    'agency_number'     => $account->agency_number,
                    'account_number'    => $account->account_number,
                ],
            ],
            'message' => 'Depósito realizado com sucesso.',
        ], Response::HTTP_CREATED);
    }

    public function withdrawal(TransactionRequest $request): JsonResponse
    { 
        $dto = TransactionDTO::appRequest($request);
        $account = $dto->getAccount();

        $transaction_history = $this->depositService->withdrawal(
            account: $account,
            dto: $dto,
        );

        return response()->json([
            'data' => [
                'transaction_id'    => $transaction_history['id'],
                'user_id'           => $transaction_history['user_id'],
                'amount'            => $transaction_history['amount'],
                'type'              => $transaction_history['type'],
                'created_at'        => $transaction_history['created_at'],
                'account' => [
                    'agency_number'     => $account->agency_number,
                    'account_number'    => $account->account_number,
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
        $origin_account = $dto->getOriginAccount();
        $destiny_account = $dto->getDestinyAccount();

        $transaction_response = $this->depositService->transfer(
            origin_account: $origin_account,
            destiny_account: $destiny_account,
            dto: $dto,
        );

        return response()->json($transaction_response, Response::HTTP_CREATED);
    }

}
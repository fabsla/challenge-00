<?php

use App\DataTransferObjects\Transactions\TransactionDTO;
use App\Models\Account;
use App\Models\TransactionHistory;
use App\Models\User;
use App\Services\Transactions\TransactionService;
use Illuminate\Support\Facades\Auth;

$depositService = app(TransactionService::class);

test('deposit service deve criar uma transação com sucesso', function () use ($depositService) {
    $user = User::factory()->create();
    Auth::login($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $initialBalance = $account->balance;
    $depositAmount = 5000;

    $dto = new TransactionDTO(
        agency_number: $account->agency_number,
        account_number: $account->account_number,
        type: 'INTERNO',
        amount: $depositAmount,
    );

    $history = $depositService->deposit($dto->getAccount(), $dto);

    expect($history)->toBeInstanceOf(TransactionHistory::class);
    expect($history->type)->toBe('DEPOSIT');
    expect($history->amount)->toBe($depositAmount);
    expect($history->account_id)->toBe($account->id);
    expect($history->user_id)->toBe($user->id);

    $account->refresh();
    expect($account->balance)->toBe($initialBalance + $depositAmount);
});

test('deposit service deve lançar erro para valor menor ou igual a zero', function () use ($depositService) {
    $user = User::factory()->create();
    Auth::login($user);

    $account = Account::factory()->create(['user_id' => $user->id]);

    $dto = new TransactionDTO(
        agency_number: $account->agency_number,
        account_number: $account->account_number,
        type: 'INTERNO',
        amount: 0,
    );

    expect(fn () => $depositService->deposit($dto->getAccount(), $dto))
        ->toThrow(InvalidArgumentException::class, 'O valor do depósito deve ser maior que zero.');
});

test('deposit service deve lançar erro quando conta não existe', function () use ($depositService) {
    $user = User::factory()->create();
    Auth::login($user);

    // cria DTO apontando para conta inexistente
    $dto = new TransactionDTO(
        agency_number: '9999',
        account_number: '99999999',
        type: 'INTERNO',
        amount: 1000,
    );

    expect(fn () => $depositService->deposit($dto->getAccount(), $dto))->toThrow(Exception::class);
});

test('deposit service deve registrar histórico de transação', function () use ($depositService) {
    $user = User::factory()->create();
    Auth::login($user);

    $account = Account::factory()->create(['user_id' => $user->id]);
    $depositAmount = 2500;

    $dto = new TransactionDTO(
        agency_number: $account->agency_number,
        account_number: $account->account_number,
        type: 'INTERNO',
        amount: $depositAmount,
    );

    $history = $depositService->deposit($dto->getAccount(), $dto);

    expect(TransactionHistory::where('id', $history->id)->exists())->toBeTrue();
    expect($history->description)->toBe('Depósito realizado');
});

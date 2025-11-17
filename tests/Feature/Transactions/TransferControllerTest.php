<?php

use App\Models\Account;
use App\Models\User;

test('transfer controller deve realizar transferência com sucesso', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create([
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 2000,
        ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'origin_transaction' => [
                'transaction_id',
                'user_id',
                'amount',
                'type',
                'created_at',
            ],
            'destiny_transaction' => [
                'transaction_id',
                'user_id',
                'amount',
                'type',
                'created_at',
            ],
        ],
        'message',
    ]);
});

test('transfer controller deve validar tipo de transferência inválido', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create(['user_id' => $user->id, 'balance' => 10000]);
    $destinyAccount = Account::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INVALIDO',
            'amount' => 2000,
        ]);

    $response->assertUnprocessable();
});

test('transfer controller deve validar campos obrigatórios', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['origin_account_id', 'agency_number', 'account_number', 'type', 'amount']);
});

test('transfer controller deve validar valor mínimo', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create(['user_id' => $user->id]);
    $destinyAccount = Account::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 0,
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('amount');
});

test('transfer controller retorna 401 sem autenticação', function () {
    $response = $this->postJson('/api/transactions/transfer', [
        'origin_account_id' => 1,
        'agency_number' => '0001',
        'account_number' => '12345678',
        'type' => 'INTERNO',
        'amount' => 2000,
    ]);

    $response->assertUnauthorized();
});

test('transfer controller deve validar conta de origem não encontrada', function () {
    $user = User::factory()->create();
    $destinyAccount = Account::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => 99999,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 2000,
        ]);

    $response->assertUnprocessable();
});

test('transfer controller deve atualizar saldo das contas', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create([
        'balance' => 5000,
    ]);

    $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 3000,
        ]);

    $originAccount->refresh();
    $destinyAccount->refresh();
    
    expect($originAccount->balance)->toBe(7000);
    expect($destinyAccount->balance)->toBe(8000);
});

test('transfer controller deve retornar dados corretos da transação', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create([
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 1500,
        ]);

    $response->assertSuccessful();
    expect($response->json('data.origin_transaction.user_id'))->toBe($user->id);
    expect($response->json('data.origin_transaction.amount'))->toBe(1500);
    expect($response->json('data.origin_transaction.type'))->toBe('TRANSFER_FROM');
    expect($response->json('data.destiny_transaction.amount'))->toBe(1500);
    expect($response->json('data.destiny_transaction.type'))->toBe('TRANSFER_TO');
});

test('transfer controller deve rejeitar transferência com saldo insuficiente', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 500,
    ]);
    $destinyAccount = Account::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 1000,
        ]);

    $response->assertUnprocessable();
});

test('transfer controller deve rejeitar valor negativo', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => -500,
        ]);

    $response->assertUnprocessable();
});

test('transfer controller deve criar registros de histórico para ambas as contas', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create([
        'balance' => 5000,
    ]);

    $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 2500,
        ]);

    $originHistory = $originAccount->history()->where('type', 'TRANSFER_FROM')->first();
    $destinyHistory = $destinyAccount->history()->where('type', 'TRANSFER_TO')->first();
    
    expect($originHistory)->not->toBeNull();
    expect($destinyHistory)->not->toBeNull();
    expect($originHistory->user_id)->toBe($user->id);
    expect($originHistory->amount)->toBe(2500);
    expect($destinyHistory->amount)->toBe(2500);
});

test('transfer controller deve retornar dados de ambas as contas na resposta', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create([
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 2000,
        ]);

    $response->assertSuccessful();
    expect($response->json('data.origin_transaction.account.agency_number'))->toBe($originAccount->agency_number);
    expect($response->json('data.origin_transaction.account.account_number'))->toBe($originAccount->account_number);
    expect($response->json('data.destiny_transaction.account.agency_number'))->toBe($destinyAccount->agency_number);
    expect($response->json('data.destiny_transaction.account.account_number'))->toBe($destinyAccount->account_number);
});

test('transfer controller deve retornar mensagem de sucesso', function () {
    $user = User::factory()->create();
    $originAccount = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 10000,
    ]);
    $destinyAccount = Account::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/transfer', [
            'origin_account_id' => $originAccount->id,
            'agency_number' => $destinyAccount->agency_number,
            'account_number' => $destinyAccount->account_number,
            'type' => 'INTERNO',
            'amount' => 1000,
        ]);

    $response->assertSuccessful();
    expect($response->json('message'))->toContain('Transferência');
});

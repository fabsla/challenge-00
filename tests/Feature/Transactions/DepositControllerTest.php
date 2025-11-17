<?php

use App\Models\Account;
use App\Models\User;

test('deposit controller deve criar depósito com sucesso', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 1000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/deposit', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 5000,
        ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'transaction_id',
            'user_id',
            'amount',
            'type',
            'created_at',
        ],
        'message',
    ]);
    $response->assertJsonPath('data.amount', 5000);
    $response->assertJsonPath('data.type', 'DEPOSIT');
    $response->assertJsonPath('message', 'Depósito realizado com sucesso.');
});

test('deposit controller deve validar tipo de depósito inválido', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/deposit', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INVALIDO',
            'amount' => 5000,
        ]);

    $response->assertUnprocessable();
});

test('deposit controller deve validar campos obrigatórios', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/deposit', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['agency_number', 'account_number', 'type', 'amount']);
});

test('deposit controller deve validar valor mínimo', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/deposit', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 0,
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('amount');
});

test('deposit controller retorna 401 sem autenticação', function () {
    $response = $this->postJson('/api/transactions/deposit', [
        'agency_number' => '0001',
        'account_number' => '12345678',
        'type' => 'INTERNO',
        'amount' => 5000,
    ]);

    $response->assertUnauthorized();
});

test('deposit controller deve atualizar saldo da conta', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 1000,
    ]);

    $this->actingAs($user)
        ->postJson('/api/transactions/deposit', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 5000,
        ]);

    $account->refresh();
    expect($account->balance)->toBe(6000);
});

test('deposit controller deve retornar dados corretos da transação', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/deposit', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 3500,
        ]);

    $response->assertSuccessful();
    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.amount'))->toBe(3500);
});

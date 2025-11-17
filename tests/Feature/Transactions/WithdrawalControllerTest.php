<?php

use App\Models\Account;
use App\Models\User;

test('withdrawal controller deve realizar saque com sucesso', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 1000,
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
    $response->assertJsonPath('data.amount', 1000);
    $response->assertJsonPath('data.type', 'WITHDRAWAL');
});

test('withdrawal controller deve validar tipo de transação inválido', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INVALIDO',
            'amount' => 1000,
        ]);

    $response->assertUnprocessable();
});

test('withdrawal controller deve validar campos obrigatórios', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', []);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['agency_number', 'account_number', 'type', 'amount']);
});

test('withdrawal controller deve validar valor mínimo', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 0,
        ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('amount');
});

test('withdrawal controller retorna 401 sem autenticação', function () {
    $response = $this->postJson('/api/transactions/withdrawal', [
        'agency_number' => '0001',
        'account_number' => '12345678',
        'type' => 'INTERNO',
        'amount' => 1000,
    ]);

    $response->assertUnauthorized();
});

test('withdrawal controller deve atualizar saldo da conta', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000,
    ]);

    $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 2000,
        ]);

    $account->refresh();
    expect($account->balance)->toBe(3000);
});

test('withdrawal controller deve retornar dados corretos da transação', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 1500,
        ]);

    $response->assertSuccessful();
    expect($response->json('data.user_id'))->toBe($user->id);
    expect($response->json('data.amount'))->toBe(1500);
});

test('withdrawal controller deve rejeitar saque com saldo insuficiente', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 500,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 1000,
        ]);

    $response->assertUnprocessable();
});

test('withdrawal controller deve rejeitar valor negativo', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000,
    ]);

    $response = $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => -500,
        ]);

    $response->assertUnprocessable();
});

test('withdrawal controller deve criar registro de histórico de transação', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create([
        'user_id' => $user->id,
        'balance' => 5000,
    ]);

    $this->actingAs($user)
        ->postJson('/api/transactions/withdrawal', [
            'agency_number' => $account->agency_number,
            'account_number' => $account->account_number,
            'type' => 'INTERNO',
            'amount' => 800,
        ]);

    $history = $account->history()->where('type', 'WITHDRAWAL')->first();
    expect($history)->not->toBeNull();
    expect($history->user_id)->toBe($user->id);
    expect($history->amount)->toBe(800);
});

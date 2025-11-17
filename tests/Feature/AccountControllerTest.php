<?php

use App\Models\User;
use App\Models\Account;

test('list accounts - deve listar as contas do usuário autenticado', function () {
    $user = User::factory()->create();
    $accounts = Account::factory(3)->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->getJson('/api/accounts');

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'message',
        'data' => [
            '*' => [
                'id',
                'user_id',
                'agency_number',
                'account_number',
                'balance',
                'created_at',
                'updated_at',
            ],
        ],
    ]);
    $response->assertJsonCount(3, 'data');
});

test('list accounts - sem autenticação deve retornar 401', function () {
    $response = $this->getJson('/api/accounts');

    $response->assertUnauthorized();
});

test('store account - deve criar uma nova conta para o usuário', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/accounts', [
        'user_id' => $user->id,
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'message',
        'data' => [
            'id',
            'user_id',
            'agency_number',
            'account_number',
            'balance',
            'created_at',
            'updated_at',
        ],
    ]);
    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
    ]);
});

test('store account - user_id inválido deve retornar validação', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/api/accounts', [
        'user_id' => 9999,
    ]);

    $response->assertUnprocessable();
});

test('store account - sem autenticação deve retornar 401', function () {
    $response = $this->postJson('/api/accounts', [
        'user_id' => 1,
    ]);

    $response->assertUnauthorized();
});

test('destroy account - deve deletar uma conta existente', function () {
    $user = User::factory()->create();
    $account = Account::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->deleteJson('/api/accounts', [
        'agency_number' => $account->agency_number,
        'account_number' => $account->account_number,
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'message' => 'Conta deletada com sucesso',
    ]);
    $this->assertDatabaseMissing('accounts', [
        'id' => $account->id,
    ]);
});

test('destroy account - conta não encontrada deve retornar 404', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->deleteJson('/api/accounts', [
        'agency_number' => 9999,
        'account_number' => 9999,
    ]);

    $response->assertNotFound();
});

test('destroy account - sem autenticação deve retornar 401', function () {
    $response = $this->deleteJson('/api/accounts', [
        'agency_number' => 1234,
        'account_number' => 5678,
    ]);

    $response->assertUnauthorized();
});

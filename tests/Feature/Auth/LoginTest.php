<?php

use App\Models\User;

it('authenticates a user with valid email and password', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'cpf_cnpj' => '12345678901',
        'lojista' => false,
    ]);

    $response = $this->postJson('/api/login', [
        'login' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            'user' => ['id', 'name', 'email', 'cpf_cnpj', 'lojista'],
            'token',
        ],
        'message',
    ]);
});

it('authenticates a user with valid cpf_cnpj and password', function () {
    $user = User::factory()->create([
        'email' => 'cpf-user@example.com',
        'password' => bcrypt('password123'),
        'cpf_cnpj' => '12345678901',
        'lojista' => false,
    ]);

    $response = $this->postJson('/api/login', [
        'login' => '123.456.789-01',
        'password' => 'password123',
    ]);

    $response->assertSuccessful();
    $response->assertJsonStructure([
        'data' => [
            'user' => ['id', 'name', 'email', 'cpf_cnpj', 'lojista'],
            'token',
        ],
        'message',
    ]);
    $response->assertJsonPath('data.user.cpf_cnpj', '123.456.789-01');
});

it('returns unauthorized when credentials are invalid', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password123'),
        'cpf_cnpj' => '12345678901',
        'lojista' => false,
    ]);

    $response = $this->postJson('/api/login', [
        'login' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertUnauthorized();
    $response->assertJsonStructure(['error']);
});

it('returns validation error when login is missing', function () {
    $response = $this->postJson('/api/login', [
        'password' => 'password123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('login');
});

it('returns validation error when password is missing', function () {
    $response = $this->postJson('/api/login', [
        'login' => 'test@example.com',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('password');
});

it('returns validation error for non-existent email', function () {
    $response = $this->postJson('/api/login', [
        'login' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('login');
});

it('returns validation error for invalid cpf_cnpj format', function () {
    $response = $this->postJson('/api/login', [
        'login' => '123',
        'password' => 'password123',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors('login');
});

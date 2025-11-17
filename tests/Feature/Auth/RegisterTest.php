<?php

use App\Models\User;

describe('User Registration', function () {
    describe('Pessoa Física (CPF)', function () {
        it('creates a user with cpf successfully', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'pessoa@fisica.com',
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '12345678901',
                'name' => 'João Silva',
            ]);

            $response->assertCreated();
            $response->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'cpf_cnpj_formatado',
                    'lojista',
                ],
                'message',
            ]);

            expect($response['data']['name'])->toBe('João Silva');
            expect($response['data']['email'])->toBe('pessoa@fisica.com');
            expect($response['data']['lojista'])->toBeFalse();

            $this->assertDatabaseHas('users', [
                'email' => 'pessoa@fisica.com',
                'name' => 'João Silva',
                'cpf_cnpj' => '12345678901',
                'lojista' => false,
            ]);
        });

        it('validates cpf format', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'pessoa@fisica.com',
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '123',
                'name' => 'João Silva',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['cpf_cnpj']);
        });

        it('requires name for pessoa física', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'pessoa@fisica.com',
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '12345678901',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['name']);
        });
    });

    describe('Pessoa Jurídica (CNPJ)', function () {
        it('creates a user with cnpj and sets lojista to true', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'empresa@jursdica.com',
                'password' => 'password123',
                'lojista' => true,
                'cpf_cnpj' => '12345678901234',
                'name' => 'Empresa LTDA',
            ]);

            $response->assertCreated();
            $response->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'cpf_cnpj_formatado',
                    'lojista',
                ],
                'message',
            ]);

            expect($response['data']['name'])->toBe('Empresa LTDA');
            expect($response['data']['email'])->toBe('empresa@jursdica.com');
            expect($response['data']['lojista'])->toBeTrue();

            $this->assertDatabaseHas('users', [
                'email' => 'empresa@jursdica.com',
                'name' => 'Empresa LTDA',
                'cpf_cnpj' => '12345678901234',
                'lojista' => true,
            ]);
        });

        it('validates cnpj format', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'empresa@juridica.com',
                'password' => 'password123',
                'lojista' => true,
                'cpf_cnpj' => '12345',
                'name' => 'Empresa LTDA',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['cpf_cnpj']);
        });

        it('sets lojista to true for cnpj type automatically', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'empresa@juridica.com',
                'password' => 'password123',
                'lojista' => true,
                'cpf_cnpj' => '12345678901234',
                'name' => 'Empresa LTDA',
            ]);

            $response->assertCreated();
            expect($response['data']['lojista'])->toBeTrue();

            $this->assertDatabaseHas('users', [
                'email' => 'empresa@juridica.com',
                'lojista' => true,
            ]);
        });

        it('requires razão social (name) for pessoa jurídica', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'empresa@juridica.com',
                'password' => 'password123',
                'lojista' => true,
                'cpf_cnpj' => '12345678901234',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['name']);
        });
    });

    describe('Validation Rules', function () {
        it('requires email', function () {
            $response = $this->postJson('/api/register', [
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '12345678901',
                'name' => 'João Silva',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['email']);
        });

        it('validates unique email', function () {
            User::factory()->create(['email' => 'existing@email.com']);

            $response = $this->postJson('/api/register', [
                'email' => 'existing@email.com',
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '12345678901',
                'name' => 'João Silva',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['email']);
        });

        it('validates unique cpf_cnpj', function () {
            User::factory()->create(['cpf_cnpj' => '12345678901']);

            $response = $this->postJson('/api/register', [
                'email' => 'novo@email.com',
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '12345678901',
                'name' => 'João Silva',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['cpf_cnpj']);
        });

        it('requires password with minimum 6 characters', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'pessoa@fisica.com',
                'password' => '123',
                'lojista' => false,
                'cpf_cnpj' => '12345678901',
                'name' => 'João Silva',
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['password']);
        });

        it('accepts cpf_cnpj with formatting', function () {
            $response = $this->postJson('/api/register', [
                'email' => 'pessoa@fisica.com',
                'password' => 'password123',
                'lojista' => false,
                'cpf_cnpj' => '123.456.789-01',
                'name' => 'João Silva',
            ]);

            $response->assertCreated();

            $this->assertDatabaseHas('users', [
                'cpf_cnpj' => '12345678901',
            ]);
        });
    });
});


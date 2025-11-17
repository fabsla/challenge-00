<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class AuthenticationService
{
    /**
     * Autenticar um usuário com login (email ou cpf_cnpj) e senha.
     *
     * Detecta automaticamente se o login é email ou cpf_cnpj.
     *
     * @param  string  $login  Email ou CPF/CNPJ (sem máscara)
     * @return array{'user': User, 'token': string}
     *
     * @throws InvalidArgumentException
     */
    public function authenticate(string $login, string $password): array
    {
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);

        $user = $isEmail
            ? User::query()->where('email', $login)->first()
            : User::query()->where('cpf_cnpj', preg_replace('/\D+/', '', $login))->first();
        
        if (!$user || !Hash::check($password, $user->password)) {
            throw new InvalidArgumentException('Credenciais inválidas.');
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Revogar todos os tokens de um usuário.
     */
    public function revokeTokens(User $user): void
    {
        $user->tokens()->delete();
    }
}

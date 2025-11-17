<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateUserRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
    ) {}

    /**
     * Criar novo usuário (pessoa física ou jurídica).
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf_cnpj' => $validated['cpf_cnpj'],
            'password' => $validated['password'],
            'lojista' => $validated['lojista'],
        ]);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cpf_cnpj_formatado' => $user->cpf_cnpj_formatado,
                'lojista' => $user->lojista,
            ],
            'message' => 'Usuário criado com sucesso.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Efetuar login e retornar um token JWT.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->validated();
            $authentication_data = $this->authenticationService->authenticate(
                $credentials['login'],
                $credentials['password'],
            );

            return response()->json([
                'data' => [
                    'token' => $authentication_data['token'],
                ],
                'message' => 'Login realizado com sucesso.',
            ], Response::HTTP_OK);
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'error' => $exception->getMessage(),
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
}

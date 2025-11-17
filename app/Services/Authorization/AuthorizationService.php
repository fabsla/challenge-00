<?php

namespace App\Services\Authorization;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class AuthorizationService
{
    private ?string $authorizationUrl = null;
    
    public function __construct()
    {
    }

    private function getAuthorizationUrl(): string
    {
        if ($this->authorizationUrl === null) {
            $this->authorizationUrl = config('services.transfer_authorization_url', 'https://util.devi.tools/api/v2/authorize');
        }
        return $this->authorizationUrl;
    }
    /**
     * Autoriza uma transação através da API externa.
     *
     * @param int $originAccountId
     * @param int $destinyAccountId
     * @param int $amount
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function authorize(): bool
    {
        try {
            $response = Http::timeout(5)
                ->connectTimeout(5)
                ->get($this->getAuthorizationUrl());

            if (!$response->successful()) {
                $message = $response->json('message');
                throw new InvalidArgumentException(
                    $message ?? 'Transação não autorizada pelo verificador.'
                );
            }

            return true;
        } catch (\InvalidArgumentException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            throw new InvalidArgumentException(
                'Erro ao verificar autorização: ' . $exception->getMessage(),
                previous: $exception,
            );
        }
    }
}

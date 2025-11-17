<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    private ?string $notificationUrl = null;
    private int $timeout = 3;
    
    public function __construct()
    {
    }

    private function getNotificationUrl(): ?string
    {
        if ($this->notificationUrl === null) {
            $this->notificationUrl = config('services.notification_service_url', 'https://util.devi.tools/api/v1/notify');
        }
        return $this->notificationUrl;
    }

    /**
     * Envia notificação de transferência realizada.
     *
     * @param int $originAccountId
     * @param int $destinyAccountId
     * @param int $amount
     * @return bool
     */
    public function notifyTransfer(User $user, int $value): bool
    {
        $url = $this->getNotificationUrl();
        if ($url === null) {
            Log::warning('URL de notificação não está configurada.');
            return false;
        }

        $decimal_value = $value / 100;

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout($this->timeout)
                ->post($url, [
                    'email' => $user->email,
                    'value' => $decimal_value,
                ]);

            if ($response->successful()) {
                Log::info('Notificação de transferência enviada com sucesso', [
                    'email' => $user->email,
                    'value' => $decimal_value,
                ]);
                return true;
            }

            Log::warning('Falha ao enviar notificação de transferência', [
                'status' => $response->status(),
                'email' => $user->email,
                'value' => $decimal_value,
            ]);

            return false;

        } catch (\Exception $exception) {
            Log::warning('Erro ao enviar notificação de transferência', [
                'message' => $exception->getMessage(),
                'email' => $user->email,
                'value' => $decimal_value,
            ]);

            return false;
        }
    }
}

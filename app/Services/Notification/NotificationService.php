<?php

namespace App\Services\Notification;

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
    public function notifyTransfer(int $originAccountId, int $destinyAccountId, int $amount): bool
    {
        $url = $this->getNotificationUrl();
        if ($url === null) {
            Log::warning('URL de notificação não está configurada.');
            return false;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout($this->timeout)
                ->post($url, [
                    'origin_account_id' => $originAccountId,
                    'destiny_account_id' => $destinyAccountId,
                    'amount' => $amount,
                    'type' => 'transfer',
                ]);

            if ($response->successful()) {
                Log::info('Notificação de transferência enviada com sucesso', [
                    'origin_account_id' => $originAccountId,
                    'destiny_account_id' => $destinyAccountId,
                    'amount' => $amount,
                ]);
                return true;
            }

            Log::warning('Falha ao enviar notificação de transferência', [
                'status' => $response->status(),
                'origin_account_id' => $originAccountId,
                'destiny_account_id' => $destinyAccountId,
                'amount' => $amount,
            ]);

            return false;

        } catch (\Exception $exception) {
            Log::warning('Erro ao enviar notificação de transferência', [
                'message' => $exception->getMessage(),
                'origin_account_id' => $originAccountId,
                'destiny_account_id' => $destinyAccountId,
                'amount' => $amount,
            ]);

            return false;
        }
    }
}

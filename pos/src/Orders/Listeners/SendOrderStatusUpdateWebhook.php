<?php

declare(strict_types=1);

namespace Tixel\Orders\Listeners;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\Factory;
use Illuminate\Log\LogManager;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Tixel\Orders\Events\UpdatedOrderStatus;

class SendOrderStatusUpdateWebhook implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 10;

    public function __construct(
        private readonly Repository $repository,
        private readonly Factory $httpFactory,
        private readonly LogManager $logManager
    ) {}

    /**
     * Retry the job for an hour in case it fails.
     * This is very standard, the webhook should be disabled until resolution of the webhook consumer endpoint but this doesn't need to be as advanced for a demo.
     */
    public function retryUntil(): Carbon
    {
        return now()->addHour();
    }

    public function handle(UpdatedOrderStatus $orderEvent): void
    {
        $this->logManager->debug('inside handler');
        $this->sendWebhook($orderEvent);
    }

    private function sendWebhook(UpdatedOrderStatus $orderEvent): void
    {
        $webhookUrl = $this->repository->get('order.webhook.url');

        // Path to your private key (used for signing)
        $privateKeyPath = storage_path('keys/sender_private_key.pem');
        // Path to recipient's public key (used for encryption)
        $recipientPublicKeyPath = storage_path('keys/recipient_public_key.pem');

        // Payload to send
        $payload = json_encode([
            'event' => 'order.'.$orderEvent->order->status->value,
            'data' => [
                'order_id' => $orderEvent->order->id,
                'amount' => $orderEvent->order->amount,
                'status' => $orderEvent->order->status,
                'created_at' => $orderEvent->order->created_at,
                'updated_at' => $orderEvent->order->updated_at,
            ],
            'timestamp' => time(),
        ]);

        // Load private key
        if (false === $privateKey = file_get_contents($privateKeyPath)) {
            $this->fail('Failed to load private key');
        }

        // Sign the payload
        $signature = null;
        $isSigned = openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (! $isSigned) {
            $this->fail('Failed to sign the payload');
        }

        // Base64-encode the signature for transmission
        $encodedSignature = base64_encode($signature);

        // Load recipient's public key
        $recipientPublicKey = file_get_contents($recipientPublicKeyPath);

        if ($recipientPublicKey === false) {
            $this->fail('Failed to load recipient public key');
        }

        // Encrypt the payload
        $encryptedPayload = null;
        $isEncrypted = openssl_public_encrypt($payload, $encryptedPayload, $recipientPublicKey);

        if (! $isEncrypted) {
            $this->fail('Failed to encrypt the playload');
        }

        // Base64-encode the encrypted payload for transmission
        $encodedPayload = base64_encode($encryptedPayload);

        // Send the webhook
        $response = $this->httpFactory->withHeaders([
            'X-Signature' => $encodedSignature, // Include the signature in a custom header
            'Content-Type' => 'application/json',
        ])->post($webhookUrl, [
            'payload' => $encodedPayload, // Send the encrypted payload
        ]);

        // Step 6: Handle the response
        if (! $response->successful()) {
            $this->fail('Endpoint url does not return 20x status code');
        }
    }
}

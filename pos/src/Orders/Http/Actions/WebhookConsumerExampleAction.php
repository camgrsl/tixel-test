<?php

declare(strict_types=1);

namespace Tixel\Orders\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookConsumerExampleAction
{
    public function __invoke(Request $request): JsonResponse
    {
        // Path to recipient's private key (used for decryption)
        $privateKeyPath = storage_path('keys/recipient_private_key.pem');
        // Path to sender's public key (used for verifying signature)
        $senderPublicKeyPath = storage_path('keys/sender_public_key.pem');

        // Get the encrypted payload and signature from the request
        $encodedPayload = $request->input('payload');
        $encodedSignature = $request->header('X-Signature');

        if (! $encodedPayload || ! $encodedSignature) {
            return response()->json(['error' => 'Missing payload or signature'], 400);
        }

        // Step 1: Decrypt the payload
        $privateKey = file_get_contents($privateKeyPath);

        if ($privateKey === false) {
            return response()->json(['error' => 'Failed to load private key'], 500);
        }

        $payload = null;
        $isDecrypted = openssl_private_decrypt(base64_decode($encodedPayload), $payload, $privateKey);

        if (! $isDecrypted) {
            return response()->json(['error' => 'Failed to decrypt payload'], 400);
        }

        // Verify the signature
        $senderPublicKey = file_get_contents($senderPublicKeyPath);

        if ($senderPublicKey === false) {
            return response()->json(['error' => 'Failed to load sender public key'], 500);
        }

        $isSignatureValid = openssl_verify($payload, base64_decode($encodedSignature), $senderPublicKey, OPENSSL_ALGO_SHA256);

        if ($isSignatureValid !== 1) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Process the decrypted payload
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON in payload'], 400);
        }

        // Log the decoded payload
        \Log::info('Webhook received:', $data);

        // Respond to the sender
        return response()->json();
    }
}

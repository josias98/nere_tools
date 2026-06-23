<?php

namespace App\Services\Microsoft;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GraphMailService
{
    public function __construct(private GraphMailTokenService $tokens)
    {
    }

    /**
     * @param array<int, string> $to
     * @param array<int, string> $cc
     * @param array<int, string> $bcc
     */
    public function send(
        array $to,
        string $subject,
        string $html,
        array $cc = [],
        array $bcc = [],
        ?string $text = null,
        ?string $event = null,
        ?string $relatedType = null,
        ?int $relatedId = null,
    ): ?string {
        $fromAddress = (string) config('services.graph_mail.from_address');

        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $html !== '' ? $html : nl2br(e($text ?? '')),
                ],
                'from' => [
                    'emailAddress' => [
                        'address' => $fromAddress,
                        'name' => (string) config('services.graph_mail.from_name'),
                    ],
                ],
                'toRecipients' => $this->recipients($to),
                'ccRecipients' => $this->recipients($cc),
                'bccRecipients' => $this->recipients($bcc),
            ],
            'saveToSentItems' => (bool) config('services.graph_mail.save_to_sent_items'),
        ];

        try {
            $response = $this->microsoftHttp()->withToken($this->tokens->accessToken())
                ->acceptJson()
                ->timeout(config('services.graph_mail.timeout'))
                ->connectTimeout(config('services.graph_mail.connect_timeout'))
                ->post("https://graph.microsoft.com/v1.0/users/{$fromAddress}/sendMail", $payload)
                ->throw();

            $requestId = $response->header('request-id') ?: $response->header('client-request-id');

            Log::info('graph_mail.sent', [
                'event' => $event,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'from_address' => $fromAddress,
                'to' => $to,
                'cc' => $cc,
                'bcc' => $bcc,
                'subject' => $subject,
                'graph_request_id' => $requestId,
            ]);

            return $requestId;
        } catch (Throwable $exception) {
            Log::warning('graph_mail.failed', [
                'event' => $event,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'from_address' => $fromAddress,
                'to' => $to,
                'cc' => $cc,
                'bcc' => $bcc,
                'subject' => $subject,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function microsoftHttp(): PendingRequest
    {
        $request = Http::acceptJson();
        $caBundle = config('services.microsoft.ca_bundle');

        if (filled($caBundle)) {
            $caBundlePath = base_path($caBundle);

            if (File::exists($caBundlePath)) {
                $request = $request->withOptions([
                    'verify' => $caBundlePath,
                ]);
            }
        }

        return $request;
    }

    /**
     * @param array<int, string> $emails
     * @return array<int, array<string, array<string, string>>>
     */
    private function recipients(array $emails): array
    {
        $addresses = array_values(array_unique(array_filter(array_map(
            static fn (mixed $email): string => is_string($email) ? strtolower(trim($email)) : '',
            $emails,
        ))));

        return array_map(
            static fn (string $address): array => [
                'emailAddress' => [
                    'address' => $address,
                ],
            ],
            $addresses,
        );
    }
}

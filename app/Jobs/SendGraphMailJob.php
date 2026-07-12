<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\Microsoft\GraphMailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;
use Throwable;

class SendGraphMailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 3;

    /**
     * @param  array<int, string>  $to
     * @param  array<int, string>  $cc
     * @param  array<int, string>  $bcc
     */
    public function __construct(
        public array $to,
        public string $subject,
        public string $html,
        public array $cc = [],
        public array $bcc = [],
        public ?string $text = null,
        public ?string $event = null,
        public ?string $relatedType = null,
        public ?int $relatedId = null,
        public ?int $notificationLogId = null,
    ) {}

    /**
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(GraphMailService $mailer): void
    {
        $notification = $this->notificationLog() ?? $this->createNotificationLog();

        $notification->forceFill([
            'status' => 'sending',
            'attempts' => $this->attempts(),
            'error_message' => null,
        ])->save();

        $requestId = $mailer->send(
            to: $this->to,
            subject: $this->subject,
            html: $this->html,
            cc: $this->cc,
            bcc: $this->bcc,
            text: $this->text,
            event: $this->event,
            relatedType: $this->relatedType,
            relatedId: $this->relatedId,
        );

        $notification->forceFill([
            'status' => 'sent',
            'attempts' => $this->attempts(),
            'graph_request_id' => $requestId,
            'sent_at' => now(),
            'failed_at' => null,
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        ($this->notificationLog() ?? $this->createNotificationLog())->forceFill([
            'status' => 'failed',
            'attempts' => max($this->tries, $this->attempts()),
            'error_message' => Str::limit($exception->getMessage(), 1000),
            'failed_at' => now(),
        ])->save();
    }

    private function notificationLog(): ?NotificationLog
    {
        if (! $this->notificationLogId) {
            return null;
        }

        return NotificationLog::query()->find($this->notificationLogId);
    }

    private function createNotificationLog(): NotificationLog
    {
        $notification = NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => $this->event,
            'related_type' => $this->relatedType,
            'related_id' => $this->relatedId,
            'to_recipients' => $this->to,
            'cc_recipients' => $this->cc,
            'bcc_recipients' => $this->bcc,
            'subject' => $this->subject,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $this->notificationLogId = $notification->id;

        return $notification;
    }
}

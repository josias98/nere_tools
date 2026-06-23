<?php

namespace App\Console\Commands;

use App\Jobs\SendGraphMailJob;
use App\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendGraphMailTestCommand extends Command
{
    protected $signature = 'graph-mail:test {recipient?} {--sync}';

    protected $description = 'Envoie un email de test via Microsoft Graph.';

    public function handle(): int
    {
        $tokenCacheKey = config('services.graph_mail.token_cache_key');

        if (is_string($tokenCacheKey) && $tokenCacheKey !== '') {
            Cache::forget($tokenCacheKey);
        }

        $recipient = $this->argument('recipient') ?: config('services.graph_mail.test_recipient');

        if (! is_string($recipient) || trim($recipient) === '') {
            $this->error("Aucun destinataire defini. Passez {recipient} ou configurez GRAPH_MAIL_TEST_RECIPIENT.");

            return self::FAILURE;
        }

        $subject = 'Test de notification Nere Tools';
        $html = view('emails.system.test', [
            'recipient' => $recipient,
            'actionUrl' => rtrim((string) config('app.url'), '/').'/',
            'sentAt' => now(),
        ])->render();

        $notification = NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => 'system.test',
            'to_recipients' => [$recipient],
            'subject' => $subject,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $job = new SendGraphMailJob(
            to: [$recipient],
            subject: $subject,
            html: $html,
            text: "Ceci est un test de notification Nere Tools. Ouvrir l'application: ".rtrim((string) config('app.url'), '/').'/',
            event: 'system.test',
            notificationLogId: $notification->id,
        );

        if ($this->option('sync')) {
            dispatch_sync($job);
            $this->info("Email de test envoye a {$recipient}.");

            return self::SUCCESS;
        }

        dispatch($job);
        $this->info("Notification de test mise en file d'attente pour {$recipient}.");

        return self::SUCCESS;
    }
}

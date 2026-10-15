<section class="leave-reminder" id="relance" aria-labelledby="reminder-title">
    <div>
        <h3 id="reminder-title">Relancer un signataire</h3>
        <p>Étape en attente : <strong>{{ $leaveRequest->currentApproval->step_label }}</strong>.</p>
    </div>
    @if (! config('services.graph_mail.enabled'))
        <p>L’envoi des emails est désactivé. Contactez l’administration pour activer les relances.</p>
    @elseif ($reminderRecipients === [])
        <p>Aucun signataire joignable par email n’est configuré pour cette étape. Contactez l’administration.</p>
    @else
        <form method="POST" action="{{ route('leaves.remind', $leaveRequest->uuid) }}" class="leave-reminder-form" data-leave-reminder>
            @csrf
            <input type="hidden" name="approval_id" value="{{ $leaveRequest->currentApproval->id }}">
            @if (count($reminderRecipients) === 1)
                <input type="hidden" name="recipient" value="{{ $reminderRecipients[0]['email'] }}">
                <p>Destinataire : <strong>{{ $reminderRecipients[0]['name'] }}</strong>.</p>
            @else
                <label class="nc-field" for="reminder-recipient">
                    <span>Signataire à relancer</span>
                    <select id="reminder-recipient" name="recipient" required aria-describedby="reminder-help">
                        @foreach ($reminderRecipients as $signatory)
                            <option value="{{ $signatory['email'] }}">{{ $signatory['name'] }} — {{ $signatory['email'] }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <button class="nc-ghost" type="submit" data-pending-label="Relance…">
                <i data-lucide="bell-ring" class="nc-icon" aria-hidden="true"></i>
                Relancer par email
            </button>
        </form>
        <p class="nc-muted" id="reminder-help">Seul le signataire choisi recevra un rappel avec le lien vers la demande. Une relance maximum par signataire et par heure.</p>
    @endif
</section>

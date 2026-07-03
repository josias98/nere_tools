@include('errors.layout', [
    'status' => $status ?? 500,
    'heading' => 'Incident enregistre',
    'message' => "Nere Tools a rencontre une erreur inattendue.",
    'summary' => 'La trace technique a ete journalisee.',
    'detail' => 'Contactez l equipe support avec l heure de l incident si le probleme persiste.',
])

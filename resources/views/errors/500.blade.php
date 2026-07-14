@include('errors.layout', [
    'status' => $status ?? 500,
    'heading' => 'Oh oh, incident enregistré !',
    'message' => "Nere Tools a rencontré une erreur inattendue, en tentant d'effectuer cette action.",
    'summary' => 'Don\'t worry, La trace technique a ete journalisée.',
    'detail' => 'Contactez l équipe support avec l heure de l incident si le problème persiste.',
])

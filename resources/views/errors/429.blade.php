@include('errors.layout', [
    'status' => $status ?? 429,
    'heading' => 'Trop de tentatives',
    'message' => 'Néré Tools limite temporairement cette action pour protéger le service.',
    'summary' => 'Veuillez patienter quelques instants.',
    'detail' => 'Réessayez dans quelques minutes. Contactez l’administrateur si le blocage persiste.',
    'retry' => true,
])

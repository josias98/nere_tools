@include('errors.layout', [
    'status' => $status ?? 500,
    'heading' => 'Action indisponible',
    'message' => "Nere Tools ne peut pas afficher cette page pour le moment.",
    'summary' => 'La trace a ete journalisee.',
    'detail' => 'Revenez au dashboard pour continuer depuis un espace stable.',
])

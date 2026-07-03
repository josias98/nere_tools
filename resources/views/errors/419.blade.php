@include('errors.layout', [
    'status' => $status ?? 419,
    'heading' => 'Session expiree',
    'message' => 'La session de securite a expire avant la fin de votre action.',
    'summary' => 'Action non enregistree.',
    'detail' => 'Reconnectez-vous puis relancez uniquement l action necessaire.',
])

@include('errors.layout', [
    'status' => $status ?? 403,
    'heading' => 'Acces refuse',
    'message' => "Vous n'avez pas les droits requis pour ouvrir cette page.",
    'summary' => 'La demande est protegee.',
    'detail' => 'Si vous venez de valider une demande, elle a probablement quitte votre file de validation.',
])

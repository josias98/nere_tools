@include('errors.layout', [
    'status' => $status ?? 404,
    'heading' => 'Page introuvable',
    'message' => "Le lien demande n'existe pas ou n'est plus disponible.",
    'summary' => 'Aucune ressource correspondante.',
    'detail' => 'Revenez au dashboard pour reprendre depuis un espace connu.',
])

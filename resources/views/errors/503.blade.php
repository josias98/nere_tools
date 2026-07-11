@include('errors.layout', [
    'status' => $status ?? 503,
    'heading' => 'Service temporairement indisponible',
    'message' => 'Néré Tools est en maintenance ou momentanément indisponible.',
    'summary' => 'Aucune action supplémentaire n’est nécessaire.',
    'detail' => 'Patientez quelques instants puis réessayez. Vos données déjà enregistrées sont conservées.',
    'retry' => true,
])

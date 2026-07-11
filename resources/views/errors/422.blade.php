@include('errors.layout', [
    'status' => $status ?? 422,
    'heading' => 'Informations à corriger',
    'message' => "Certaines informations transmises ne peuvent pas être utilisées.",
    'summary' => 'Votre action n’a pas été enregistrée.',
    'detail' => 'Revenez au formulaire et corrigez les champs signalés avant de réessayer.',
])

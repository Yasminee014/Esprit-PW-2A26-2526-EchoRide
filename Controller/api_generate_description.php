<?php
// Controller/api_generate_description.php
header('Content-Type: application/json');

// 🔑 METTEZ VOTRE CLÉ API ICI (copiez-collez)
$apiKey = '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';

// Récupérer les données envoyées
$input = json_decode(file_get_contents('php://input'), true);
$titre = $input['titre'] ?? '';
$ville = $input['ville'] ?? '';
$type = $input['type'] ?? '';

if(empty($titre)) {
    echo json_encode(['error' => 'Titre manquant']);
    exit();
}

// Préparer le prompt pour l'IA
$prompt = "Génère une description courte et attrayante (2-3 phrases) pour un événement avec les caractéristiques suivantes :
- Titre : $titre
- Ville : $ville
- Type : $type

La description doit être enthousiaste, professionnelle et donner envie de participer.
Réponds uniquement avec la description, sans texte supplémentaire.";

// Appel à l'API OpenRouter
$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
    'HTTP-Referer: http://localhost/projet-event',
    'X-Title: Eco Ride'
]);

curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'max_tokens' => 200,
    'temperature' => 0.8
]));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpCode == 200) {
    $data = json_decode($response, true);
    $description = $data['choices'][0]['message']['content'] ?? '';
    echo json_encode(['description' => trim($description)]);
} else {
    // Fallback : génération locale si l'API échoue
    $descriptions = [
        'concert' => "🎵 Ne manquez pas \"$titre\" ! Un concert exceptionnel vous attend à $ville. Réservez vos places dès maintenant pour une soirée inoubliable !",
        'match' => "⚽ Vivez l'émotion du sport avec \"$titre\" à $ville. Venez soutenir votre équipe dans une ambiance électrique !",
        'festival' => "🎉 Plongez dans l'ambiance unique du festival \"$titre\" à $ville. Musique, bonne humeur et découvertes vous attendent !",
        'sortie' => "🌿 Participez à \"$titre\" à $ville. Une belle occasion de partager un moment convivial en pleine nature !",
        'autre' => "✨ Découvrez \"$titre\" à $ville. Un événement exceptionnel à ne pas manquer !"
    ];
    $description = $descriptions[$type] ?? $descriptions['autre'];
    echo json_encode(['description' => $description, 'fallback' => true]);
}
?>
<?php
include '../config.php';
header('Content-Type: application/json');

// Ensure the Hugging Face API token is provided
$apiKey = defined('HF_API_KEY') ? HF_API_KEY : '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';

if (empty($apiKey)) {
    die(json_encode([
        'success' => false,
        'response' => 'Chatbot configuration error'
    ]));
}

$query = $_POST['query'] ?? '';
$sessionId = session_id();
$userId = $_SESSION['user_id'] ?? null;

try {
    // Prepare the Hugging Face API request URL
    $url = "https://api-inference.huggingface.co/models/EleutherAI/gpt-neo-2.7B";
    
    // Prepare the request data
    $data = [
        'inputs' => "You're a helpful plant store assistant. Respond to: " . $query
    ];

    // Set up the headers with the API key for authentication
    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ];

    // Set up the HTTP context options for the request
    $options = [
        'http' => [
            'header'  => implode("\r\n", $headers),
            'method'  => 'POST',
            'content' => json_encode($data),
            'timeout' => 15
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        throw new Exception("API request failed");
    }

    // Decode the response from Hugging Face
    $responseData = json_decode($response, true);

    if (isset($responseData['generated_text'])) {
        $botResponse = $responseData['generated_text'];
        
        echo json_encode([
            'success' => true,
            'response' => $botResponse
        ]);
    } else {
        throw new Exception("Unexpected API response format: " . json_encode($responseData));
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'response' => "I'm having trouble answering right now. Please try again later.",
        'error' => $e->getMessage() // Remove in production
    ]);
}

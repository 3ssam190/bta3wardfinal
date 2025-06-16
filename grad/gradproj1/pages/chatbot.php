<?php
include '../config.php'; // Database connection

$apiKey = "AIzaSyBVd42crYY4jUeagT9n2oEALV9tEn8wR78";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    $userID = $_SESSION['UserID'] ?? null;
    
    $query = trim($_POST['query']);
    
    // Normalize Arabic and English queries
    $query = mb_strtolower($query, 'UTF-8');
    
    // Extract potential plant name from the query
    preg_match('/(?:معلومات عن|عايز اعرف عن|tell me about|info about) (.+)/i', $query, $matches);
    $plantName = $matches[1] ?? null;

    if ($plantName) {
        // Search for the plant in the database (Arabic & English support)
        $stmt = $conn->prepare("SELECT Name, Description, Price, image, id FROM items WHERE LOWER(Name) LIKE ? OR LOWER(Name) LIKE ? LIMIT 1");
        $stmt->execute(["%$plantName%", "%$plantName%"]);
        $plant = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($plant) {
            $plantLink = "https://helectronicservices.com/grad/gradproj/product.php?id=" . $plant['id'];
            $imageTag = "<img src='https://helectronicservices.com/grad/gradproj/uploads/{$plant['image']}' alt='{$plant['Name']}' width='150' style='border-radius:10px;'>";
            
            $response = "<div style='font-family:Arial, sans-serif;'>";
            $response .= "<h3>🌱 {$plant['Name']}</h3>";
            $response .= $imageTag . "<br><br>";
            $response .= "<p>{$plant['Description']}</p>";
            $response .= "<p><strong>💲 Price:</strong> \${$plant['Price']}</p>";
            $response .= "<p><a href='$plantLink' target='_blank' style='color:#28a745; font-weight:bold; text-decoration:none;'>🔗 View in Store</a></p>";
            $response .= "</div>";
        } else {
            // Try to find similar plants
            $stmt = $conn->prepare("SELECT Name FROM items WHERE LOWER(Name) LIKE ? LIMIT 3");
            $stmt->execute(["%$plantName%"]);
            $similarPlants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($similarPlants) {
                $response = "لم يتم العثور على النبات المطلوب. ربما تقصد: ";
                foreach ($similarPlants as $plant) {
                    $response .= "<br>🌱 " . htmlspecialchars($plant['Name']);
                }
            } else {
                // If no similar plants, fetch response from Gemini API
                $response = fetchGeminiResponse($query, $apiKey);
            }
        }
    } else {
        // Default case - general plant-related queries
        $response = fetchGeminiResponse($query, $apiKey);
    }
    
    // Log query and response
    $stmt = $conn->prepare("INSERT INTO chatbot_logs (userID, Query, Response, Timestamp) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$userID, $query, $response]);
    
    // Send response
    echo json_encode(['response' => $response]);
}

// Function to fetch response from Gemini AI
function fetchGeminiResponse($query, $apiKey) {
    $data = [
        "contents" => [["role" => "user", "parts" => [["text" => $query]]]],
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($result, true);
    return strip_tags(preg_replace('/\*{1,2}(.*?)\*{1,2}/', '$1', $responseData['candidates'][0]['content']['parts'][0]['text'] ?? "عذرًا، لم أتمكن من معالجة هذا الطلب."));
}
?>

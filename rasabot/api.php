<?php
header('Content-Type: application/json');


ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
// Database configuration
$db_host = "localhost";
$db_user = "u578375581_plants_admin";
$db_pass = "ESAM123esam@";
$db_name = "u578375581_plants_store";
// Enable error logging

file_put_contents(__DIR__ . '/request.log', print_r([
    'time' => date('Y-m-d H:i:s'),
    'input' => file_get_contents('php://input'),
    'post' => $_POST,
    'get' => $_GET
], true), FILE_APPEND);


try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get Rasa webhook data
    $input = json_decode(file_get_contents('php://input'), true);
    $intent = $input['next_action'] ?? '';
    $slots = $input['tracker']['slots'] ?? [];

    // Handle different actions
    if ($intent === 'action_search_products') {
        $query = "SELECT p.*, c.name as category_name 
                 FROM Products p
                 JOIN Categories c ON p.category_id = c.category_id
                 WHERE p.stock_quantity > 0";
        
        $conditions = [];
        $params = [];

        // Add filters based on slots
        if (!empty($slots['category'])) {
            $conditions[] = "(LOWER(p.name) LIKE LOWER(:category) OR LOWER(c.name) LIKE LOWER(:category))";
            $params[':category'] = "%{$slots['category']}%";
        }

        if (!empty($slots['price_min'])) {
            $conditions[] = "p.price >= :price_min";
            $params[':price_min'] = (float)$slots['price_min'];
        }

        if (!empty($slots['price_max'])) {
            $conditions[] = "p.price <= :price_max";
            $params[':price_max'] = (float)$slots['price_max'];
        }

        if (!empty($slots['featured']) && $slots['featured']) {
            $conditions[] = "p.is_featured = 1";
        }

        if (!empty($slots['environment_suitability'])) {
            $conditions[] = "p.environment_suitability = :environment";
            $params[':environment'] = $slots['environment_suitability'];
        }

        if ($conditions) {
            $query .= " AND " . implode(" AND ", $conditions);
        }

        $query .= " ORDER BY p.is_featured DESC, p.price LIMIT 6";

        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format response
        if ($products) {
            $response = ["🌿 Available Products:"];
            foreach ($products as $product) {
                $product_text = [
                    "\n🌸 *{$product['name']}* (\${$product['price']})",  // Fixed price formatting
                    "📦 Category: {$product['category_name']}",
                    "🏷️ In stock: {$product['stock_quantity']}"
                ];
                
                if ($product['environment_suitability']) {
                    $product_text[] = "🌱 Suitable for: {$product['environment_suitability']}";
                }
                
                if ($product['care_instructions']) {
                    $product_text[] = "💡 Care: " . substr($product['care_instructions'], 0, 100) . "...";
                }
                
                if ($product['is_featured']) {
                    $product_text[] = "⭐ Featured Product";
                }
                
                $response[] = implode("\n", $product_text);
            }
            
            echo json_encode([
                "events" => [],
                "responses" => [
                    ["text" => implode("\n", $response)]
                ]
            ]);
        } else {
            echo json_encode([
                "events" => [],
                "responses" => [
                    ["text" => "No matching products found. Try different filters."]
                ]
            ]);
        }
    } else {
        echo json_encode([
            "events" => [],
            "responses" => []
        ]);
    }

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        "events" => [],
        "responses" => [
            ["text" => "Our plant catalog is currently unavailable. Please try again later."]
        ]
    ]);
}
?>
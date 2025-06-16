<?php
define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'] . '');
define('ASSETS_DIR', '/assets/images/');
define('DEFAULT_PROFILE', ASSETS_DIR . 'default-profile.png');
define('DEFAULT_ADMIN', ASSETS_DIR . 'default-admin.png');
// Paymob Configuration
define('PAYMOB_API_KEY', 'ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2T1RRM016ZzRMQ0p1WVcxbElqb2lhVzVwZEdsaGJDSjkudkxtaDZ2TjI1QnZ0T0RBY0RFajVlYjhsMGtFcUpqLXBxb1poME5HSnVfRDMteDRLNk1WTDN3ZDMtTUtfTEF5ZVZwT3paU2ZyYjJWeXEwV3R5S2gxYWc=');
define('PAYMOB_CARD_INTEGRATION_ID', '4418468');
define('PAYMOB_IFRAME_ID', '809800');
define('PAYMOB_HMAC_SECRET', '94C7664F3ABE14A7C63F6FEA9BE6FCAA');

if (defined('CONFIG_LOADED')) {
    return;
}
define('CONFIG_LOADED', true);

define('CURRENCY', 'EGP ');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Language settings
define('DEFAULT_LANGUAGE', 'en');
$available_languages = ['en', 'ar'];

// Set language from session or default
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    if (in_array($lang, $available_languages)) {
        $_SESSION['lang'] = $lang;
    }
}
$current_lang = $_SESSION['lang'] ?? DEFAULT_LANGUAGE;

// Load language file
if (!function_exists('loadLanguage')) {
    function loadLanguage($lang) {
        $lang_file = __DIR__ . "/languages/{$lang}.php";
        if (file_exists($lang_file)) {
            return require $lang_file;
        }
        return require __DIR__ . "/languages/" . DEFAULT_LANGUAGE . ".php";
    }
}

$translations = loadLanguage($current_lang);


if (!function_exists('__')) {
    function __($key) {
        global $translations;
        return $translations[$key] ?? $key;
    }
}

// Database connection
require_once __DIR__ . '/admin/config/database.php';
$conn = Database::connect();


/**
 * Get user by email
 */
function getUserByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



function mergeCarts($userId, $sessionId) {
    global $conn;
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Get or create user's cart
        $userCart = getOrCreateCart($userId, $sessionId);
        
        // Get guest cart by session ID (where user_id is null)
        $stmt = $conn->prepare("SELECT * FROM Carts WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$sessionId]);
        $guestCart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($guestCart && $guestCart['cart_id'] != $userCart['cart_id']) {
            // Get all items from guest cart
            $stmt = $conn->prepare("SELECT * FROM CartItems WHERE cart_id = ?");
            $stmt->execute([$guestCart['cart_id']]);
            $guestItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($guestItems as $item) {
                // Check if user already has this product in their cart
                $stmt = $conn->prepare("SELECT * FROM CartItems 
                                      WHERE cart_id = ? AND product_id = ?");
                $stmt->execute([$userCart['cart_id'], $item['product_id']]);
                $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existingItem) {
                    // Update quantity if product exists
                    $newQuantity = $existingItem['quantity'] + $item['quantity'];
                    $stmt = $conn->prepare("UPDATE CartItems 
                                          SET quantity = ? 
                                          WHERE cart_item_id = ?");
                    $stmt->execute([$newQuantity, $existingItem['cart_item_id']]);
                } else {
                    // Add new item to user's cart
                    $stmt = $conn->prepare("INSERT INTO CartItems 
                                          (cart_id, product_id, quantity, price, custom_data, is_custom, custom_type) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $userCart['cart_id'],
                        $item['product_id'],
                        $item['quantity'],
                        $item['price'],
                        $item['custom_data'],
                        $item['is_custom'],
                        $item['custom_type']
                    ]);
                }
            }
            
            // Delete guest cart items
            $stmt = $conn->prepare("DELETE FROM CartItems WHERE cart_id = ?");
            $stmt->execute([$guestCart['cart_id']]);
            
            // Delete guest cart
            $stmt = $conn->prepare("DELETE FROM Carts WHERE cart_id = ?");
            $stmt->execute([$guestCart['cart_id']]);
        }
        
        // Commit transaction
        $conn->commit();
        
        return $userCart;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        error_log("Cart merge failed: " . $e->getMessage());
        return false;
    }
}
/**
 * Create a new user
 */
function createUser($data) {
    global $conn;

    try {
        $stmt = $conn->prepare("INSERT INTO Users 
            (email, password_hash, first_name, last_name, phone, address, city, region, postal_code, profile_photo, is_verified, verification_token, token_expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        $success = $stmt->execute([
            $data['email'],
            $hashedPassword,
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['region'] ?? null,
            $data['postal_code'] ?? null,
            $data['profile_photo'] ?? null,
            $data['is_verified'] ?? 0,
            $data['verification_token'] ?? null,
            $data['token_expires_at'] ?? null
        ]);

        if (!$success) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("PDO Error: " . $errorInfo[2]);
        }

        return $success;
    } catch (Exception $e) {
        die("Error creating user: " . $e->getMessage());
    }
}


function storePasswordResetToken($userId, $token, $expires, $userType = 'user') {
    global $conn;
    
    // Check if connection exists
    if (!$conn) {
        error_log("Database connection not established in storePasswordResetToken");
        return false;
    }
    
    $table = ($userType === 'admin') ? 'password_reset_admin' : 'password_reset';
    $idField = ($userType === 'admin') ? 'admin_id' : 'user_id';
    
    try {
        // Delete any existing tokens for this user
        $stmt = $conn->prepare("DELETE FROM $table WHERE $idField = ?");
        $stmt->execute([$userId]);
        
        // Insert new token
        $stmt = $conn->prepare("INSERT INTO $table ($idField, token, expires_at) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $token, $expires]);
    } catch (PDOException $e) {
        error_log("Error in storePasswordResetToken: " . $e->getMessage());
        return false;
    }
}

function getPasswordResetToken($token, $userType = 'user') {
    global $conn;
    
    $table = ($userType === 'admin') ? 'password_reset_admin' : 'password_reset';
    $idField = ($userType === 'admin') ? 'admin_id' : 'user_id';
    
    $stmt = $conn->prepare("SELECT * FROM $table WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function deletePasswordResetToken($token, $userType = 'user') {
    global $conn;
    
    $table = ($userType === 'admin') ? 'password_reset_admin' : 'password_reset';
    $stmt = $conn->prepare("DELETE FROM $table WHERE token = ?");
    return $stmt->execute([$token]);
}



function getAdminByEmail($email) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Admins WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}



/**
 * Clears all items from a cart and removes the cart record
 * @param int $cartId The ID of the cart to clear
 * @return bool True on success, false on failure
 */
function clearCart($cartId) {
    global $conn;
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Delete all cart items
        $stmt = $conn->prepare("DELETE FROM CartItems WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        
        // Delete the cart itself
        $stmt = $conn->prepare("DELETE FROM Carts WHERE cart_id = ?");
        $stmt->execute([$cartId]);
        
        // Commit transaction
        $conn->commit();
        
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollBack();
        error_log("Error clearing cart: " . $e->getMessage());
        return false;
    }
}
/**
 * Get or create cart for user/session
 */
function getOrCreateCart($userId, $sessionId) {
    global $conn;
    
    try {
        // Try to get existing cart for this user
        $stmt = $conn->prepare("
            SELECT * FROM Carts 
            WHERE user_id = :user_id
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$cart) {
            // Try to get cart by session ID
            $stmt = $conn->prepare("
                SELECT * FROM Carts 
                WHERE session_id = :session_id
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([':session_id' => $sessionId]);
            $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cart) {
                // Update existing cart with user ID
                $stmt = $conn->prepare("
                    UPDATE Carts 
                    SET user_id = :user_id 
                    WHERE cart_id = :cart_id
                ");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':cart_id' => $cart['cart_id']
                ]);
            } else {
                // Create brand new cart
                $stmt = $conn->prepare("
                    INSERT INTO Carts (user_id, session_id) 
                    VALUES (:user_id, :session_id)
                ");
                $stmt->execute([
                    ':user_id' => $userId,
                    ':session_id' => $sessionId
                ]);
                $cartId = $conn->lastInsertId();
                
                $stmt = $conn->prepare("SELECT * FROM Carts WHERE cart_id = :cart_id");
                $stmt->execute([':cart_id' => $cartId]);
                $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        
        return $cart;
    } catch (PDOException $e) {
        error_log("Cart error: " . $e->getMessage());
        return false;
    }
}

function getDeliveryRegions() {
    global $conn;
    return $conn->query("SELECT * FROM DeliveryPricing ORDER BY region_name")->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get delivery fee for a region
 */
function getDeliveryFee($regionId) {
    global $conn;
    $stmt = $conn->prepare("SELECT delivery_fee FROM DeliveryPricing WHERE region_id = ?");
    $stmt->execute([$regionId]);
    return $stmt->fetchColumn();
}

function calculateCartTotal($cartItems) {
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/**
 * Get cart items with product details
 */
function getCartItems($cartId) {
    global $conn;
    $stmt = $conn->prepare("SELECT ci.*, p.name, p.price, p.stock_quantity, pi.image_url 
                           FROM CartItems ci
                           JOIN Products p ON ci.product_id = p.product_id
                           LEFT JOIN ProductImages pi ON p.product_id = pi.product_id AND pi.is_primary = 1
                           WHERE ci.cart_id = ?");
    $stmt->execute([$cartId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Add product to cart
 */
function addToCart($cartId, $productId, $quantity = 1) {
    global $conn;
    
    // Check if product exists and has stock
    $product = getProductById($productId);
    if (!$product || $product['stock_quantity'] < $quantity) {
        return false;
    }
    
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT * FROM CartItems WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    $existingItem = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingItem) {
        $newQuantity = $existingItem['quantity'] + $quantity;
        if ($newQuantity > $product['stock_quantity']) {
            return false;
        }
        
        $stmt = $conn->prepare("UPDATE CartItems SET quantity = ? WHERE cart_item_id = ?");
        return $stmt->execute([$newQuantity, $existingItem['cart_item_id']]);
    } else {
        $stmt = $conn->prepare("INSERT INTO CartItems (cart_id, product_id, quantity) VALUES (?, ?, ?)");
        return $stmt->execute([$cartId, $productId, $quantity]);
    }
}

/**
 * Get product by ID
 */
// function getProductById($productId) {
//     global $conn; // Make sure we can access the connection
    
//     if (!$conn) {
//         throw new Exception("Database connection not established");
//     }

//     try {
//         $stmt = $conn->prepare("SELECT p.*, 
//                               c.name AS category_name,
//                               (SELECT image_url FROM ProductImages 
//                               WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) AS primary_image
//                               FROM Products p
//                               LEFT JOIN Categories c ON p.category_id = c.category_id
//                               WHERE p.product_id = :product_id");
        
//         $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
//         $stmt->execute();
        
//         $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
//         if (!$product) {
//             throw new Exception("Product not found");
//         }
        
//         return $product;
//     } catch (PDOException $e) {
//         error_log("Database error in getProductById: " . $e->getMessage());
//         throw new Exception("Failed to retrieve product information");
//     }
// }

function getProductById($productId, $lang = 'en') {
    global $conn;

    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name, 
               COALESCE(t_name.translated_text, p.name) AS name,
               COALESCE(t_desc.translated_text, p.description) AS description,
               COALESCE(t_care.translated_text, p.care_instructions) AS care_instructions,
               COALESCE(t_env.translated_text, p.environment_suitability) AS environment_suitability,
               pi.image_url AS primary_image
        FROM Products p
        LEFT JOIN Categories c ON p.category_id = c.category_id
        LEFT JOIN Translations t_name ON 
            t_name.entity_type = 'product' AND 
            t_name.entity_id = p.product_id AND 
            t_name.field_name = 'name' AND 
            t_name.language_code = ?
        LEFT JOIN Translations t_desc ON 
            t_desc.entity_type = 'product' AND 
            t_desc.entity_id = p.product_id AND 
            t_desc.field_name = 'description' AND 
            t_desc.language_code = ?
        LEFT JOIN Translations t_care ON 
            t_care.entity_type = 'product' AND 
            t_care.entity_id = p.product_id AND 
            t_care.field_name = 'care_instructions' AND 
            t_care.language_code = ?
        LEFT JOIN Translations t_env ON 
            t_env.entity_type = 'product' AND 
            t_env.entity_id = p.product_id AND 
            t_env.field_name = 'environment_suitability' AND 
            t_env.language_code = ?
        LEFT JOIN ProductImages pi ON 
            pi.product_id = p.product_id AND 
            pi.is_primary = 1
        WHERE p.product_id = ?
    ");

    // PDO version: execute with array of parameters
    $stmt->execute([$lang, $lang, $lang, $lang, $productId]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}


function getProductImages($productId) {
    global $conn; // Access the database connection

    if (!$conn) {
        throw new Exception("Database connection not established");
    }

    try {
        // Prepare the SQL query to get all images for the product
        $stmt = $conn->prepare("
            SELECT image_id, product_id, image_url, is_primary 
            FROM ProductImages 
            WHERE product_id = :product_id
            ORDER BY is_primary DESC, image_id ASC
        ");
        
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_INT);
        $stmt->execute();
        
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ensure we always return an array, even if empty
        return $images ?: [];
        
    } catch (PDOException $e) {
        error_log("Database error in getProductImages: " . $e->getMessage());
        throw new Exception("Failed to retrieve product images");
    }
}
/**
 * Get all products
 */
// function getAllProducts() {
//     global $conn;
//     $stmt = $conn->prepare("SELECT p.*, 
//                           (SELECT image_url FROM ProductImages WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
//                           FROM Products p");
//     $stmt->execute();
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

function getAllProducts($conn, $lang = 'en') {
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name, 
               COALESCE(t_name.translated_text, p.name) AS name,
               t_desc.translated_text AS description,
               pi.image_url AS primary_image
        FROM Products p
        LEFT JOIN Categories c ON p.category_id = c.category_id
        LEFT JOIN Translations t_name ON 
            t_name.entity_type = 'product' AND 
            t_name.entity_id = p.product_id AND 
            t_name.field_name = 'name' AND 
            t_name.language_code = ?
        LEFT JOIN Translations t_desc ON 
            t_desc.entity_type = 'product' AND 
            t_desc.entity_id = p.product_id AND 
            t_desc.field_name = 'description' AND 
            t_desc.language_code = ?
        LEFT JOIN ProductImages pi ON 
            pi.product_id = p.product_id AND 
            pi.is_primary = 1
    ");

    // Use PDO style binding:
    $stmt->execute([$lang, $lang]);

    // Fetch all results as associative array
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


/**
 * Get featured products
 */
// function getFeaturedProducts($limit = 6) {
//     global $conn;
//     $stmt = $conn->prepare("SELECT p.*, 
//                           (SELECT image_url FROM ProductImages WHERE product_id = p.product_id AND is_primary = 1 LIMIT 1) as primary_image
//                           FROM Products p
//                           WHERE p.is_featured = 1
//                           LIMIT ?");
//     $stmt->bindValue(1, $limit, PDO::PARAM_INT);
//     $stmt->execute();
//     return $stmt->fetchAll(PDO::FETCH_ASSOC);
// }

/**
 * Get categories
 */
function getCategories($conn, $lang = 'en') {
    // Check if Arabic columns exist
    $stmt = $conn->query("SHOW COLUMNS FROM Categories LIKE 'name_ar'");
    $hasArabic = ($stmt->rowCount() > 0);
    
    // Select appropriate fields based on language
    $nameField = ($lang === 'ar' && $hasArabic) ? 'name_ar AS name' : 'name';
    $descField = ($lang === 'ar' && $hasArabic) ? 'description_ar AS description' : 'description';
    
    $query = "SELECT category_id, $nameField, $descField FROM Categories ORDER BY name";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Log chatbot interaction
 */
function logChatbotInteraction($sessionId, $userId, $userMessage, $botResponse, $intent = null, $confidence = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO ChatbotInteractions 
                           (user_id, session_id, user_message, bot_response, intent, confidence_score)
                           VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $sessionId, $userMessage, $botResponse, $intent, $confidence]);
}
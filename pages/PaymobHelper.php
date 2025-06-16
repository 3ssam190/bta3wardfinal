<?php
class PaymobHelper {
    private $apiKey;
    private $integrationId;
    private $iframeId;
    private $hmacSecret;

    public function __construct() {
        // Get credentials from config constants
        $this->apiKey = defined('PAYMOB_API_KEY') ? PAYMOB_API_KEY : '';
        $this->integrationId = defined('PAYMOB_CARD_INTEGRATION_ID') ? PAYMOB_CARD_INTEGRATION_ID : '';
        $this->iframeId = defined('PAYMOB_IFRAME_ID') ? PAYMOB_IFRAME_ID : '';
        $this->hmacSecret = defined('PAYMOB_HMAC_SECRET') ? PAYMOB_HMAC_SECRET : '';
        
        // Validate that all required constants are set
        if (empty($this->apiKey) || empty($this->integrationId) || empty($this->iframeId)) {
            throw new Exception("Paymob configuration constants are not properly set in config.php");
        }
    }

    public function authenticate() {
        $url = "https://accept.paymob.com/api/auth/tokens";
        $data = ['api_key' => $this->apiKey];
        
        $response = $this->curlPost($url, $data);
        return $response['token'] ?? null;
    }

    public function createOrder($token, $amount, $merchantOrderId, $items = []) {
        $url = "https://accept.paymob.com/api/ecommerce/orders";
        $data = [
            'auth_token' => $token,
            'delivery_needed' => false,
            'amount_cents' => $amount * 100, // Paymob uses cents
            'currency' => 'EGP', // Change to your currency
            'merchant_order_id' => $merchantOrderId,
            'items' => $items
        ];
        
        $response = $this->curlPost($url, $data);
        return $response['id'] ?? null;
    }
    
    
    public function getVodafoneCashPaymentKey($token, $orderId, $amount, $phoneNumber) {
        $url = "https://accept.paymob.com/api/acceptance/payment_keys";
        $data = [
            'auth_token' => $token,
            'amount_cents' => $amount * 100,
            'expiration' => 3600,
            'order_id' => $orderId,
            'billing_data' => [
                'phone_number' => $phoneNumber,
                'email' => $_SESSION['user']['email'] ?? '',
                'first_name' => explode(' ', $_POST['full_name'])[0],
                'last_name' => explode(' ', $_POST['full_name'])[1] ?? '',
            ],
            'currency' => 'EGP',
            'integration_id' => 5096403, // Vodafone Cash integration ID
            'payment_method' => 'WALLET'
        ];
        
        $response = $this->curlPost($url, $data);
        return $response['token'] ?? null;
    }

    public function getPaymentKey($token, $orderId, $amount, $billingData) {
        $url = "https://accept.paymob.com/api/acceptance/payment_keys";
        $data = [
            'auth_token' => $token,
            'amount_cents' => $amount * 100,
            'expiration' => 3600, // 1 hour
            'order_id' => $orderId,
            'billing_data' => $billingData,
            'currency' => 'EGP', // Change to your currency
            'integration_id' => $this->integrationId
        ];
        
        $response = $this->curlPost($url, $data);
        return $response['token'] ?? null;
    }

    public function verifyHmac($data) {
        $receivedHmac = $data['hmac'];
        $queryString = 
            'amount_cents=' . $data['amount_cents'] .
            '&created_at=' . $data['created_at'] .
            '&currency=' . $data['currency'] .
            '&error_occured=' . $data['error_occured'] .
            '&has_parent_transaction=' . $data['has_parent_transaction'] .
            '&id=' . $data['id'] .
            '&integration_id=' . $data['integration_id'] .
            '&is_3d_secure=' . $data['is_3d_secure'] .
            '&is_auth=' . $data['is_auth'] .
            '&is_capture=' . $data['is_capture'] .
            '&is_refunded=' . $data['is_refunded'] .
            '&is_standalone_payment=' . $data['is_standalone_payment'] .
            '&is_voided=' . $data['is_voided'] .
            '&order=' . $data['order'] .
            '&owner=' . $data['owner'] .
            '&pending=' . $data['pending'] .
            '&source_data_pan=' . $data['source_data_pan'] .
            '&source_data_sub_type=' . $data['source_data_sub_type'] .
            '&source_data_type=' . $data['source_data_type'] .
            '&success=' . $data['success'];
        
        $calculatedHmac = hash_hmac('sha512', $queryString, $this->hmacSecret);
        return hash_equals($calculatedHmac, $receivedHmac);
    }

    private function curlPost($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
?>
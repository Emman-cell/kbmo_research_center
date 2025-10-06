<?php
class PaymentService {
    private $pdo;
    private $config;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->config = require 'config/payment_config.php';
    }
    
    /**
     * Initialize mobile money payment
     */
    public function initializeMobileMoneyPayment($document_id, $phone_number, $provider_id = 1) {
        try {
            // Get document details
            $stmt = $this->pdo->prepare("SELECT d.*, s.price, u.full_name, u.email 
                                       FROM documents d 
                                       JOIN services s ON d.service_id = s.id 
                                       JOIN users u ON d.user_id = u.id 
                                       WHERE d.id = ?");
            $stmt->execute([$document_id]);
            $document = $stmt->fetch();
            
            if (!$document) {
                throw new Exception("Document not found");
            }
            
            // Convert price to UGX (assuming 1 USD = 3800 UGX)
            $amount_ugx = $document['price'] * 3800;
            
            // Initialize payment with MTN Mobile Money
            $payment_data = $this->initializeMTNPayment($phone_number, $amount_ugx, $document);
            
            // Save payment record
            $payment_id = $this->savePaymentRecord([
                'user_id' => $_SESSION['user_id'],
                'document_id' => $document_id,
                'amount' => $document['price'],
                'currency' => 'UGX',
                'payment_method' => 'mobile_money',
                'provider_id' => $provider_id,
                'provider_name' => 'MTN Mobile Money',
                'transaction_id' => $payment_data['transaction_id'],
                'provider_reference' => $payment_data['reference'],
                'status' => 'pending',
                'payment_details' => json_encode([
                    'phone_number' => $phone_number,
                    'amount_ugx' => $amount_ugx,
                    'api_response' => $payment_data
                ])
            ]);
            
            return [
                'success' => true,
                'payment_id' => $payment_id,
                'transaction_id' => $payment_data['transaction_id'],
                'reference' => $payment_data['reference'],
                'instructions' => $payment_data['instructions'] ?? null
            ];
            
        } catch (Exception $e) {
            error_log("Mobile Money Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Initialize MTN Mobile Money Payment
     */
    private function initializeMTNPayment($phone_number, $amount, $document) {
        $config = $this->config['mtn_momo'];
        
        // Prepare request data
        $request_data = [
            'amount' => $amount,
            'currency' => 'UGX',
            'externalId' => uniqid('KBMO_'),
            'payer' => [
                'partyIdType' => 'MSISDN',
                'partyId' => $this->formatPhoneNumber($phone_number)
            ],
            'payerMessage' => 'Payment for ' . $document['title'],
            'payeeNote' => 'KBMO Center - ' . $document['service_name']
        ];
        
        // Generate access token
        $token = $this->getMTNAccessToken();
        
        // Make API request to MTN Momo API
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['base_url'] . '/collection/v1_0/requesttopay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($request_data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'X-Reference-Id: ' . $request_data['externalId'],
                'X-Target-Environment: ' . $config['target_environment'],
                'Content-Type: application/json',
                'Ocp-Apim-Subscription-Key: ' . $config['subscription_key']
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 202) {
            // Payment request accepted
            return [
                'transaction_id' => $request_data['externalId'],
                'reference' => $request_data['externalId'],
                'instructions' => 'Check your phone to complete the payment'
            ];
        } else {
            $error_info = json_decode($response, true);
            throw new Exception("MTN Payment failed: " . ($error_info['message'] ?? 'Unknown error'));
        }
    }
    
    /**
     * Get MTN Access Token
     */
    private function getMTNAccessToken() {
        $config = $this->config['mtn_momo'];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['base_url'] . '/collection/token/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => $config['api_key'] . ':' . $config['subscription_key'],
            CURLOPT_HTTPHEADER => [
                'Ocp-Apim-Subscription-Key: ' . $config['subscription_key']
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $data = json_decode($response, true);
            return $data['access_token'];
        } else {
            throw new Exception("Failed to get MTN access token");
        }
    }
    
    /**
     * Initialize bank transfer payment
     */
    public function initializeBankTransferPayment($document_id, $provider_id = 3) {
        try {
            // Get document details
            $stmt = $this->pdo->prepare("SELECT d.*, s.price, u.full_name, u.email 
                                       FROM documents d 
                                       JOIN services s ON d.service_id = s.id 
                                       JOIN users u ON d.user_id = u.id 
                                       WHERE d.id = ?");
            $stmt->execute([$document_id]);
            $document = $stmt->fetch();
            
            if (!$document) {
                throw new Exception("Document not found");
            }
            
            // Convert price to UGX
            $amount_ugx = $document['price'] * 3800;
            
            // Generate unique transaction ID
            $transaction_id = 'BANK_' . time() . '_' . uniqid();
            
            // Save payment record
            $payment_id = $this->savePaymentRecord([
                'user_id' => $_SESSION['user_id'],
                'document_id' => $document_id,
                'amount' => $document['price'],
                'currency' => 'UGX',
                'payment_method' => 'bank_transfer',
                'provider_id' => $provider_id,
                'provider_name' => 'Centenary Bank',
                'transaction_id' => $transaction_id,
                'status' => 'pending',
                'payment_details' => json_encode([
                    'amount_ugx' => $amount_ugx,
                    'bank_details' => $this->config['bank_transfer']
                ])
            ]);
            
            return [
                'success' => true,
                'payment_id' => $payment_id,
                'transaction_id' => $transaction_id,
                'bank_details' => $this->config['bank_transfer'],
                'amount_ugx' => $amount_ugx
            ];
            
        } catch (Exception $e) {
            error_log("Bank Transfer Payment Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Save payment record to database
     */
    private function savePaymentRecord($data) {
        $stmt = $this->pdo->prepare("INSERT INTO payments 
            (user_id, document_id, amount, currency, payment_method, provider_id, provider_name, transaction_id, provider_reference, status, payment_details, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([
            $data['user_id'],
            $data['document_id'],
            $data['amount'],
            $data['currency'],
            $data['payment_method'],
            $data['provider_id'],
            $data['provider_name'],
            $data['transaction_id'],
            $data['provider_reference'] ?? null,
            $data['status'],
            $data['payment_details'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Check payment status
     */
    public function checkPaymentStatus($transaction_id) {
        $stmt = $this->pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
        $stmt->execute([$transaction_id]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            return ['success' => false, 'error' => 'Payment not found'];
        }
        
        // If payment is already completed, return status
        if ($payment['status'] === 'completed') {
            return ['success' => true, 'status' => 'completed', 'payment' => $payment];
        }
        
        // Check with payment provider for real-time status
        if ($payment['provider_name'] === 'MTN Mobile Money') {
            return $this->checkMTNPaymentStatus($payment);
        }
        
        return ['success' => true, 'status' => $payment['status'], 'payment' => $payment];
    }
    
    /**
     * Check MTN Payment Status
     */
    private function checkMTNPaymentStatus($payment) {
        $config = $this->config['mtn_momo'];
        $payment_details = json_decode($payment['payment_details'], true);
        
        $token = $this->getMTNAccessToken();
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $config['base_url'] . '/collection/v1_0/requesttopay/' . $payment['provider_reference'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $token,
                'X-Target-Environment: ' . $config['target_environment'],
                'Ocp-Apim-Subscription-Key: ' . $config['subscription_key']
            ]
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $status_data = json_decode($response, true);
            $status = $this->mapMTNStatus($status_data['status']);
            
            // Update payment status in database
            $this->updatePaymentStatus($payment['id'], $status);
            
            return ['success' => true, 'status' => $status, 'payment' => $payment];
        }
        
        return ['success' => true, 'status' => $payment['status'], 'payment' => $payment];
    }
    
    /**
     * Map MTN status to our system status
     */
    private function mapMTNStatus($mtn_status) {
        $status_map = [
            'SUCCESSFUL' => 'completed',
            'FAILED' => 'failed',
            'PENDING' => 'pending'
        ];
        
        return $status_map[$mtn_status] ?? 'pending';
    }
    
    /**
     * Update payment status
     */
    private function updatePaymentStatus($payment_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE payments SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $payment_id]);
        
        // If payment completed, update document status
        if ($status === 'completed') {
            $payment_stmt = $this->pdo->prepare("SELECT document_id FROM payments WHERE id = ?");
            $payment_stmt->execute([$payment_id]);
            $payment = $payment_stmt->fetch();
            
            if ($payment) {
                $doc_stmt = $this->pdo->prepare("UPDATE documents SET status = 'in_progress' WHERE id = ?");
                $doc_stmt->execute([$payment['document_id']]);
            }
        }
    }
    
    /**
     * Format phone number for MTN
     */
    private function formatPhoneNumber($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Ensure it starts with 256 for Uganda
        if (substr($phone, 0, 3) === '256') {
            return $phone;
        } elseif (substr($phone, 0, 1) === '0') {
            return '256' . substr($phone, 1);
        } elseif (substr($phone, 0, 3) === '254') {
            return $phone; // Kenya number
        } else {
            return '256' . $phone;
        }
    }
}
?>
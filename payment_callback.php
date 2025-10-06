<?php
require_once 'config/database.php';
require_once 'includes/PaymentService.php';

// This file handles payment callbacks from payment providers
$input = file_get_contents('php://input');
$data = json_decode($input, true);

error_log("Payment callback received: " . print_r($data, true));

if (isset($data['externalId'])) {
    $paymentService = new PaymentService($pdo);
    $status_result = $paymentService->checkPaymentStatus($data['externalId']);
    
    if ($status_result['success'] && $status_result['status'] === 'completed') {
        error_log("Payment completed for: " . $data['externalId']);
        // You can send email notifications here
    }
}

// Always return 200 OK to payment provider
http_response_code(200);
echo json_encode(['status' => 'ok']);
?>
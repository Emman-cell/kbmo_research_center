<?php
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect admin users to admin dashboard
if ($_SESSION['user_type'] == 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}

$pageTitle = "Payment - KBMO Center";
include 'includes/header.php';

// Check if document_id is provided
if (!isset($_GET['document_id']) || empty($_GET['document_id'])) {
    echo '<div class="container mt-4">
            <div class="alert alert-danger">
                <h4>Error</h4>
                <p>No document specified for payment.</p>
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
          </div>';
    include 'includes/footer.php';
    exit();
}

$document_id = intval($_GET['document_id']);

// Get document and service details
$stmt = $pdo->prepare("SELECT d.*, s.name as service_name, s.price, u.full_name, u.email 
                      FROM documents d 
                      JOIN services s ON d.service_id = s.id 
                      JOIN users u ON d.user_id = u.id 
                      WHERE d.id = ? AND d.user_id = ?");
$stmt->execute([$document_id, $_SESSION['user_id']]);
$document = $stmt->fetch();

if (!$document) {
    echo '<div class="container mt-4">
            <div class="alert alert-danger">
                <h4>Error</h4>
                <p>Document not found or you do not have permission to access it.</p>
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>
          </div>';
    include 'includes/footer.php';
    exit();
}

// Check if payment already exists
$payment_stmt = $pdo->prepare("SELECT * FROM payments WHERE document_id = ?");
$payment_stmt->execute([$document_id]);
$existing_payment = $payment_stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];
    $transaction_id = 'KBMO_' . time() . '_' . uniqid();
    
    // Validate payment method
    if (empty($payment_method)) {
        $error = "Please select a payment method.";
    } else {
        // Simulate payment processing
        $payment_status = 'completed'; // In real scenario, this would come from payment gateway
        
        try {
            $stmt = $pdo->prepare("INSERT INTO payments (user_id, document_id, amount, payment_method, transaction_id, status) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$_SESSION['user_id'], $document_id, $document['price'], $payment_method, $transaction_id, $payment_status])) {
                $success = "Payment completed successfully!";
                
                // Update document status to in_progress after payment
                $update_stmt = $pdo->prepare("UPDATE documents SET status = 'in_progress' WHERE id = ?");
                $update_stmt->execute([$document_id]);
                
                // Redirect to dashboard after 3 seconds
                //header("Refresh: 3; URL=dashboard.php");
            } else {
                $error = "Payment processing failed. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card dashboard-card">
                <div class="card-header bg-success text-white">
                    <h4 class="card-title mb-0"><i class="fas fa-credit-card me-2"></i>Complete Payment</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <p class="mb-0">Redirecting to dashboard...</p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($existing_payment): ?>
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Payment Already Processed</h5>
                            <p>Your payment for this document has already been completed.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Transaction ID:</strong><br><?php echo $existing_payment['transaction_id']; ?></p>
                                    <p><strong>Amount Paid:</strong><br>$<?php echo number_format($existing_payment['amount'], 2); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Payment Method:</strong><br><?php echo ucfirst(str_replace('_', ' ', $existing_payment['payment_method'])); ?></p>
                                    <p><strong>Status:</strong><br>
                                        <span class="status-badge <?php echo $existing_payment['status'] == 'completed' ? 'status-completed' : 'status-pending'; ?>">
                                            <?php echo ucfirst($existing_payment['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                        </div>
                    <?php else: ?>
                        <!-- Order Summary -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Document Title:</strong><br><?php echo htmlspecialchars($document['title']); ?></p>
                                        <p><strong>Service:</strong><br><?php echo htmlspecialchars($document['service_name']); ?></p>
                                        <p><strong>Customer:</strong><br><?php echo htmlspecialchars($document['full_name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Amount:</strong><br>$<?php echo number_format($document['price'], 2); ?></p>
                                        <p><strong>Order Date:</strong><br><?php echo date('F j, Y', strtotime($document['created_at'])); ?></p>
                                        <p><strong>Status:</strong><br>
                                            <span class="status-badge status-pending">Pending Payment</span>
                                        </p>
                                    </div>
                                </div>
                                <?php if (!empty($document['description'])): ?>
                                    <div class="mt-3">
                                        <strong>Requirements:</strong>
                                        <p class="text-muted"><?php echo htmlspecialchars($document['description']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Payment Form -->
                        <form method="POST" id="paymentForm">
                            <div class="mb-4">
                                <h5><i class="fas fa-wallet me-2"></i>Select Payment Method</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 mb-3 border rounded">
                                            <input class="form-check-input" type="radio" name="payment_method" id="mobile_money" value="mobile_money" checked>
                                            <label class="form-check-label" for="mobile_money">
                                                <i class="fas fa-mobile-alt fa-2x text-primary me-2"></i>
                                                <div>
                                                    <strong>Mobile Money</strong>
                                                    <p class="text-muted mb-0">Pay via MTN or Airtel Money</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check card p-3 mb-3 border rounded">
                                            <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                            <label class="form-check-label" for="bank_transfer">
                                                <i class="fas fa-university fa-2x text-primary me-2"></i>
                                                <div>
                                                    <strong>Bank Transfer</strong>
                                                    <p class="text-muted mb-0">Direct bank transfer</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Instructions -->
                            <div class="mb-4">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-info-circle me-2"></i>Payment Instructions</h6>
                                    <div id="mobile_instructions" class="payment-instructions">
                                        <p class="mb-2"><strong>Mobile Money Payments:</strong></p>
                                        <ol class="mb-2">
                                            <li>Go to your mobile money menu</li>
                                            <li>Select "Send Money"</li>
                                            <li>Enter phone number: <strong>0771200234</strong></li>
                                            <li>Enter amount: <strong>$<?php echo number_format($document['price'], 2); ?></strong></li>
                                            <li>Enter your name as reference</li>
                                        </ol>
                                    </div>
                                    <div id="bank_instructions" class="payment-instructions" style="display: none;">
                                        <p class="mb-2"><strong>Bank Transfer Details:</strong></p>
                                        <p class="mb-1">Bank: Centenary Bank</p>
                                        <p class="mb-1">Account Name: KBMO Center for Translational Research</p>
                                        <p class="mb-1">Account Number: 31003456789</p>
                                        <p class="mb-1">Branch: Gulu Main Branch</p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg py-3">
                                    <i class="fas fa-lock me-2"></i>Pay $<?php echo number_format($document['price'], 2); ?> Now
                                </button>
                            </div>
                            
                            <div class="text-center mt-3">
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel and Return to Dashboard</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide payment instructions based on selected method
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const mobileInstructions = document.getElementById('mobile_instructions');
    const bankInstructions = document.getElementById('bank_instructions');
    
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            if (this.value === 'mobile_money') {
                mobileInstructions.style.display = 'block';
                bankInstructions.style.display = 'none';
            } else if (this.value === 'bank_transfer') {
                mobileInstructions.style.display = 'none';
                bankInstructions.style.display = 'block';
            }
        });
    });
    
    // Form submission loading state
    const paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Payment...';
            submitBtn.disabled = true;
        });
    }
    
    // Log for debugging
    console.log('Payment page loaded for document ID:', <?php echo $document_id; ?>);
});
</script>

<?php include 'includes/footer.php'; ?>
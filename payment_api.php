<?php
require_once 'config/database.php';
require_once 'includes/Paymentservice.php';

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

// Initialize payment service
$paymentService = new PaymentService($pdo);

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
$stmt = $pdo->prepare("SELECT d.*, s.name as service_name, s.price, u.full_name, u.email, u.phone 
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
$payment_result = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = $_POST['payment_method'];

    if ($payment_method === 'mobile_money') {
        $phone_number = $_POST['phone_number'];

        if (empty($phone_number)) {
            $error = "Please enter your phone number for mobile money payment.";
        } else {
            // Initialize mobile money payment
            $payment_result = $paymentService->initializeMobileMoneyPayment($document_id, $phone_number);

            if ($payment_result['success']) {
                $success = "Payment request sent! Check your phone to complete the payment.";
            } else {
                $error = $payment_result['error'];
            }
        }
    } elseif ($payment_method === 'bank_transfer') {
        // Initialize bank transfer payment
        $payment_result = $paymentService->initializeBankTransferPayment($document_id);

        if ($payment_result['success']) {
            $success = "Bank transfer details generated successfully.";
        } else {
            $error = $payment_result['error'];
        }
    }
}

// Convert price to UGX
$amount_ugx = $document['price'] * 3800;
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
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
                            <?php if ($payment_result && isset($payment_result['transaction_id'])): ?>
                                <p class="mb-0">Transaction ID: <strong><?php echo $payment_result['transaction_id']; ?></strong></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($existing_payment && $existing_payment['status'] == 'completed'): ?>
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle me-2"></i>Payment Already Processed</h5>
                            <p>Your payment for this document has already been completed.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Transaction ID:</strong><br><?php echo $existing_payment['transaction_id']; ?></p>
                                    <p><strong>Amount Paid:</strong><br>UGX <?php echo number_format($existing_payment['amount'] * 3800, 0); ?> ($<?php echo number_format($existing_payment['amount'], 2); ?>)</p>
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
                                        <p><strong>Amount:</strong><br>UGX <?php echo number_format($amount_ugx, 0); ?> ($<?php echo number_format($document['price'], 2); ?>)</p>
                                        <p><strong>Order Date:</strong><br><?php echo date('F j, Y', strtotime($document['created_at'])); ?></p>
                                        <p><strong>Status:</strong><br>
                                            <span class="status-badge status-pending">Pending Payment</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Methods -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0"><i class="fas fa-wallet me-2"></i>Select Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" id="paymentForm">
                                    <div class="row">
                                        <!-- Mobile Money -->
                                        <div class="col-md-6 mb-4">
                                            <div class="payment-option card h-100">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" id="mobile_money" value="mobile_money" class="d-none" checked>
                                                    <label for="mobile_money" class="stretched-link cursor-pointer">
                                                        <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                                                        <h5>Mobile Money</h5>
                                                        <p class="text-muted">Pay via MTN Mobile Money</p>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Bank Transfer -->
                                        <div class="col-md-6 mb-4">
                                            <div class="payment-option card h-100">
                                                <div class="card-body text-center">
                                                    <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" class="d-none">
                                                    <label for="bank_transfer" class="stretched-link cursor-pointer">
                                                        <i class="fas fa-university fa-3x text-primary mb-3"></i>
                                                        <h5>Bank Transfer</h5>
                                                        <p class="text-muted">Direct bank transfer</p>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mobile Money Form -->
                                    <div id="mobile_money_form" class="payment-form">
                                        <div class="mb-3">
                                            <label for="phone_number" class="form-label">Phone Number</label>
                                            <div class="input-group">
                                                <span class="input-group-text">+256</span>
                                                <input type="tel" class="form-control" id="phone_number" name="phone_number"
                                                    value="<?php echo htmlspecialchars($document['phone'] ?? ''); ?>"
                                                    placeholder="77 123 4567" required>
                                            </div>
                                            <div class="form-text">Enter your MTN Mobile Money number</div>
                                        </div>

                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i>How to pay with Mobile Money:</h6>
                                            <ol class="mb-0">
                                                <li>Enter your MTN phone number above</li>
                                                <li>Click "Pay Now"</li>
                                                <li>Check your phone for a payment request</li>
                                                <li>Enter your Mobile Money PIN to complete payment</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <!-- Bank Transfer Form -->
                                    <div id="bank_transfer_form" class="payment-form" style="display: none;">
                                        <div class="alert alert-warning">
                                            <h6><i class="fas fa-university me-2"></i>Bank Transfer Details</h6>
                                            <p class="mb-1"><strong>Bank:</strong> Centenary Bank</p>
                                            <p class="mb-1"><strong>Account Name:</strong> KBMO Center for Translational Research</p>
                                            <p class="mb-1"><strong>Account Number:</strong> 31003456789</p>
                                            <p class="mb-1"><strong>Branch:</strong> Gulu Main Branch</p>
                                            <p class="mb-1"><strong>SWIFT Code:</strong> CBEUUGKA</p>
                                            <p class="mb-0"><strong>Amount:</strong> UGX <?php echo number_format($amount_ugx, 0); ?></p>
                                        </div>

                                        <div class="mb-3">
                                            <label for="reference" class="form-label">Payment Reference</label>
                                            <input type="text" class="form-control" id="reference"
                                                value="KBMO-<?php echo $document_id; ?>-<?php echo substr($_SESSION['username'], 0, 3); ?>"
                                                readonly>
                                            <div class="form-text">Use this reference when making the transfer</div>
                                        </div>

                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-info-circle me-2"></i>Instructions:</h6>
                                            <ol class="mb-0">
                                                <li>Transfer UGX <?php echo number_format($amount_ugx, 0); ?> to the bank account above</li>
                                                <li>Use the provided reference number</li>
                                                <li>Click "Confirm Bank Transfer" after making payment</li>
                                                <li>Your order will be processed once payment is verified</li>
                                            </ol>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 mt-4">
                                        <button type="submit" class="btn btn-success btn-lg py-3" id="submitBtn">
                                            <i class="fas fa-lock me-2"></i>
                                            <span id="submitText">Pay UGX <?php echo number_format($amount_ugx, 0); ?> Now</span>
                                        </button>
                                        <a href="dashboard.php" class="btn btn-outline-secondary">Cancel and Return to Dashboard</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-option {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .payment-option:hover {
        border-color: #3498db;
        transform: translateY(-2px);
    }

    .payment-option input:checked+label {
        background-color: #e8f4fd;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .payment-form {
        transition: all 0.3s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const mobileForm = document.getElementById('mobile_money_form');
        const bankForm = document.getElementById('bank_transfer_form');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const amountUGX = <?php echo $amount_ugx; ?>;

        // Format number with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Show/hide payment forms based on selection
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                if (this.value === 'mobile_money') {
                    mobileForm.style.display = 'block';
                    bankForm.style.display = 'none';
                    submitText.innerHTML = `Pay UGX ${formatNumber(amountUGX)} Now`;
                    submitBtn.querySelector('i').className = 'fas fa-lock me-2';
                } else if (this.value === 'bank_transfer') {
                    mobileForm.style.display = 'none';
                    bankForm.style.display = 'block';
                    submitText.innerHTML = 'Confirm Bank Transfer';
                    submitBtn.querySelector('i').className = 'fas fa-check-circle me-2';
                }
            });
        });

        // Highlight selected payment method
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                document.querySelectorAll('.payment-option').forEach(option => {
                    option.style.borderColor = 'transparent';
                    option.style.backgroundColor = '';
                });

                if (this.checked) {
                    this.closest('.payment-option').style.borderColor = '#3498db';
                    this.closest('.payment-option').style.backgroundColor = '#e8f4fd';
                }
            });
        });

        // Form submission loading state
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
                submitBtn.disabled = true;

                // Re-enable after 10 seconds if still processing
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            });
        }

        // Initialize first payment method as selected
        document.querySelector('input[name="payment_method"]:checked').dispatchEvent(new Event('change'));
    });
</script>

<?php include 'includes/footer.php'; ?>
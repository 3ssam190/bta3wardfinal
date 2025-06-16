<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

$orderId = $_GET['order_id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM Orders WHERE order_id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $conn->prepare("SELECT * FROM Payments WHERE order_id = ?");
$stmt2->execute([$orderId]);
$payment = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$order || !$payment) {
    header("Location: /");
    exit();
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Vodafone Cash Payment Instructions</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Important Instructions</h5>
                        <p>Please complete your payment within 24 hours to avoid order cancellation.</p>
                    </div>
                    
                    <div class="payment-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h5>Send Payment</h5>
                                <p>Transfer <strong><?= CURRENCY . number_format($payment['amount'], 2) ?></strong> to:</p>
                                <div class="vodafone-number-display">
                                    <span>01011960681</span>
                                    <button class="btn btn-sm btn-outline-success copy-btn" data-number="01011960681">
                                        <i class="fas fa-copy"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h5>Upload Proof</h5>
                                <p>Take a screenshot of the transaction and upload it below:</p>
                                
                                <form id="screenshotForm" action="upload_payment_proof.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                    
                                    <div class="mb-3">
                                        <label for="screenshot" class="form-label">Transaction Screenshot</label>
                                        <input class="form-control" type="file" id="screenshot" name="screenshot" accept="image/*" required>
                                        <small class="text-muted">Max 5MB (JPG, PNG)</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="transaction_number" class="form-label">Transaction Number (Optional)</label>
                                        <input type="text" class="form-control" id="transaction_number" name="transaction_number" placeholder="If available">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-upload"></i> Submit Proof
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h5>Order Processing</h5>
                                <p>Your order will be processed after we verify the payment (usually within 24 hours).</p>
                                <div class="countdown-timer">
                                    <small>Time remaining to submit proof:</small>
                                    <div class="timer" data-expiry="<?= $payment['verification_expiry'] ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Copy Vodafone number to clipboard
document.querySelectorAll('.copy-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const number = this.getAttribute('data-number');
        navigator.clipboard.writeText(number);
        this.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            this.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }, 2000);
    });
});

// Countdown timer
function updateCountdown() {
    const timer = document.querySelector('.timer');
    const expiry = new Date(timer.getAttribute('data-expiry')).getTime();
    const now = new Date().getTime();
    const distance = expiry - now;
    
    if (distance < 0) {
        timer.innerHTML = "EXPIRED";
        return;
    }
    
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    timer.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
}

setInterval(updateCountdown, 1000);
updateCountdown();
</script>

<style>
.vodafone-number-display {
    background: #f8f9fa;
    padding: 10px 15px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}

.vodafone-number-display span {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2e7d32;
}

.payment-steps {
    margin-top: 20px;
}

.step {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    position: relative;
}

.step:not(:last-child):after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -30px;
    width: 2px;
    background: #dee2e6;
}

.step-number {
    width: 40px;
    height: 40px;
    background: #2e7d32;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.step-content {
    flex: 1;
}

.countdown-timer {
    margin-top: 10px;
}

.timer {
    font-weight: bold;
    color: #2e7d32;
    font-size: 1.1rem;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
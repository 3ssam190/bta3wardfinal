<?php
// Set the page title
$pageTitle = "Terms and Conditions - [Your Plant Store Name]";

// Include the header (if you have one)
include('header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #2e8b57;
            text-align: center;
        }
        h2 {
            color: #3a9d5d;
            margin-top: 20px;
        }
        .last-updated {
            text-align: right;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Terms and Conditions</h1>
        <p>Welcome to <strong>[Your Plant Store Name]</strong>. By accessing or using our website and services, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before making a purchase.</p>

        <h2>1. General Terms</h2>
        <ul>
            <li>These terms apply to all purchases made through <strong>[Your Plant Store Name]</strong>.</li>
            <li>By placing an order, you confirm that you are at least 18 years old or have parental consent.</li>
            <li>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting.</li>
        </ul>

        <h2>2. Ordering & Payment</h2>
        <ul>
            <li>All orders are subject to product availability.</li>
            <li>Prices are listed in <strong>[Your Currency]</strong> and are subject to change without notice.</li>
            <li>We accept <strong>[List Payment Methods, e.g., Credit/Debit Cards, PayPal, etc.]</strong>.</li>
            <li>Payment must be completed before order processing begins.</li>
        </ul>

        <h2>3. Shipping & Delivery</h2>
        <ul>
            <li>Shipping costs and delivery times vary based on location and product type.</li>
            <li>We are not responsible for delays caused by shipping carriers or unforeseen circumstances (e.g., weather, customs).</li>
            <li>Risk of loss or damage passes to you upon delivery.</li>
        </ul>

        <h2>4. Returns & Refunds</h2>
        <ul>
            <li>Plants are living products; we do not accept returns unless they arrive damaged or dead.</li>
            <li>If your plant arrives damaged, contact us within <strong>[X] days</strong> with photos for a refund or replacement.</li>
            <li>Refunds will be processed to the original payment method within <strong>[X] business days</strong>.</li>
        </ul>

        <h2>5. Plant Care & Liability</h2>
        <ul>
            <li>We provide care instructions, but ultimate plant health depends on your environment and care.</li>
            <li><strong>[Your Plant Store Name]</strong> is not responsible for plants once they are in your care.</li>
        </ul>

        <h2>6. Privacy Policy</h2>
        <p>Your personal information is secure. See our <a href="privacy-policy.php">Privacy Policy</a> for details.</p>

        <h2>7. Intellectual Property</h2>
        <p>All content (images, text, logos) on our website is owned by <strong>[Your Plant Store Name]</strong> and protected by copyright.</p>

        <h2>8. Limitation of Liability</h2>
        <p>We are not liable for any indirect, incidental, or consequential damages arising from plant purchases.</p>

        <h2>9. Governing Law</h2>
        <p>These terms are governed by the laws of <strong>[Your Country/State]</strong>.</p>

        <h2>10. Contact Us</h2>
        <p>For questions, contact us at:</p>
        <ul>
            <li>📧 <strong><a href="mailto:your@email.com">your@email.com</a></strong></li>
            <li>📞 <strong>[Your Phone Number]</strong></li>
            <li>📍 <strong>[Your Business Address]</strong></li>
        </ul>

        <p>By using our website, you agree to these terms. Thank you for supporting <strong>[Your Plant Store Name]!</strong> 🌿</p>

        <p class="last-updated"><em>Last Updated: <?php echo date("F j, Y"); ?></em></p>
    </div>
</body>
</html>

<?php
// Include the footer (if you have one)
include('footer.php');
?>
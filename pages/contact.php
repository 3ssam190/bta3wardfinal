<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// If using Composer
require '../vendor/autoload.php';

// Set page title based on language
$pageTitle = $current_lang === 'ar' ? "اتصل بنا - GreenThumb Plants" : "Contact Us - GreenThumb Plants";
$pageDescription = $current_lang === 'ar' 
    ? "تواصل مع فريق GreenThumb Plants للاستفسارات أو الدعم الفني أو أي أسئلة أخرى." 
    : "Contact GreenThumb Plants team for inquiries, support, or any other questions.";
$pageKeywords = $current_lang === 'ar' 
    ? "اتصال, دعم, استفسارات, GreenThumb Plants" 
    : "contact, support, inquiries, GreenThumb Plants";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_UNSAFE_RAW);
    $message = filter_input(INPUT_POST, 'message', FILTER_UNSAFE_RAW);
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = $current_lang === 'ar' ? 'الرجاء إدخال اسمك' : 'Please enter your name';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = $current_lang === 'ar' ? 'الرجاء إدخال بريد إلكتروني صحيح' : 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $errors['subject'] = $current_lang === 'ar' ? 'الرجاء إدخال الموضوع' : 'Please enter a subject';
    }
    
    if (empty($message)) {
        $errors['message'] = $current_lang === 'ar' ? 'الرجاء إدخال رسالتك' : 'Please enter your message';
    }
    
    // If no errors, process the form            
    if (empty($errors)) {
        try {
            // Save to database
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
            
            // Send email notification
            $mail = new PHPMailer();
            
            $mail->isSMTP();
            $mail->Host = 'smtp.titan.email';
            $mail->SMTPAuth = true;
            $mail->Username = 'Support@bta3ward.shop';
            $mail->Password = 'ESAM123esam@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            
            $mail->setFrom($email, $name);
            $mail->setFrom('Support@bta3ward.shop', $current_lang === 'ar' ? 'بتاع ورد' : 'Bta3 Ward');
            
            $mail->isHTML(true);
            $mail->Subject = $current_lang === 'ar' 
                ? "إرسال نموذج اتصال جديد: $subject" 
                : "New Contact Form Submission: $subject";
            $mail->Body = $current_lang === 'ar'
                ? "<h2>رسالة اتصال جديدة</h2>
                   <p><strong>الاسم:</strong> $name</p>
                   <p><strong>البريد الإلكتروني:</strong> $email</p>
                   <p><strong>الموضوع:</strong> $subject</p>
                   <p><strong>الرسالة:</strong></p>
                   <p>$message</p>"
                : "<h2>New Contact Message</h2>
                   <p><strong>Name:</strong> $name</p>
                   <p><strong>Email:</strong> $email</p>
                   <p><strong>Subject:</strong> $subject</p>
                   <p><strong>Message:</strong></p>
                   <p>$message</p>";
            
            $mail->send();
            
            $_SESSION['success'] = $current_lang === 'ar' 
                ? "شكرًا لك على رسالتك! سنتواصل معك قريبًا." 
                : "Thank you for your message! We'll get back to you soon.";
            header("Location: contact.php");
            exit();
            
        } catch (Exception $e) {
            $errors['general'] = $current_lang === 'ar' 
                ? "حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى لاحقًا." 
                : "There was an error sending your message. Please try again later.";
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}
?>

<!-- Animated Background -->
<div class="page-background">
    <div class="circle-animation circle-1"></div>
    <div class="circle-animation circle-2"></div>
    <div class="circle-animation circle-3"></div>
</div>

<section class="contact-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6">
                <div class="contact-info" data-aos="fade-right">
                    <h1 class="contact-title"><?php echo $current_lang === 'ar' ? 'تواصل معنا' : 'Get In Touch'; ?></h1>
                    <p class="contact-subtitle">
                        <?php echo $current_lang === 'ar' 
                            ? 'نحن نتطلع لسماع منك! تواصل معنا للاستفسارات أو الملاحظات أو فقط لتحية.' 
                            : 'We\'d love to hear from you! Reach out with questions, feedback, or just to say hello.'; ?>
                    </p>
                    
                    <div class="contact-methods">
                        <div class="contact-method" data-aos="fade-up" data-aos-delay="100">
                        </div>
                        
                        <div class="contact-method" data-aos="fade-up" data-aos-delay="200">
                            <div class="method-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div class="method-details">
                                <h3><?php echo $current_lang === 'ar' ? 'اتصل بنا' : 'Call Us'; ?></h3>
                                <p>+20 01011960681</p>
                            </div>
                        </div>
                        
                        <div class="contact-method" data-aos="fade-up" data-aos-delay="300">
                            <div class="method-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="method-details">
                                <h3><?php echo $current_lang === 'ar' ? 'راسلنا' : 'Email Us'; ?></h3>
                                <p>support@bta3ward.shop</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="social-links" data-aos="fade-up" data-aos-delay="400">
                        <h3><?php echo $current_lang === 'ar' ? 'تابعنا' : 'Follow Us'; ?></h3>
                        <div class="social-icons">
                            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
                            <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-form-container" data-aos="fade-left">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo $current_lang === 'ar' ? 'إغلاق' : 'Close'; ?>"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($errors['general'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $errors['general']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="<?php echo $current_lang === 'ar' ? 'إغلاق' : 'Close'; ?>"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="contactForm" class="needs-validation" novalidate>
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                                   id="name" name="name" placeholder="<?php echo $current_lang === 'ar' ? 'اسمك' : 'Your Name'; ?>" required
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            <label for="name"><?php echo $current_lang === 'ar' ? 'اسمك' : 'Your Name'; ?></label>
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['name']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                   id="email" name="email" placeholder="<?php echo $current_lang === 'ar' ? 'بريدك الإلكتروني' : 'Your Email'; ?>" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            <label for="email"><?php echo $current_lang === 'ar' ? 'بريدك الإلكتروني' : 'Your Email'; ?></label>
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['email']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control <?php echo isset($errors['subject']) ? 'is-invalid' : ''; ?>" 
                                   id="subject" name="subject" placeholder="<?php echo $current_lang === 'ar' ? 'الموضوع' : 'Subject'; ?>" required
                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">
                            <label for="subject"><?php echo $current_lang === 'ar' ? 'الموضوع' : 'Subject'; ?></label>
                            <?php if (isset($errors['subject'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['subject']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label"><?php echo $current_lang === 'ar' ? 'رسالتك' : 'Your Message'; ?></label>
                            <textarea class="form-control <?php echo isset($errors['message']) ? 'is-invalid' : ''; ?>" 
                                      id="message" name="message" rows="5" required
                                      placeholder="<?php echo $current_lang === 'ar' ? 'اكتب رسالتك هنا...' : 'Type your message here...'; ?>"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                                <div class="invalid-feedback">
                                    <?php echo $errors['message']; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-send">
                                <span class="btn-text"><?php echo $current_lang === 'ar' ? 'إرسال الرسالة' : 'Send Message'; ?></span>
                                <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CSS Styles -->
<style>
/* Base Styles */
.contact-section {
    position: relative;
    padding: 5rem 0;
    overflow: hidden;
    z-index: 1;
}

/* Contact Info */
.contact-info {
    padding: 2rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.contact-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #2e7d32;
}

.contact-subtitle {
    font-size: 1.25rem;
    color: #6c757d;
    margin-bottom: 3rem;
}

.contact-methods {
    margin-bottom: 3rem;
}

.contact-method {
    display: flex;
    align-items: flex-start;
    margin-bottom: 2rem;
    transition: transform 0.3s ease;
}

.contact-method:hover {
    transform: translateX(<?php echo $current_lang === 'ar' ? '-10px' : '10px'; ?>);
}

.method-icon {
    width: 50px;
    height: 50px;
    background: #e8f5e9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-<?php echo $current_lang === 'ar' ? 'left' : 'right'; ?>: 1.5rem;
    color: #2e7d32;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.method-details h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: #2e7d32;
}

.method-details p {
    color: #6c757d;
    margin-bottom: 0.25rem;
}

/* Social Links */
.social-links h3 {
    font-size: 1.25rem;
    margin-bottom: 1rem;
    color: #2e7d32;
}

.social-icons {
    display: flex;
    gap: 15px;
}

.social-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e8f5e9;
    color: #2e7d32;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background: #2e7d32;
    color: white;
    transform: translateY(-5px);
}

/* Contact Form */
.contact-form-container {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    height: 100%;
}

.form-floating label {
    color: #6c757d;
}

.form-control {
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid #dee2e6;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #81c784;
    box-shadow: 0 0 0 0.25rem rgba(129, 199, 132, 0.25);
}

.btn-send {
    position: relative;
    padding: 0.75rem 2.5rem;
    border-radius: 50px;
    font-weight: 600;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-send:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(46, 125, 50, 0.2);
}

/* Map Section */
.map-section {
    margin-top: 5rem;
}

.map-container {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.map-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: transparent;
    z-index: 1;
    cursor: pointer;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 2rem;
    text-align: center;
    color: #2e7d32;
    position: relative;
}

.section-title::after {
    content: '';
    display: block;
    width: 80px;
    height: 4px;
    background: #81c784;
    margin: 1rem auto 0;
}

/* Animated Background */
.page-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    overflow: hidden;
}

.circle-animation {
    position: absolute;
    border-radius: 50%;
    background: rgba(46, 125, 50, 0.05);
    filter: blur(60px);
    animation: float 15s infinite ease-in-out;
}

.circle-1 {
    width: 300px;
    height: 300px;
    top: 10%;
    <?php echo $current_lang === 'ar' ? 'right' : 'left'; ?>: 5%;
    animation-delay: 0s;
}

.circle-2 {
    width: 400px;
    height: 400px;
    bottom: 15%;
    <?php echo $current_lang === 'ar' ? 'left' : 'right'; ?>: 10%;
    animation-delay: 3s;
}

.circle-3 {
    width: 250px;
    height: 250px;
    top: 60%;
    <?php echo $current_lang === 'ar' ? 'right' : 'left'; ?>: 30%;
    animation-delay: 6s;
}

@keyframes float {
    0%, 100% { transform: translateY(0) translateX(0); }
    50% { transform: translateY(-50px) translateX(<?php echo $current_lang === 'ar' ? '-50px' : '50px'; ?>); }
}

/* RTL Specific Styles */
[dir="rtl"] .contact-method {
    flex-direction: row-reverse;
}

[dir="rtl"] .method-icon {
    margin-right: 0;
    margin-left: 1.5rem;
}

[dir="rtl"] .form-floating > label {
    right: 1.5rem;
    left: auto;
}

[dir="rtl"] .form-floating > .form-control {
    padding-right: 1.5rem;
    padding-left: 0.5rem;
}

[dir="rtl"] .form-floating > .form-control-plaintext ~ label,
[dir="rtl"] .form-floating > .form-control:focus ~ label,
[dir="rtl"] .form-floating > .form-control:not(:placeholder-shown) ~ label,
[dir="rtl"] .form-floating > .form-select ~ label {
    transform: scale(0.85) translateY(-0.5rem) translateX(1.5rem);
}

/* Responsive Styles */
@media (max-width: 992px) {
    .contact-info {
        padding: 2rem 0;
    }
    
    .contact-title {
        font-size: 2.5rem;
    }
    
    .contact-method {
        margin-bottom: 1.5rem;
    }
}

@media (max-width: 768px) {
    .contact-section {
        padding: 3rem 0;
    }
    
    .contact-title {
        font-size: 2rem;
    }
    
    .contact-subtitle {
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }
    
    .method-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
        margin-<?php echo $current_lang === 'ar' ? 'left' : 'right'; ?>: 1rem;
    }
    
    .method-details h3 {
        font-size: 1.1rem;
    }
    
    .contact-form-container {
        padding: 1.5rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .contact-title {
        font-size: 1.8rem;
    }
    
    .social-icons {
        justify-content: center;
    }
    
    .map-section {
        margin-top: 3rem;
    }
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const btnText = submitBtn.querySelector('.btn-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            if (this.checkValidity()) {
                // Show loading state
                btnText.classList.add('d-none');
                spinner.classList.remove('d-none');
                submitBtn.disabled = true;
            } else {
                e.preventDefault();
                e.stopPropagation();
            }
            
            this.classList.add('was-validated');
        });
    }
    
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }
    
    // Remove map overlay on click
    const mapOverlay = document.querySelector('.map-overlay');
    if (mapOverlay) {
        mapOverlay.addEventListener('click', function() {
            this.style.pointerEvents = 'none';
            this.style.opacity = '0';
            setTimeout(() => {
                this.style.display = 'none';
            }, 300);
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Set page title and descriptions based on language
if ($current_lang === 'ar') {
    $pageTitle = "الشروط والأحكام - Bta3 Ward Store";
    $pageDescription = "اقرأ الشروط والأحكام الخاصة بمتجر بتاع ورد لمعرفة سياساتنا المتعلقة بالشراء، الشحن، المرتجعات، والخصوصية.";
    $pageKeywords = "شروط متجر النباتات, سياسات بتاع ورد, شروط الشراء, سياسة الخصوصية";
} else {
    $pageTitle = "Terms and Conditions - Bta3 Ward Store";
    $pageDescription = "Read Bta3 Ward Store's terms and conditions to understand our policies regarding purchases, shipping, returns, and privacy.";
    $pageKeywords = "plant store terms, Bta3 Ward policies, purchase terms, privacy policy";
}
?>

<div class="container terms-page" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
    <h1><?php echo $current_lang === 'ar' ? 'الشروط والأحكام' : 'Terms and Conditions'; ?></h1>
    
    <div class="last-updated">
        <p><?php echo $current_lang === 'ar' ? 'آخر تحديث: 14 يونيو 2025' : 'Last Updated: June 14, 2025'; ?></p>
    </div>
    
    <div class="terms-intro">
        <p>
            <?php echo $current_lang === 'ar' 
                ? 'يرجى قراءة هذه الشروط والأحكام بعناية قبل استخدام موقع بتاع ورد أو إجراء أي عمليات شراء. باستخدام موقعنا أو الخدمات، فإنك توافق على الالتزام بهذه الشروط.' 
                : 'Please read these Terms and Conditions carefully before using the Bta3 Ward website or making any purchases. By using our site or services, you agree to be bound by these terms.'; ?>
        </p>
    </div>
    
    <div class="terms-section">
        <h2><?php echo $current_lang === 'ar' ? '1. الطلبات والدفع' : '1. Orders & Payment'; ?></h2>
        
        <div class="terms-content">
            <h3><?php echo $current_lang === 'ar' ? 'تأكيد الطلب' : 'Order Confirmation'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'سيتم تأكيد طلبك عبر البريد الإلكتروني بمجرد استلامه. هذا التأكيد لا يشكل قبولًا للطلب، ولكنه مجرد اعتراف باستلامه. يتم قبول الطلب فقط عند الشحن.' 
                : 'Your order will be confirmed via email once received. This confirmation does not constitute acceptance of the order but merely acknowledges receipt. The order is only accepted upon shipment.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'الأسعار والضرائب' : 'Pricing & Taxes'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'جميع الأسعار بالجنيه المصري وتشمل الضرائب المطبقة. نحتفظ بالحق في تعديل الأسعار في أي وقت دون إشعار مسبق.' 
                : 'All prices are in EGP and include applicable taxes. We reserve the right to adjust prices at any time without prior notice.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'طرق الدفع' : 'Payment Methods'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نقبل الدفع عن طريق بطاقات الائتمان، فودافون كاش، والدفع عند الاستلام (في بعض المناطق). يجب التحقق من جميع المدفوعات قبل الشحن.' 
                : 'We accept payment via credit cards, Vodafone Cash, and cash on delivery (in some areas). All payments must be verified before shipping.'; ?></p>
        </div>
    </div>
    
    <div class="terms-section">
        <h2><?php echo $current_lang === 'ar' ? '2. الشحن والتسليم' : '2. Shipping & Delivery'; ?></h2>
        
        <div class="terms-content">
            <h3><?php echo $current_lang === 'ar' ? 'أوقات الشحن' : 'Shipping Times'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نحن نبذل قصارى جهدنا لتلبية أوقات الشحن المقدرة، ولكن هذه الأوقات تقديرية وقد تختلف حسب الظروف الموسمية والجغرافية.' 
                : 'We make every effort to meet estimated shipping times, but these are approximate and may vary due to seasonal and geographic circumstances.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'تسليم النباتات' : 'Plant Delivery'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'يتم شحن النباتات بعناية فائقة. إذا وصل نباتك تالفًا، يرجى الاتصال بنا خلال 48 ساعة مع صور للضرر.' 
                : 'Plants are shipped with extreme care. If your plant arrives damaged, please contact us within 48 hours with photos of the damage.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'المناطق الجغرافية' : 'Geographic Areas'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نحن نقدم الشحن إلى جميع محافظات مصر. قد تختلف رسوم الشحن حسب الموقع.' 
                : 'We ship to all governorates in Egypt. Shipping fees may vary by location.'; ?></p>
        </div>
    </div>
    
    <div class="terms-section">
        <h2><?php echo $current_lang === 'ar' ? '3. المرتجعات والاستبدال' : '3. Returns & Exchanges'; ?></h2>
        
        <div class="terms-content">
            <h3><?php echo $current_lang === 'ar' ? 'ضمان النباتات' : 'Plant Guarantee'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نحن نضمن جميع النباتات لمدة 14 يومًا من تاريخ التسليم. لا يشمل الضمان الأضرار الناتجة عن سوء الرعاية أو الظروف البيئية.' 
                : 'We guarantee all plants for 14 days from delivery date. The guarantee does not cover damage from improper care or environmental conditions.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'إجراءات الإرجاع' : 'Return Procedures'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'يجب إخطارنا بالرغبة في الإرجاع خلال 3 أيام من الاستلام. يجب أن تكون النباتات المرتجعة في حالتها الأصلية مع جميع العلامات.' 
                : 'You must notify us of your intent to return within 3 days of receipt. Returned plants must be in their original condition with all tags.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'المبالغ المستردة' : 'Refunds'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'سيتم معالجة المبالغ المستردة في غضون 7-10 أيام عمل باستخدام طريقة الدفع الأصلية. لا تشمل المبالغ المستردة تكاليف الشحن الأصلية.' 
                : 'Refunds will be processed within 7-10 business days using the original payment method. Refunds do not include original shipping costs.'; ?></p>
        </div>
    </div>
    
    <div class="terms-section">
        <h2><?php echo $current_lang === 'ar' ? '4. الخصوصية والأمان' : '4. Privacy & Security'; ?></h2>
        
        <div class="terms-content">
            <h3><?php echo $current_lang === 'ar' ? 'جمع المعلومات' : 'Information Collection'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نجمع المعلومات اللازمة لمعالجة طلباتك وتحسين تجربتك. لن نبيع أو نشارك معلوماتك مع أطراف ثالثة دون موافقتك.' 
                : 'We collect information necessary to process your orders and improve your experience. We will not sell or share your information with third parties without your consent.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'أمان الدفع' : 'Payment Security'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نستخدم تقنيات تشفير متقدمة لحماية معلومات الدفع الخاصة بك. لا نخزن تفاصيل بطاقة الائتمان على خوادمنا.' 
                : 'We use advanced encryption technologies to protect your payment information. We do not store credit card details on our servers.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'الكوكيز' : 'Cookies'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'نستخدم ملفات تعريف الارتباط لتحسين تجربة التصفح الخاصة بك. يمكنك تعطيل ملفات تعريف الارتباط في إعدادات المتصفح الخاص بك.' 
                : 'We use cookies to enhance your browsing experience. You may disable cookies in your browser settings.'; ?></p>
        </div>
    </div>
    
    <div class="terms-section">
        <h2><?php echo $current_lang === 'ar' ? '5. الملكية الفكرية' : '5. Intellectual Property'; ?></h2>
        
        <div class="terms-content">
            <h3><?php echo $current_lang === 'ar' ? 'حقوق النشر' : 'Copyright'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'جميع المحتويات على هذا الموقع، بما في ذلك النصوص والصور والتصاميم، هي ملك لـ بتاع ورد ويحظر نسخها أو توزيعها دون إذن.' 
                : 'All content on this site, including text, images, and designs, are property of Bta3 Ward and may not be copied or distributed without permission.'; ?></p>
            
            <h3><?php echo $current_lang === 'ar' ? 'العلامات التجارية' : 'Trademarks'; ?></h3>
            <p><?php echo $current_lang === 'ar' 
                ? 'شعار بتاع ورد واسم المتجر علامتان تجاريتان مسجلتان. أي استخدام غير مصرح به محظور.' 
                : 'The Bta3 Ward logo and store name are registered trademarks. Any unauthorized use is prohibited.'; ?></p>
        </div>
    </div>
    
    <div class="terms-section">
        <h2><?php echo $current_lang === 'ar' ? '6. التعديلات على الشروط' : '6. Changes to Terms'; ?></h2>
        
        <div class="terms-content">
            <p><?php echo $current_lang === 'ar' 
                ? 'نحتفظ بالحق في تعديل هذه الشروط والأحكام في أي وقت. سيتم نشر أي تغييرات على هذه الصفحة وسيكون تاريخ التحديث في الأعلى.' 
                : 'We reserve the right to modify these Terms and Conditions at any time. Any changes will be posted on this page with an updated revision date.'; ?></p>
        </div>
    </div>
    
    <div class="terms-contact">
        <h3><?php echo $current_lang === 'ar' ? 'اتصل بنا' : 'Contact Us'; ?></h3>
        <p><?php echo $current_lang === 'ar' 
            ? 'إذا كانت لديك أي أسئلة حول هذه الشروط والأحكام، يرجى الاتصال بنا:' 
            : 'If you have any questions about these Terms and Conditions, please contact us:'; ?></p>
        <ul>
            <li><?php echo $current_lang === 'ar' ? 'البريد الإلكتروني: info@bta3ward.shop' : 'Email: info@bta3ward.shop'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'الهاتف: 01095327020' : 'Phone: 01095327020'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'ساعات العمل: الأحد إلى الخميس، 9 صباحًا إلى 5 مساءً' : 'Hours: Sunday to Thursday, 9am to 5pm'; ?></li>
        </ul>
    </div>
</div>

<style>
.terms-page {
    padding: 2rem 0;
    max-width: 900px;
    margin: 0 auto;
    line-height: 1.6;
}

.last-updated {
    text-align: right;
    font-style: italic;
    color: #666;
    margin-bottom: 1rem;
}

.terms-intro {
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.terms-section {
    margin-bottom: 2.5rem;
}

.terms-section h2 {
    color: #2c7a4a;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
}

.terms-content {
    padding: 0 1rem;
}

.terms-content h3 {
    color: #3a3a3a;
    margin: 1.5rem 0 0.5rem 0;
}

.terms-contact {
    margin-top: 3rem;
    padding: 1.5rem;
    background-color: #f1f8e9;
    border-radius: 5px;
}

.terms-contact h3 {
    color: #2c7a4a;
    margin-bottom: 1rem;
}

/* RTL specific styles */
[dir="rtl"] .last-updated {
    text-align: left;
}

[dir="rtl"] .terms-content {
    padding: 0 1rem;
}

[dir="rtl"] .terms-section h2,
[dir="rtl"] .terms-content h3,
[dir="rtl"] .terms-contact h3 {
    text-align: right;
}

[dir="rtl"] .terms-contact ul {
    padding-right: 1.5rem;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
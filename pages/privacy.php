<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Set page title and descriptions based on language
if ($current_lang === 'ar') {
    $pageTitle = "سياسة الخصوصية - GreenThumb Plants";
    $pageDescription = "تعرف على كيفية جمعنا واستخدامنا وحمايتنا لبياناتك الشخصية في Bta3 Ward Store.";
    $pageKeywords = "سياسة الخصوصية, حماية البيانات, معلومات شخصية, Bta3 Ward Store";
} else {
    $pageTitle = "Privacy Policy - GreenThumb Plants";
    $pageDescription = "Learn how we collect, use, and protect your personal data at Bta3 Ward Store.";
    $pageKeywords = "privacy policy, data protection, personal information, Bta3 Ward Store";
}
?>

<div class="container privacy-page" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
    <h1><?php echo $current_lang === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy'; ?></h1>
    
    <div class="last-updated">
        <?php echo $current_lang === 'ar' 
            ? 'آخر تحديث: ' . date('Y/m/d') 
            : 'Last Updated: ' . date('F j, Y'); ?>
    </div>
    
    <div class="privacy-intro">
        <p>
            <?php echo $current_lang === 'ar' 
                ? 'في Bta3 Ward Store، نحن نأخذ خصوصيتك على محمل الجد. توضح سياسة الخصوصية هذه كيفية جمعنا واستخدامنا وحمايتنا لمعلوماتك الشخصية عندما تزور موقعنا الإلكتروني أو تستخدم خدماتنا.'
                : 'At Bta3 Ward Store, we take your privacy seriously. This Privacy Policy explains how we collect, use, and protect your personal information when you visit our website or use our services.'; ?>
        </p>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'المعلومات التي نجمعها' : 'Information We Collect'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'قد نجمع الأنواع التالية من المعلومات:'
            : 'We may collect the following types of information:'; ?></p>
            
        <ul>
            <li>
                <strong><?php echo $current_lang === 'ar' ? 'معلومات شخصية:' : 'Personal Information:'; ?></strong> 
                <?php echo $current_lang === 'ar' 
                    ? 'الاسم، عنوان البريد الإلكتروني، عنوان الشحن، رقم الهاتف، ومعلومات الدفع عند إجراء عملية شراء.'
                    : 'Name, email address, shipping address, phone number, and payment information when you make a purchase.'; ?>
            </li>
            <li>
                <strong><?php echo $current_lang === 'ar' ? 'معلومات الحساب:' : 'Account Information:'; ?></strong> 
                <?php echo $current_lang === 'ar' 
                    ? 'اسم المستخدم وكلمة المرور وتفضيلاتك عند إنشاء حساب.'
                    : 'Username, password, and your preferences when you create an account.'; ?>
            </li>
            <li>
                <strong><?php echo $current_lang === 'ar' ? 'بيانات التصفح:' : 'Browsing Data:'; ?></strong> 
                <?php echo $current_lang === 'ar' 
                    ? 'عنوان IP، نوع المتصفح، صفحات الزيارة، وقت ومدة الزيارات.'
                    : 'IP address, browser type, pages visited, time and duration of visits.'; ?>
            </li>
            <li>
                <strong><?php echo $current_lang === 'ar' ? 'ملفات تعريف الارتباط:' : 'Cookies:'; ?></strong> 
                <?php echo $current_lang === 'ar' 
                    ? 'نستخدم ملفات تعريف الارتباط لتحسين تجربتك وتذكر تفضيلاتك.'
                    : 'We use cookies to enhance your experience and remember your preferences.'; ?>
            </li>
        </ul>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'كيف نستخدم معلوماتك' : 'How We Use Your Information'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'نستخدم المعلومات التي نجمعها للأغراض التالية:'
            : 'We use the information we collect for the following purposes:'; ?></p>
            
        <ul>
            <li><?php echo $current_lang === 'ar' ? 'معالجة الطلبات وإتمام المعاملات' : 'To process orders and complete transactions'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'تحسين تجربة المستخدم على موقعنا' : 'To improve your user experience on our website'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'الرد على استفساراتك وطلباتك' : 'To respond to your inquiries and requests'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'إرسال تحديثات وعروض ترويجية (إذا وافقت على ذلك)' : 'To send updates and promotional offers (if you opt-in)'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'تحليل استخدام الموقع لتحسين خدماتنا' : 'To analyze website usage to improve our services'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'منع الاحتيال وحماية أمن موقعنا' : 'To prevent fraud and protect the security of our website'; ?></li>
        </ul>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'مشاركة المعلومات' : 'Sharing Information'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'لا نبيع أو نؤجر معلوماتك الشخصية إلى أطراف ثالثة. قد نشارك معلوماتك مع:'
            : 'We do not sell or rent your personal information to third parties. We may share your information with:'; ?></p>
            
        <ul>
            <li>
                <strong><?php echo $current_lang === 'ar' ? 'مقدمي الخدمات:' : 'Service Providers:'; ?></strong> 
                <?php echo $current_lang === 'ar' 
                    ? 'شركات الشحن ومعالجة الدفع التي تساعدنا في تشغيل أعمالنا.'
                    : 'Shipping and payment processing companies that help us operate our business.'; ?>
            </li>
            <li>
                <strong><?php echo $current_lang === 'ar' ? 'الامتثال القانوني:' : 'Legal Compliance:'; ?></strong> 
                <?php echo $current_lang === 'ar' 
                    ? 'عندما نعتقد أن الكشف مطلوب بموجب القانون أو لحماية حقوقنا.'
                    : 'When we believe disclosure is required by law or to protect our rights.'; ?>
            </li>
        </ul>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'حماية البيانات' : 'Data Security'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'نحن نستخدم تدابير أمنية معقولة لحماية معلوماتك الشخصية من الوصول غير المصرح به أو الكشف أو التغيير أو التدمير. ومع ذلك، لا يوجد نظام آمن بنسبة 100٪، لذلك لا يمكننا ضمان الأمان المطلق.'
            : 'We use reasonable security measures to protect your personal information from unauthorized access, disclosure, alteration, or destruction. However, no system is 100% secure, so we cannot guarantee absolute security.'; ?></p>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'ملفات تعريف الارتباط (الكوكيز)' : 'Cookies'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'نستخدم ملفات تعريف الارتباط لتحسين تجربتك على موقعنا. يمكنك التحكم في ملفات تعريف الارتباط أو تعطيلها من خلال إعدادات المتصفح الخاصة بك، ولكن قد يؤثر ذلك على بعض وظائف الموقع.'
            : 'We use cookies to enhance your experience on our website. You can control or disable cookies through your browser settings, but this may affect some website functionality.'; ?></p>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'روابط لمواقع أخرى' : 'Links to Other Websites'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'قد يحتوي موقعنا على روابط لمواقع أخرى. نحن لسنا مسؤولين عن ممارسات الخصوصية أو المحتوى على تلك المواقع.'
            : 'Our website may contain links to other sites. We are not responsible for the privacy practices or content of those sites.'; ?></p>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'حقوقك' : 'Your Rights'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'لديك الحق في:'
            : 'You have the right to:'; ?></p>
            
        <ul>
            <li><?php echo $current_lang === 'ar' ? 'الوصول إلى معلوماتك الشخصية التي نحتفظ بها' : 'Access the personal information we hold about you'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'طلب تصحيح المعلومات غير الدقيقة' : 'Request correction of inaccurate information'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'طلب حذف بياناتك في ظروف معينة' : 'Request deletion of your data under certain circumstances'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'الاعتراض على معالجة بياناتك' : 'Object to processing of your data'; ?></li>
            <li><?php echo $current_lang === 'ar' ? 'طلب تقييد معالجة بياناتك' : 'Request restriction of processing your data'; ?></li>
        </ul>
        
        <p><?php echo $current_lang === 'ar' 
            ? 'للمطالبة بهذه الحقوق، يرجى الاتصال بنا باستخدام معلومات الاتصال أدناه.'
            : 'To exercise these rights, please contact us using the information below.'; ?></p>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'تغييرات على سياسة الخصوصية' : 'Changes to This Privacy Policy'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر. سننشر أي تغييرات على هذه الصفحة ونحدد تاريخ التحديث في الأعلى.'
            : 'We may update this Privacy Policy from time to time. We will post any changes on this page and update the revision date at the top.'; ?></p>
    </div>
    
    <div class="privacy-section">
        <h2><?php echo $current_lang === 'ar' ? 'اتصل بنا' : 'Contact Us'; ?></h2>
        <p><?php echo $current_lang === 'ar' 
            ? 'إذا كانت لديك أي أسئلة حول سياسة الخصوصية هذه أو ممارسات البيانات الخاصة بنا، يرجى الاتصال بنا:'
            : 'If you have any questions about this Privacy Policy or our data practices, please contact us:'; ?></p>
            
        <ul class="contact-info">
            <li><i class="fas fa-envelope"></i> <?php echo $current_lang === 'ar' ? 'البريد الإلكتروني:' : 'Email:'; ?> 
support@bta3ward.shop</li>
            <li><i class="fas fa-phone"></i> <?php echo $current_lang === 'ar' ? 'الهاتف:' : 'Phone:'; ?> +20 01011960681</li>
        </ul>
    </div>
</div>

<style>
.privacy-page {
    padding: 2rem 0;
    max-width: 900px;
    margin: 0 auto;
    line-height: 1.6;
}

.privacy-page h1 {
    color: #2c7a4a;
    margin-bottom: 1rem;
    text-align: center;
}

.last-updated {
    text-align: center;
    color: #666;
    margin-bottom: 2rem;
    font-style: italic;
}

.privacy-intro {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background-color: #f8f9fa;
    border-radius: 8px;
}

.privacy-section {
    margin-bottom: 2.5rem;
}

.privacy-section h2 {
    color: #2c7a4a;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e0e0e0;
}

.privacy-section ul {
    padding-left: 1.5rem;
}

.privacy-section li {
    margin-bottom: 0.5rem;
}

.contact-info {
    list-style: none;
    padding-left: 0;
}

.contact-info li {
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
}

.contact-info i {
    margin-right: 0.75rem;
    color: #2c7a4a;
    width: 20px;
    text-align: center;
}

/* RTL specific styles */
[dir="rtl"] .privacy-section ul {
    padding-right: 1.5rem;
    padding-left: 0;
}

[dir="rtl"] .contact-info i {
    margin-right: 0;
    margin-left: 0.75rem;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
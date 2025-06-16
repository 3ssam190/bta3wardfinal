<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Set page title and descriptions based on language
if ($current_lang === 'ar') {
    $pageTitle = "الأسئلة الشائعة - Bta3 Ward Store";
    $pageDescription = "ابحث عن إجابات للأسئلة الشائعة حول رعاية النباتات والشحن والمرتجعات والمزيد في Bta3 Ward Store.";
    $pageKeywords = "أسئلة النباتات الشائعة, أسئلة رعاية النباتات, مساعدة البستنة, أسئلة متجر النباتات";
} else {
    $pageTitle = "Frequently Asked Questions - Bta3 Ward Store";
    $pageDescription = "Find answers to common questions about plant care, shipping, returns, and more at Bta3 Ward Store.";
    $pageKeywords = "plant FAQs, plant care questions, gardening help, plant store questions";
}
?>

<div class="container faq-page" dir="<?php echo $current_lang === 'ar' ? 'rtl' : 'ltr'; ?>">
    <h1><?php echo $current_lang === 'ar' ? 'الأسئلة الشائعة' : 'Frequently Asked Questions'; ?></h1>
    
    <div class="faq-intro">
        <p>
            <?php echo $current_lang === 'ar' 
                ? 'هل لديك أسئلة حول نباتاتنا أو التوصيل أو تعليمات العناية؟ ستجد أدناه إجابات لأسئلتنا الأكثر شيوعًا. إذا لم تجد ما تبحث عنه، يرجى <a href="contact.php">الاتصال بنا</a>.' 
                : 'Have questions about our plants, delivery, or care instructions? Below you\'ll find answers to our most frequently asked questions. If you don\'t find what you\'re looking for, please <a href="contact.php">contact us</a>.'; ?>
        </p>
    </div>
    
    <div class="faq-category">
        <h2><?php echo $current_lang === 'ar' ? 'أسئلة العناية بالنباتات' : 'Plant Care Questions'; ?></h2>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'كم مرة يجب أن أسقي نباتاتي؟' : 'How often should I water my plants?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' ? 'تختلف احتياجات الري حسب نوع النبات. كقاعدة عامة:' : 'Watering needs vary by plant type. As a general rule:'; ?></p>
                <ul>
                    <li><strong><?php echo $current_lang === 'ar' ? 'النباتات العصارية والصبار:' : 'Succulents & Cacti:'; ?></strong> <?php echo $current_lang === 'ar' ? 'كل 2-3 أسابيع (اترك التربة تجف تمامًا بين الري)' : 'Every 2-3 weeks (let soil dry completely between waterings)'; ?></li>
                    <li><strong><?php echo $current_lang === 'ar' ? 'النباتات الاستوائية:' : 'Tropical Plants:'; ?></strong> <?php echo $current_lang === 'ar' ? 'مرة واحدة في الأسبوع أو عندما تكون البوصة العلوية من التربة جافة' : 'Once a week or when top inch of soil is dry'; ?></li>
                    <li><strong><?php echo $current_lang === 'ar' ? 'السراخس:' : 'Ferns:'; ?></strong> <?php echo $current_lang === 'ar' ? 'حافظ على التربة رطبة باستمرار ولكن ليست مشبعة بالماء' : 'Keep soil consistently moist but not soggy'; ?></li>
                </ul>
                <p><?php echo $current_lang === 'ar' ? 'كل نبات تشتريه يأتي مع تعليمات رعاية محددة.' : 'Each plant you purchase comes with specific care instructions.'; ?></p>
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'ما هو أفضل مكان لنباتي؟' : 'What\'s the best location for my plant?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' ? 'تختلف متطلبات الإضاءة:' : 'Light requirements vary:'; ?></p>
                <ul>
                    <li><strong><?php echo $current_lang === 'ar' ? 'نباتات الإضاءة المنخفضة:' : 'Low light plants:'; ?></strong> <?php echo $current_lang === 'ar' ? 'النوافذ المواجهة للشمال أو الغرف الداخلية (نباتات الثعبان، نباتات ZZ)' : 'North-facing windows or interior rooms (Snake Plants, ZZ Plants)'; ?></li>
                    <li><strong><?php echo $current_lang === 'ar' ? 'نباتات الإضاءة المتوسطة:' : 'Medium light plants:'; ?></strong> <?php echo $current_lang === 'ar' ? 'النوافذ المواجهة للشرق أو الغرب (نباتات البوثوس، الفيلوديندرون)' : 'East or West-facing windows (Pothos, Philodendrons)'; ?></li>
                    <li><strong><?php echo $current_lang === 'ar' ? 'نباتات الإضاءة الساطعة:' : 'Bright light plants:'; ?></strong> <?php echo $current_lang === 'ar' ? 'النوافذ المواجهة للجنوب (النباتات العصارية، الفيكس، أشجار الحمضيات)' : 'South-facing windows (Succulents, Ficus, Citrus Trees)'; ?></li>
                </ul>
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'كيف أعرف إذا كان نباتي يحصل على الكثير من الضوء أو القليل جدًا؟' : 'How do I know if my plant is getting too much or too little light?'; ?>
            </button>
            <div class="faq-answer">
                <p><strong><?php echo $current_lang === 'ar' ? 'علامات كثرة الضوء:' : 'Signs of too much light:'; ?></strong> <?php echo $current_lang === 'ar' ? 'تحول الأوراق إلى اللون الأصفر أو البني، بقع محروقة، تجعد الأوراق' : 'Leaves turning yellow or brown, scorched spots, leaves curling'; ?></p>
                <p><strong><?php echo $current_lang === 'ar' ? 'علامات قلة الضوء:' : 'Signs of too little light:'; ?></strong> <?php echo $current_lang === 'ar' ? 'نمو طويل وضعيف، أوراق جديدة صغيرة، ميل نحو مصدر الضوء، فقدان التلون' : 'Leggy growth, small new leaves, leaning toward light source, loss of variegation'; ?></p>
            </div>
        </div>
    </div>
    
    <div class="faq-category">
        <h2><?php echo $current_lang === 'ar' ? 'الشحن والتوصيل' : 'Shipping & Delivery'; ?></h2>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'كيف يتم تغليف النباتات للشحن؟' : 'How are plants packaged for shipping?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' ? 'نحن نحرص بشدة على تغليف نباتاتك:' : 'We take great care in packaging your plants:'; ?></p>
                <ul>
                    <li><?php echo $current_lang === 'ar' ? 'يتم تثبيت النباتات في أوانيها بمواد تغليف قابلة للتحلل' : 'Plants are secured in their pots with biodegradable packing material'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'يتم تغليف الأواني لمنع انسكاب التربة' : 'Pots are wrapped to prevent soil spillage'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'يتم حماية الأوراق الحساسة بورق ناعم أو أغلفة من الورق المقوى' : 'Delicate leaves are protected with tissue paper or cardboard sleeves'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'يتم تمييز الصناديق بوضوح على أنها نباتات حية' : 'Boxes are clearly marked as live plants'; ?></li>
                </ul>
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'كم يستغرق الشحن؟' : 'How long does shipping take?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' ? 'تختلف أوقات الشحن حسب الموقع والموسم:' : 'Shipping times vary by location and season:'; ?></p>
                <ul>
                    <li><strong><?php echo $current_lang === 'ar' ? 'التوصيل المحلي (ضمن 50 ميلاً):' : 'Local delivery (within 50 miles):'; ?></strong> <?php echo $current_lang === 'ar' ? '1-2 يوم عمل' : '1-2 business days'; ?></li>
                    <li><strong><?php echo $current_lang === 'ar' ? 'الشحن العادي:' : 'Standard shipping:'; ?></strong> <?php echo $current_lang === 'ar' ? '3-5 أيام عمل' : '3-5 business days'; ?></li>
                </ul>
                <p><?php echo $current_lang === 'ar' ? 'نتجنب الشحن خلال درجات الحرارة القصوى التي قد تضر بالنباتات.' : 'We avoid shipping during extreme temperatures that could harm plants.'; ?></p>
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'هل تقدمون خدمة الاستلام من المتجر؟' : 'Do you offer local pickup?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' 
                    ? 'للأسف بتاع ورد حتى الان ليس لديه اى فروع.'
                    : 'Not Yet Bta3 Ward Store Still Doesn\'t have any Physical location.'; ?></p>
            </div>
        </div>
    </div>
    
    <div class="faq-category">
        <h2><?php echo $current_lang === 'ar' ? 'المرتجعات والضمانات' : 'Returns & Guarantees'; ?></h2>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'ما هي سياسة ضمان النباتات الخاصة بكم؟' : 'What is your plant guarantee policy?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' 
                    ? 'نحن نضمن جميع النباتات لمدة 14 يومًا بعد التسليم. إذا وصل نباتك تالفًا أو مات خلال هذه الفترة، يرجى الاتصال بنا مع صور وسنقوم باستبداله أو إصدار رد. لا يشمل ذلك الضرر الناتج عن الرعاية غير السليمة.' 
                    : 'We guarantee all plants for 14 days after delivery. If your plant arrives damaged or dies within this period, please contact us with photos and we\'ll replace it or issue a refund. This doesn\'t cover damage from improper care.'; ?></p>
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'هل يمكنني إرجاع نبات غير مرغوب فيه؟' : 'Can I return a plant I changed my mind about?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' 
                    ? 'نظرًا لأن النباتات كائنات حية، لا يمكننا قبول المرتجعات إلا إذا كان النبات في حالته الأصلية مع جميع العلامات وإذا تم الترتيب خلال 3 أيام من التسليم. يتم تطبيق رسوم إعادة تخزين بنسبة 15٪. لا يمكن إرجاع العناصر القابلة للتلف مثل الزهور المقطوفة.' 
                    : 'Because plants are living things, we can only accept returns if the plant is in its original condition with all tags and if arranged within 3 days of delivery. A 15% restocking fee applies. Perishable items like cut flowers cannot be returned.'; ?></p>
            </div>
        </div>
    </div>
    
    <div class="faq-category">
        <h2><?php echo $current_lang === 'ar' ? 'اختيار النباتات' : 'Plant Selection'; ?></h2>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'هل لديك نباتات آمنة للحيوانات الأليفة؟' : 'Do you have pet-friendly plants?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' ? 'نعم! لدينا <a href="https://bta3ward.shop/pages/shop.php">مجموعة خاصة</a> من النباتات غير السامة الآمنة للمنازل التي بها حيوانات أليفة، بما في ذلك:' : 'Yes! We have a <a https://bta3ward.shop/pages/shop.phpts.php">special collection</a> of non-toxic plants safe for homes with pets, including:'; ?></p>
                <ul>
                    <li><?php echo $current_lang === 'ar' ? 'نباتات العنكبوت' : 'Spider Plants'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'السراخس بوسطن' : 'Boston Ferns'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'نخيل البارلور' : 'Parlor Palms'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'بيبروميا' : 'Peperomia'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'كالاثيا' : 'Calathea'; ?></li>
                </ul>
                <p><?php echo $current_lang === 'ar' 
                    ? 'ابحث دائمًا عن النباتات إذا كان لديك حيوانات أليفة، حيث قد تتفاعل الحيوانات الفردية بشكل مختلف.' 
                    : 'Always research plants if you have pets, as individual animals may react differently.'; ?></p>
            </div>
        </div>
        
        <div class="faq-item">
            <button class="faq-question">
                <?php echo $current_lang === 'ar' ? 'هل يمكنكم مساعدتي في اختيار نباتات للإضاءة المنخفضة؟' : 'Can you help me choose plants for low-light conditions?'; ?>
            </button>
            <div class="faq-answer">
                <p><?php echo $current_lang === 'ar' ? 'بالتأكيد! بعض من أفضل نباتاتنا للإضاءة المنخفضة تشمل:' : 'Absolutely! Some of our best low-light plants include:'; ?></p>
                <ul>
                    <li><?php echo $current_lang === 'ar' ? 'نبات الثعبان (سانسيفيريا)' : 'Snake Plant (Sansevieria)'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'نبات ZZ (زاميوكولكاس زاميفوليا)' : 'ZZ Plant (Zamioculcas zamiifolia)'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'بوثوس' : 'Pothos'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'زنبق السلام' : 'Peace Lily'; ?></li>
                    <li><?php echo $current_lang === 'ar' ? 'نبات الحديد الزهر (أسبيدسترا)' : 'Cast Iron Plant (Aspidistra)'; ?></li>
                </ul>
                <p><?php echo $current_lang === 'ar' 
                    ? 'قم بزيارة <a href="https://bta3ward.shop/pages/shop.php">مجموعة الإضاءة المنخفضة</a> لدينا أو <a href="contact.php">اتصل بنا</a> للحصول على توصيات مخصصة.' 
                    : 'Visit our <a href="https://bta3ward.shop/pages/shop.php">Low Light Collection</a> or <a href="contact.php">contact us</a> for personalized recommendations.'; ?></p>
            </div>
        </div>
    </div>
    
    <div class="faq-contact-prompt">
        <p>
            <?php echo $current_lang === 'ar' 
                ? 'لا تزال لديك أسئلة؟ خبراء النبات لدينا سعداء بمساعدتك!' 
                : 'Still have questions? Our plant experts are happy to help!'; ?>
            <a href="contact.php" class="contact-button">
                <?php echo $current_lang === 'ar' ? 'اتصل بنا' : 'Contact Us'; ?>
            </a>
        </p>
    </div>
</div>

<script>
// Simple JavaScript to toggle FAQ answers
document.querySelectorAll('.faq-question').forEach(button => {
    button.addEventListener('click', () => {
        const answer = button.nextElementSibling;

        // Toggle active class for styling (like arrow rotation or bold text)
        button.classList.toggle('active');

        // Toggle show class for the answer
        answer.classList.toggle('show');
    });
});
</script>

<style>
.faq-page {
    padding: 2rem 0;
    max-width: 900px;
    margin: 0 auto;
}

.faq-intro {
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.faq-category {
    margin-bottom: 3rem;
}

.faq-category h2 {
    color: #2c7a4a;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}

.faq-item {
    margin-bottom: 1rem;
    border: 1px solid #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.faq-question {
    width: 100%;
    text-align: left;
    padding: 1rem;
    background-color: #f8f9fa;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 1rem;
    transition: background-color 0.3s;
}

.faq-question:hover {
    background-color: #e9ecef;
}

.faq-question:after {
    content: '+';
    float: right;
    <?php echo $current_lang === 'ar' ? 'float: left;' : ''; ?>
}

.faq-question.active:after {
    content: '-';
}

.faq-answer {
    padding: 0 1rem;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out, padding 0.3s ease;
}

.faq-answer.show {
    padding: 1rem;
    max-height: 500px;
}

.faq-contact-prompt {
    text-align: center;
    margin-top: 3rem;
    padding: 2rem;
    background-color: #f1f8e9;
    border-radius: 5px;
}

.contact-button {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.5rem 1.5rem;
    background-color: #2c7a4a;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s;
}

.contact-button:hover {
    background-color: #1e5631;
}

/* RTL specific styles */
[dir="rtl"] .faq-question:after {
    float: left;
}

[dir="rtl"] .faq-question {
    text-align: right;
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
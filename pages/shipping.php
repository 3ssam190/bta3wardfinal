<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Define translations
$translations = [
    'en' => [
        'page_title' => 'Shipping Information',
        'shipping_policy' => 'Shipping Policy',
        'domestic_shipping' => 'Domestic Shipping',
        'domestic_details' => 'We deliver to all governorates across Egypt within 3-7 business days.',
        'delivery_areas' => 'Delivery Areas & Timeframes',
        'cairo_alex' => 'Cairo & Alexandria',
        'cairo_alex_time' => '1-3 business days',
        'delta' => 'Delta Cities (Tanta, Mansoura, etc.)',
        'delta_time' => '2-4 business days',
        'canal' => 'Canal Cities (Ismailia, Suez, etc.)',
        'canal_time' => '3-5 business days',
        'upper_egypt' => 'Upper Egypt (Assiut, Luxor, Aswan)',
        'upper_egypt_time' => '5-7 business days',
        'remote' => 'Remote Areas (Oases, Sinai)',
        'remote_time' => '7-10 business days',
        'shipping_rates' => 'Shipping Rates',
        'free_shipping' => 'FREE Shipping on orders over EGP 500',
        'standard_rate' => 'Standard Rate: EGP 50',
        'express_rate' => 'Express Delivery (Cairo only): EGP 100 (next day)',
        'processing_time' => 'Processing Time',
        'processing_details' => 'Orders are processed within 1-2 business days. Weekend orders are processed on Sunday.',
        'plant_shipping' => 'Plant Shipping Care',
        'plant_details' => 'Our plants are carefully packaged with special protective materials to ensure they arrive safely. We include care instructions with every order.',
        'tracking' => 'Order Tracking',
        'tracking_details' => 'You will receive an SMS with tracking information once your order ships.',
        'faq' => 'Shipping FAQ',
        'faq_items' => [
            [
                'question' => 'What are your delivery hours?',
                'answer' => 'We deliver daily from 9 AM to 9 PM, including weekends.'
            ],
            [
                'question' => 'What if I\'m not available for delivery?',
                'answer' => 'Our driver will call you to reschedule. Two failed attempts will return the order to our warehouse.'
            ],
            [
                'question' => 'Do you offer cash on delivery?',
                'answer' => 'Yes, we accept cash on delivery across Egypt.'
            ]
        ]
    ],
    'ar' => [
        'page_title' => 'معلومات الشحن',
        'shipping_policy' => 'سياسة الشحن',
        'domestic_shipping' => 'الشحن المحلي',
        'domestic_details' => 'نقوم بالتوصيل لجميع المحافظات في مصر خلال 3-7 أيام عمل.',
        'delivery_areas' => 'مناطق التوصيل والفترات الزمنية',
        'cairo_alex' => 'القاهرة والإسكندرية',
        'cairo_alex_time' => '1-3 أيام عمل',
        'delta' => 'مدن الدلتا (طنطا، المنصورة، إلخ)',
        'delta_time' => '2-4 أيام عمل',
        'canal' => 'مدن القناة (الإسماعيلية، السويس، إلخ)',
        'canal_time' => '3-5 أيام عمل',
        'upper_egypt' => 'صعيد مصر (أسيوط، الأقصر، أسوان)',
        'upper_egypt_time' => '5-7 أيام عمل',
        'remote' => 'المناطق النائية (الواحات، سيناء)',
        'remote_time' => '7-10 أيام عمل',
        'shipping_rates' => 'أسعار الشحن',
        'free_shipping' => 'شحن مجاني للطلبات فوق 500 جنيه',
        'standard_rate' => 'السعر القياسي: 50 جنيهاً',
        'express_rate' => 'توصيل سريع (القاهرة فقط): 100 جنيهاً (اليوم التالي)',
        'processing_time' => 'وقت المعالجة',
        'processing_details' => 'يتم معالجة الطلبات خلال 1-2 يوم عمل. يتم معالجة طلبات نهاية الأسبوع يوم الأحد.',
        'plant_shipping' => 'رعاية شحن النباتات',
        'plant_details' => 'يتم تغليف نباتاتنا بعناية بمواد واقية خاصة لضمان وصولها بأمان. نضمن تعليمات العناية مع كل طلب.',
        'tracking' => 'تتبع الطلب',
        'tracking_details' => 'سوف تتلقى رسالة نصية تحتوي على معلومات التتبع بمجرد شحن طلبك.',
        'faq' => 'أسئلة شائعة عن الشحن',
        'faq_items' => [
            [
                'question' => 'ما هي ساعات التوصيل؟',
                'answer' => 'نقوم بالتوصيل يوميًا من الساعة 9 صباحًا حتى 9 مساءً، بما في ذلك عطلة نهاية الأسبوع.'
            ],
            [
                'question' => 'ماذا لو لم أكن متاحًا للتوصيل؟',
                'answer' => 'سيتصل بك السائق لإعادة الجدولة. سيتم إرجاع الطلب إلى مستودعنا بعد محاولتين فاشلتين.'
            ],
            [
                'question' => 'هل تقدمون الدفع عند الاستلام؟',
                'answer' => 'نعم، نقبل الدفع عند الاستلام في جميع أنحاء مصر.'
            ]
        ]
    ]
];

$lang = $current_lang ?? 'en';
$t = $translations[$lang];
?>

<section class="shipping-section py-5" <?php echo $current_lang === 'ar' ? 'dir="rtl"' : ''; ?>>
    <div class="container">
        <div class="row mb-5">
            <div class="col-12 text-center">
                <h1 class="display-5 fw-bold text-success mb-3"><?php echo $t['shipping_policy']; ?></h1>
                <p class="lead"><?php echo $t['domestic_details']; ?></p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card h-100 border-success border-2">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><?php echo $t['delivery_areas']; ?></h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $t['cairo_alex']; ?>
                                <span class="badge bg-success rounded-pill"><?php echo $t['cairo_alex_time']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $t['delta']; ?>
                                <span class="badge bg-success rounded-pill"><?php echo $t['delta_time']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $t['canal']; ?>
                                <span class="badge bg-success rounded-pill"><?php echo $t['canal_time']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $t['upper_egypt']; ?>
                                <span class="badge bg-success rounded-pill"><?php echo $t['upper_egypt_time']; ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $t['remote']; ?>
                                <span class="badge bg-success rounded-pill"><?php echo $t['remote_time']; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-success border-2">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0"><?php echo $t['shipping_rates']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <i class="fas fa-truck me-2"></i>
                            <strong><?php echo $t['free_shipping']; ?></strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><?php echo $t['standard_rate']; ?></li>
                            <li class="list-group-item"><?php echo $t['express_rate']; ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="icon-lg bg-light-success rounded-circle mb-3 mx-auto">
                            <i class="fas fa-clock text-success fs-3"></i>
                        </div>
                        <h4><?php echo $t['processing_time']; ?></h4>
                        <p><?php echo $t['processing_details']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="icon-lg bg-light-success rounded-circle mb-3 mx-auto">
                            <i class="fas fa-seedling text-success fs-3"></i>
                        </div>
                        <h4><?php echo $t['plant_shipping']; ?></h4>
                        <p><?php echo $t['plant_details']; ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="icon-lg bg-light-success rounded-circle mb-3 mx-auto">
                            <i class="fas fa-map-marked-alt text-success fs-3"></i>
                        </div>
                        <h4><?php echo $t['tracking']; ?></h4>
                        <p><?php echo $t['tracking_details']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h3 class="mb-0 text-success"><?php echo $t['faq']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="shippingFAQ">
                            <?php foreach ($t['faq_items'] as $index => $faq): ?>
                            <div class="accordion-item border-0 mb-2 rounded overflow-hidden">
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="false" aria-controls="collapse<?php echo $index; ?>">
                                        <?php echo $faq['question']; ?>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#shippingFAQ">
                                    <div class="accordion-body">
                                        <?php echo $faq['answer']; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.shipping-section {
    background-color: #f8f9fa;
}

.icon-lg {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-light-success {
    background-color: rgba(40, 167, 69, 0.1);
}

.accordion-button:not(.collapsed) {
    background-color: #e8f5e9;
    color: #2e7d32;
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}

[dir="rtl"] .accordion-button::after {
    margin-left: 0;
    margin-right: auto;
}

[dir="rtl"] .me-2 {
    margin-right: 0 !important;
    margin-left: 0.5rem !important;
}
</style>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
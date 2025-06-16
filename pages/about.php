<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';

// Define translations
$translations = [
    'en' => [
        'page_title' => 'About Us',
        'our_story' => 'Our Story',
        'story_subtitle' => 'From small beginnings to growing together',
        'meet_team' => 'Meet Our Team',
        'get_in_touch' => 'Get In Touch',
        'our_mission' => 'Our Mission',
        'mission_text' => 'To bring nature\'s beauty into every home with sustainable, high-quality plants that thrive in any environment.',
        'our_vision' => 'Our Vision',
        'vision_text' => 'A world where everyone has access to greenery that improves their quality of life and connection to nature.',
        'our_values' => 'Our Values',
        'values_text' => 'Sustainability, quality, education, and exceptional customer experiences guide everything we do.',
        'our_journey' => 'Our Journey',
        'timeline_2015' => 'Founded in a Small Garage',
        'timeline_2015_text' => 'Started with just 10 plant varieties and a passion for greenery.',
        'timeline_2017' => 'First Retail Location',
        'timeline_2017_text' => 'Opened our flagship store in downtown, expanding to 50+ plant varieties.',
        'timeline_2019' => 'Launched Online Store',
        'timeline_2019_text' => 'Began shipping nationwide with our innovative plant-safe packaging.',
        'timeline_2022' => 'Sustainability Certification',
        'timeline_2022_text' => 'Achieved Green Business certification for our eco-friendly practices.',
        'timeline_2023' => 'Community Garden Initiative',
        'timeline_2023_text' => 'Planted our 10,000th free community garden across the city.',
        'stats_plants' => 'Plants Sold',
        'stats_varieties' => 'Varieties',
        'stats_satisfaction' => '% Satisfaction',
        'stats_team' => 'Team Members'
    ],
    'ar' => [
        'page_title' => 'من نحن',
        'our_story' => 'قصتنا',
        'story_subtitle' => 'من بدايات صغيرة إلى النمو معًا',
        'meet_team' => 'تعرف على فريقنا',
        'get_in_touch' => 'تواصل معنا',
        'our_mission' => 'مهمتنا',
        'mission_text' => 'جلب جمال الطبيعة إلى كل منزل مع نباتات عالية الجودة ومستدامة تزدهر في أي بيئة.',
        'our_vision' => 'رؤيتنا',
        'vision_text' => 'عالم حيث يكون للجميع إمكانية الوصول إلى المساحات الخضراء التي تحسن جودة حياتهم واتصالهم بالطبيعة.',
        'our_values' => 'قيمنا',
        'values_text' => 'الاستدامة، الجودة، التعليم، وتجارب العملاء الاستثنائية توجه كل ما نقوم به.',
        'our_journey' => 'رحلتنا',
        'timeline_2015' => 'تأسست في مرآب صغير',
        'timeline_2015_text' => 'بدأنا بـ 10 أنواع فقط من النباتات وشغف بالمساحات الخضراء.',
        'timeline_2017' => 'أول موقع بيع بالتجزئة',
        'timeline_2017_text' => 'افتتحنا متجرنا الرئيسي في وسط المدينة، وتوسعنا إلى أكثر من 50 نوعًا من النباتات.',
        'timeline_2019' => 'إطلاق المتجر الإلكتروني',
        'timeline_2019_text' => 'بدأنا الشحن على مستوى البلاد بتغليفنا المبتكر الآمن للنباتات.',
        'timeline_2022' => 'شهادة الاستدامة',
        'timeline_2022_text' => 'حصلنا على شهادة الأعمال الخضراء لممارساتنا الصديقة للبيئة.',
        'timeline_2023' => 'مبادرة حديقة المجتمع',
        'timeline_2023_text' => 'زرعنا حديقتنا المجتمعية المجانية رقم 10,000 في جميع أنحاء المدينة.',
        'stats_plants' => 'نباتات بيعت',
        'stats_varieties' => 'أصناف',
        'stats_satisfaction' => '٪ رضا',
        'stats_team' => 'أعضاء الفريق'
    ]
];

$lang = $current_lang ?? 'en';
$t = $translations[$lang];
?>

<!-- Animated Background -->
<div class="page-background">
    <div class="circle-animation circle-1"></div>
    <div class="circle-animation circle-2"></div>
    <div class="circle-animation circle-3"></div>
</div>

<section class="about-section" <?php echo $current_lang === 'ar' ? 'dir="rtl"' : ''; ?>>
    <div class="container">
        <!-- Hero Section -->
        <div class="row align-items-center hero-row">
            <div class="col-lg-6">
                <h1 class="hero-title" data-aos="fade-up"><?php echo $t['our_story']; ?></h1>
                <p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo $t['story_subtitle']; ?></p>
                <div class="hero-cta" data-aos="fade-up" data-aos-delay="200">
                    <a href="#our-team" class="btn btn-success me-3"><?php echo $t['meet_team']; ?></a>
                    <a href="contact" class="btn btn-outline-success"><?php echo $t['get_in_touch']; ?></a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-image-container" data-aos="zoom-in" data-aos-delay="300">
                    <img src="<?php echo BASE_URL; ?>/assets/images/about-hero.avif" alt="Our team working together" class="hero-image">
                    <div class="floating-shapes">
                        <div class="shape shape-1"></div>
                        <div class="shape shape-2"></div>
                        <div class="shape shape-3"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission Section -->
        <div class="mission-section">
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <h3><?php echo $t['our_mission']; ?></h3>
                        <p><?php echo $t['mission_text']; ?></p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <h3><?php echo $t['our_vision']; ?></h3>
                        <p><?php echo $t['vision_text']; ?></p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="mission-card">
                        <div class="mission-icon">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <h3><?php echo $t['our_values']; ?></h3>
                        <p><?php echo $t['values_text']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Section -->
        <div class="timeline-section">
            <h2 class="section-title" data-aos="fade-up"><?php echo $t['our_journey']; ?></h2>
            <div class="timeline">
                <div class="timeline-item" data-aos="fade-right">
                    <div class="timeline-content">
                        <div class="timeline-year">2015</div>
                        <h3><?php echo $t['timeline_2015']; ?></h3>
                        <p><?php echo $t['timeline_2015_text']; ?></p>
                    </div>
                </div>
                <div class="timeline-item" data-aos="fade-left" data-aos-delay="100">
                    <div class="timeline-content">
                        <div class="timeline-year">2017</div>
                        <h3><?php echo $t['timeline_2017']; ?></h3>
                        <p><?php echo $t['timeline_2017_text']; ?></p>
                    </div>
                </div>
                <div class="timeline-item" data-aos="fade-right" data-aos-delay="200">
                    <div class="timeline-content">
                        <div class="timeline-year">2019</div>
                        <h3><?php echo $t['timeline_2019']; ?></h3>
                        <p><?php echo $t['timeline_2019_text']; ?></p>
                    </div>
                </div>
                <div class="timeline-item" data-aos="fade-left" data-aos-delay="300">
                    <div class="timeline-content">
                        <div class="timeline-year">2022</div>
                        <h3><?php echo $t['timeline_2022']; ?></h3>
                        <p><?php echo $t['timeline_2022_text']; ?></p>
                    </div>
                </div>
                <div class="timeline-item" data-aos="fade-right" data-aos-delay="400">
                    <div class="timeline-content">
                        <div class="timeline-year">2023</div>
                        <h3><?php echo $t['timeline_2023']; ?></h3>
                        <p><?php echo $t['timeline_2023_text']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Section -->
        <div class="team-section" id="our-team">
            <h2 class="section-title" data-aos="fade-up"><?php echo $t['meet_team']; ?></h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="zoom-in">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/1.jpeg" alt="team leader">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="https://x.com/EsamAhmed190?t=CntWizJPz6pyslLbEfPqGg&s=09"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
                                <a href="https://www.instagram.com/_essamahmed_?igsh=MWVjc3U3cGg1eXRnbw=="><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3>Essam Ahmed</h3>
                            <p><?php echo $current_lang === 'ar' ? 'قائد الفريق' : 'Team Leader'; ?></p>
                            <p><?php echo $current_lang === 'ar' ? 'المؤسس والرئيس التنفيذي' : 'Founder & CEO'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/2.jpeg" alt="team 2">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3>Marwan Mahmoud</h3>
                            <p><?php echo $current_lang === 'ar' ? 'الدور' : 'Role'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/3.jpeg" alt="team3">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3>Mohamed Said</h3>
                            <p><?php echo $current_lang === 'ar' ? 'الدور' : 'Role'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/4.jpeg" alt="team3">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3>Galal Maher</h3>
                            <p><?php echo $current_lang === 'ar' ? 'الدور' : 'Role'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/5.jpeg" alt="team3">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3>Ziad Akram</h3>
                            <p><?php echo $current_lang === 'ar' ? 'الدور' : 'Role'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
                    <div class="team-card">
                        <div class="team-image">
                            <img src="<?php echo BASE_URL; ?>/assets/images/6.jpeg" alt="team3">
                            <div class="team-social">
                                <a href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter-x" viewBox="0 0 16 16">
  <path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865z"/>
</svg></a>
                                <a href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                        <div class="team-info">
                            <h3>Hazem Hassan</h3>
                            <p><?php echo $current_lang === 'ar' ? 'الدور' : 'Role'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="row g-4">
                <div class="col-md-3" data-aos="fade-up">
                    <div class="stat-card">
                        <div class="stat-number" data-count="10000">0</div>
                        <div class="stat-label"><?php echo $t['stats_plants']; ?></div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-number" data-count="500">0</div>
                        <div class="stat-label"><?php echo $t['stats_varieties']; ?></div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-number" data-count="98">0</div>
                        <div class="stat-label"><?php echo $t['stats_satisfaction']; ?></div>
                    </div>
                </div>
                <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card">
                        <div class="stat-number" data-count="25">0</div>
                        <div class="stat-label"><?php echo $t['stats_team']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CSS Styles -->
<style>
/* Base Styles */
.about-section {
    position: relative;
    padding: 5rem 0;
    overflow: hidden;
    z-index: 1;
}

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
    left: 5%;
    animation-delay: 0s;
}

.circle-2 {
    width: 400px;
    height: 400px;
    bottom: 15%;
    right: 10%;
    animation-delay: 3s;
}

.circle-3 {
    width: 250px;
    height: 250px;
    top: 60%;
    left: 30%;
    animation-delay: 6s;
}

@keyframes float {
    0%, 100% { transform: translateY(0) translateX(0); }
    50% { transform: translateY(-50px) translateX(50px); }
}

/* Hero Section */
.hero-row {
    margin-bottom: 5rem;
}

.hero-title {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: #2e7d32;
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.25rem;
    color: #6c757d;
    margin-bottom: 2rem;
}

.hero-image-container {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.1);
}

.hero-image {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.5s ease;
}

.hero-image-container:hover .hero-image {
    transform: scale(1.05);
}

.floating-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(5px);
}

.shape-1 {
    width: 80px;
    height: 80px;
    top: -20px;
    left: -20px;
}

.shape-2 {
    width: 120px;
    height: 120px;
    bottom: -30px;
    right: -30px;
}

.shape-3 {
    width: 60px;
    height: 60px;
    top: 50%;
    right: -15px;
}

/* Mission Section */
.mission-section {
    margin: 5rem 0;
}

.mission-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    height: 100%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid rgba(0,0,0,0.05);
}

.mission-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.mission-icon {
    width: 60px;
    height: 60px;
    background: #e8f5e9;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
    color: #2e7d32;
    font-size: 1.5rem;
}

.mission-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: #2e7d32;
}

/* Timeline Section */
.timeline-section {
    margin: 5rem 0;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 3rem;
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

.timeline {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.timeline::after {
    content: '';
    position: absolute;
    width: 4px;
    background: #81c784;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -2px;
}

[dir="rtl"] .timeline::after {
    left: auto;
    right: 50%;
    margin-left: 0;
    margin-right: -2px;
}

.timeline-item {
    padding: 10px 40px;
    position: relative;
    width: 50%;
    box-sizing: border-box;
}

.timeline-item:nth-child(odd) {
    left: 0;
}

.timeline-item:nth-child(even) {
    left: 50%;
}

[dir="rtl"] .timeline-item:nth-child(odd) {
    left: auto;
    right: 0;
}

[dir="rtl"] .timeline-item:nth-child(even) {
    left: auto;
    right: 50%;
}

.timeline-content {
    padding: 20px 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.05);
    position: relative;
}

.timeline-year {
    position: absolute;
    top: -25px;
    font-weight: bold;
    color: #2e7d32;
    background: white;
    padding: 5px 15px;
    border-radius: 20px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    z-index: 1;
}

.timeline-item:nth-child(odd) .timeline-year {
    right: -15px;
}

.timeline-item:nth-child(even) .timeline-year {
    left: -15px;
}

[dir="rtl"] .timeline-item:nth-child(odd) .timeline-year {
    right: auto;
    left: -15px;
}

[dir="rtl"] .timeline-item:nth-child(even) .timeline-year {
    left: auto;
    right: -15px;
}

.timeline-item::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    background: white;
    border: 4px solid #2e7d32;
    border-radius: 50%;
    top: 15px;
    z-index: 1;
}

.timeline-item:nth-child(odd)::after {
    right: -12px;
}

.timeline-item:nth-child(even)::after {
    left: -12px;
}

[dir="rtl"] .timeline-item:nth-child(odd)::after {
    right: auto;
    left: -12px;
}

[dir="rtl"] .timeline-item:nth-child(even)::after {
    left: auto;
    right: -12px;
}

@media (max-width: 768px) {
    .timeline::after {
        left: 31px;
    }
    
    [dir="rtl"] .timeline::after {
        left: auto;
        right: 31px;
    }
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    [dir="rtl"] .timeline-item {
        padding-left: 25px;
        padding-right: 70px;
    }
    
    .timeline-item:nth-child(even) {
        left: 0;
    }
    
    [dir="rtl"] .timeline-item:nth-child(even) {
        right: 0;
    }
    
    .timeline-item::after {
        left: 21px;
    }
    
    [dir="rtl"] .timeline-item::after {
        left: auto;
        right: 21px;
    }
    
    .timeline-item:nth-child(odd) .timeline-year,
    .timeline-item:nth-child(even) .timeline-year {
        left: -15px;
        right: auto;
    }
    
    [dir="rtl"] .timeline-item:nth-child(odd) .timeline-year,
    [dir="rtl"] .timeline-item:nth-child(even) .timeline-year {
        left: auto;
        right: -15px;
    }
}

/* Team Section */
.team-section {
    margin: 5rem 0;
}

.team-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: transform 0.3s ease;
    margin-bottom: 1.5rem;
}

.team-card:hover {
    transform: translateY(-10px);
}

.team-image {
    position: relative;
    overflow: hidden;
    height: 300px;
}

.team-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.team-card:hover .team-image img {
    transform: scale(1.1);
}

.team-social {
    position: absolute;
    bottom: -60px;
    left: 0;
    width: 100%;
    background: rgba(46, 125, 50, 0.9);
    padding: 15px;
    display: flex;
    justify-content: center;
    gap: 15px;
    transition: bottom 0.3s ease;
}

.team-card:hover .team-social {
    bottom: 0;
}

.team-social a {
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.2);
    transition: background 0.3s ease;
}

.team-social a:hover {
    background: white;
    color: #2e7d32;
}

.team-info {
    padding: 1.5rem;
    text-align: center;
}

.team-info h3 {
    margin-bottom: 0.5rem;
    color: #2e7d32;
}

.team-info p {
    color: #6c757d;
    margin-bottom: 0;
}

/* Stats Section */
.stats-section {
    margin: 5rem 0;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    height: 100%;
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    color: #2e7d32;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1.1rem;
    color: #6c757d;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-row {
        flex-direction: column-reverse;
    }
    
    .hero-image-container {
        margin-bottom: 2rem;
    }
    
    /*.timeline::after {*/
    /*    left: 31px;*/
    /*}*/
    
    .timeline-item {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }
    
    .timeline-item:nth-child(even) {
        left: 0;
    }
    
    .timeline-item::after {
        left: 21px;
    }
    
    .timeline-item:nth-child(odd) .timeline-year,
    .timeline-item:nth-child(even) .timeline-year {
        left: -15px;
        right: auto;
    }
}

/* RTL Adjustments */
[dir="rtl"] .hero-cta .btn {
    margin-right: 0 !important;
    margin-left: 1rem !important;
}

/*[dir="rtl"] .timeline::after {*/
/*    right: 31px;*/
/*    left: auto;*/
/*}*/

[dir="rtl"] .timeline-item {
    padding-right: 70px;
    padding-left: 25px;
}

[dir="rtl"] .timeline-item::after {
    right: 21px;
    left: auto;
}

[dir="rtl"] .timeline-item:nth-child(odd) .timeline-year,
[dir="rtl"] .timeline-item:nth-child(even) .timeline-year {
    right: -15px;
    left: auto;
}

[dir="rtl"] .shape-1 {
    right: -20px;
    left: auto;
}

[dir="rtl"] .shape-2 {
    left: -30px;
    right: auto;
}

[dir="rtl"] .shape-3 {
    left: -15px;
    right: auto;
}
</style>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animate stats counting
    const statNumbers = document.querySelectorAll('.stat-number');
    
    statNumbers.forEach(stat => {
        const target = parseInt(stat.getAttribute('data-count'));
        const suffix = stat.textContent.match(/%/) ? '%' : '';
        const duration = 2000; // Animation duration in ms
        const startTime = Date.now();
        
        const animateCount = () => {
            const currentTime = Date.now();
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const value = Math.floor(progress * target);
            
            stat.textContent = value + suffix;
            
            if (progress < 1) {
                requestAnimationFrame(animateCount);
            }
        };
        
        // Start animation when element is in view
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animateCount();
                observer.unobserve(stat);
            }
        });
        
        observer.observe(stat);
    });
    
    // Initialize AOS (Animate On Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    }
});
</script>

<?php 
require_once __DIR__ . '/../includes/footer.php';
?>
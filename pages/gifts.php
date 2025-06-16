<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="gift-builder-container">
    <!-- Animated Background Elements -->
    <div class="romantic-elements">
        <div class="heart-animation heart-1"></div>
        <div class="heart-animation heart-2"></div>
        <div class="heart-animation heart-3"></div>
        <div class="floating-rose rose-1"></div>
        <div class="floating-rose rose-2"></div>
    </div>
    
    <!-- Progress Steps -->
    <div class="progress-tracker">
        <div class="progress-bar">
            <div class="progress-fill" style="width: 0%"></div>
        </div>
        <div class="steps-container">
            <div class="step active" data-step="1">
                <div class="step-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12,3L2,12H5V20H19V12H22L12,3Z" />
                    </svg>
                </div>
                <span class="step-label">Choose Pattern</span>
            </div>
            <div class="step" data-step="2">
                <div class="step-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12,2L4.5,20.29L5.21,21L12,18L18.79,21L19.5,20.29L12,2Z" />
                    </svg>
                </div>
                <span class="step-label"><?php echo __('choose_cover'); ?></span>
            </div>
            <div class="step" data-step="3">
                <div class="step-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12,2C13.1,2 14,2.9 14,4C14,5.1 13.1,6 12,6C10.9,6 10,5.1 10,4C10,2.9 10.9,2 12,2M15.5,8C16.3,8 17,8.7 17,9.5C17,10.3 16.3,11 15.5,11C14.7,11 14,10.3 14,9.5C14,8.7 14.7,8 15.5,8M8.5,8C9.3,8 10,8.7 10,9.5C10,10.3 9.3,11 8.5,11C7.7,11 7,10.3 7,9.5C7,8.7 7.7,8 8.5,8M3.5,11C4.3,11 5,11.7 5,12.5C5,13.3 4.3,14 3.5,14C2.7,14 2,13.3 2,12.5C2,11.7 2.7,11 3.5,11M5,3H8V5H5V10H3V5H0V3H3V0H5V3M19,0H21V3H24V5H21V10H19V5H16V3H19V0M12,12C14.67,12 20,13.34 20,16V18H4V16C4,13.34 9.33,12 12,12Z" />
                    </svg>
                </div>
                <span class="step-label"><?php echo __('choose_flowers'); ?></span>
            </div>
            <div class="step" data-step="4">
                <div class="step-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z" />
                    </svg>
                </div>
                <span class="step-label"><?php echo __('review'); ?></span>
            </div>
        </div>
    </div>

    <div class="builder-content">
        <!-- Products Selection Panel -->
        <div class="products-panel" id="products-container">
            <div class="panel-header">
                <h3 class="panel-title"><?php echo __('choose_pattern'); ?></h3>
                <div class="step-counter">
                    <span class="current-step">1</span> / 4
                </div>
            </div>
            <div class="products-grid"></div>
        </div>

        <!-- Bouquet Preview Panel -->
        <div class="preview-panel">
            <div class="preview-container">
                <div class="bouquet-display">
                    <div class="pattern-preview">
                        <div class="pattern-image"></div>
                    </div>
                    <div class="cover-preview animated-cover">
                        <div class="cover-image"></div>
                        <div class="bouquet-circle">
                            <div class="flowers-preview"></div>
                            <div class="circle-decoration"></div>
                            <div class="circle-decoration-2"></div>
                        </div>
                    </div>
                    <div class="bouquet-shine"></div>
                </div>
                
                <div class="bouquet-summary">
                    <h3 class="summary-title"><?php echo __('your_custom_bouquet'); ?></h3>
                    <div class="summary-details">
                        <div class="summary-item">
                            <span class="summary-label">Pattern:</span>
                            <span class="summary-value" id="pattern-summary">Not selected</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Cover:</span>
                            <span class="summary-value" id="cover-summary">Not selected</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Flowers:</span>
                            <span class="summary-value" id="flowers-summary">0 selected</span>
                        </div>
                    </div>
                    
                    <div class="builder-navigation">
                        <button class="btn btn-prev" id="prev-btn" disabled>
                            <svg viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
                            </svg>
                            Previous
                        </button>
                        <button class="btn btn-next" id="next-btn">
                            Next
                            <svg viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confetti Effect Container -->
<div class="confetti-container"></div>

<style>
:root {
    --primary-color: #ff6b8b;
    --primary-dark: #ff4757;
    --secondary-color: #ffb8c6;
    --accent-color: #ff9eb5;
    --light-color: #fff5f7;
    --dark-color: #2d3436;
    --success-color: #55efc4;
    --shadow-color: rgba(255, 107, 139, 0.3);
}

.gift-builder-container {
    position: relative;
    min-height: calc(100vh - 150px);
    padding: 2rem;
    overflow: hidden;
    background-color: var(--light-color);
    background-image: radial-gradient(circle at 10% 20%, rgba(255, 184, 198, 0.1) 0%, rgba(255, 255, 255, 1) 90%);
}

.romantic-elements {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: 0;
}

.heart-animation {
    position: absolute;
    background-color: var(--primary-color);
    opacity: 0.15;
    border-radius: 50%;
    animation: float 15s infinite linear;
}

.heart-1 {
    width: 100px;
    height: 100px;
    top: 10%;
    left: 5%;
    animation-delay: 0s;
}

.heart-2 {
    width: 60px;
    height: 60px;
    top: 60%;
    left: 80%;
    animation-delay: 3s;
}

.heart-3 {
    width: 80px;
    height: 80px;
    top: 30%;
    left: 70%;
    animation-delay: 6s;
}

.floating-rose {
    position: absolute;
    width: 50px;
    height: 50px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    opacity: 0.1;
    animation: float 20s infinite ease-in-out;
}

.rose-1 {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%23ff6b8b" d="M12,2C13.1,2 14,2.9 14,4C14,5.1 13.1,6 12,6C10.9,6 10,5.1 10,4C10,2.9 10.9,2 12,2M15.5,8C16.3,8 17,8.7 17,9.5C17,10.3 16.3,11 15.5,11C14.7,11 14,10.3 14,9.5C14,8.7 14.7,8 15.5,8M8.5,8C9.3,8 10,8.7 10,9.5C10,10.3 9.3,11 8.5,11C7.7,11 7,10.3 7,9.5C7,8.7 7.7,8 8.5,8M3.5,11C4.3,11 5,11.7 5,12.5C5,13.3 4.3,14 3.5,14C2.7,14 2,13.3 2,12.5C2,11.7 2.7,11 3.5,11M5,3H8V5H5V10H3V5H0V3H3V0H5V3M19,0H21V3H24V5H21V10H19V5H16V3H19V0M12,12C14.67,12 20,13.34 20,16V18H4V16C4,13.34 9.33,12 12,12Z" /></svg>');
    top: 20%;
    left: 85%;
    animation-delay: 2s;
}

.rose-2 {
    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="%23ff6b8b" d="M12,2C13.1,2 14,2.9 14,4C14,5.1 13.1,6 12,6C10.9,6 10,5.1 10,4C10,2.9 10.9,2 12,2M15.5,8C16.3,8 17,8.7 17,9.5C17,10.3 16.3,11 15.5,11C14.7,11 14,10.3 14,9.5C14,8.7 14.7,8 15.5,8M8.5,8C9.3,8 10,8.7 10,9.5C10,10.3 9.3,11 8.5,11C7.7,11 7,10.3 7,9.5C7,8.7 7.7,8 8.5,8M3.5,11C4.3,11 5,11.7 5,12.5C5,13.3 4.3,14 3.5,14C2.7,14 2,13.3 2,12.5C2,11.7 2.7,11 3.5,11M5,3H8V5H5V10H3V5H0V3H3V0H5V3M19,0H21V3H24V5H21V10H19V5H16V3H19V0M12,12C14.67,12 20,13.34 20,16V18H4V16C4,13.34 9.33,12 12,12Z" /></svg>');
    top: 70%;
    left: 15%;
    animation-delay: 5s;
}

@keyframes float {
    0% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
    100% { transform: translateY(0) rotate(360deg); }
}

.progress-tracker {
    margin-bottom: 2.5rem;
    position: relative;
    z-index: 1;
}

.progress-bar {
    height: 6px;
    background-color: #f0f0f0;
    border-radius: 3px;
    position: absolute;
    top: 24px;
    left: 0;
    right: 0;
    margin: 0 12%;
    z-index: 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
    transition: width 0.5s ease;
}

.steps-container {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 1;
    padding: 0 10%;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0.5;
    transform: scale(0.9);
}

.step.active {
    opacity: 1;
    transform: scale(1);
}

.step-icon {
    width: 48px;
    height: 48px;
    background-color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px var(--shadow-color);
    margin-bottom: 8px;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.step.active .step-icon {
    background-color: var(--primary-color);
    color: white;
}

.step-icon svg {
    width: 24px;
    height: 24px;
    fill: var(--dark-color);
    transition: all 0.3s ease;
}

.step.active .step-icon svg {
    fill: white;
}

.step-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--dark-color);
    text-align: center;
    transition: all 0.3s ease;
}

.step.active .step-label {
    color: var(--primary-dark);
    font-weight: 600;
}

.builder-content {
    display: flex;
    gap: 2rem;
    min-height: 600px;
    position: relative;
    z-index: 1;
}

.products-panel {
    flex: 1;
    background-color: white;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 107, 139, 0.2);
    overflow: hidden;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.panel-title {
    font-size: 1.5rem;
    color: var(--dark-color);
    font-weight: 600;
    margin: 0;
    position: relative;
}

.panel-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 0;
    width: 40px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
}

.step-counter {
    background-color: var(--light-color);
    color: var(--primary-dark);
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.products-grid {
    flex: 1;
    overflow-y: auto;
    padding: 4px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1rem;
    align-content: start;
}

.preview-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.preview-container {
    flex: 1;
    background-color: white;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    padding: 2rem;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(255, 107, 139, 0.2);
    position: relative;
    overflow: hidden;
}

.bouquet-display {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin-bottom: 2rem;
}

.pattern-preview {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pattern-image {
    width: 200px;
    height: 200px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    opacity: 0.7;
}

.animated-cover {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    width: 100%;
    height: 300px;
    perspective: 1000px;
}

.cover-image {
    width: 200px;
    height: 200px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    transform-style: preserve-3d;
    position: relative;
    z-index: 2;
}

.bouquet-circle {
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--light-color), white);
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    box-shadow: 0 10px 30px rgba(255, 107, 139, 0.2);
    transition: all 0.5s ease;
    margin-left: -50px;
}

.flowers-preview {
    width: 90%;
    height: 90%;
    border-radius: 50%;
    position: relative;
}

.circle-decoration {
    position: absolute;
    width: 110%;
    height: 110%;
    border-radius: 50%;
    border: 2px dashed var(--secondary-color);
    animation: rotate 60s linear infinite;
}

.circle-decoration-2 {
    position: absolute;
    width: 120%;
    height: 120%;
    border-radius: 50%;
    border: 1px dashed rgba(255, 107, 139, 0.3);
    animation: rotate 120s linear infinite reverse;
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.bouquet-shine {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.8) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
    opacity: 0.5;
}

.product-item {
    background-color: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    border: 1px solid rgba(0, 0, 0, 0.05);
    padding-bottom: 10px;
}

.product-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px var(--shadow-color);
    border-color: var(--primary-color);
}

.product-item.selected {
    border: 2px solid var(--primary-color);
}

.product-item img {
    width: 100%;
    height: 140px;
    object-fit: contain;
    padding: 1rem;
    background-color: var(--light-color);
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.product-item h4 {
    font-size: 14px;
    margin: 0.5rem;
    color: var(--dark-color);
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-item p {
    font-size: 14px;
    margin: 0.5rem;
    color: var(--primary-dark);
    font-weight: 600;
    text-align: center;
}

.flower-quantity-selector {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 10px;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    border: none;
    font-size: 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.quantity-btn:hover {
    background-color: var(--primary-dark);
}

.quantity-btn:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

.quantity-input {
    width: 40px;
    text-align: center;
    margin: 0 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 5px;
}

.quantity-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    background-color: var(--primary-dark);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 2;
}

.flower-in-preview {
    position: absolute;
    width: 40px;
    height: 40px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.bouquet-summary {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.summary-title {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
    text-align: center;
    position: relative;
}

.summary-title::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
    border-radius: 3px;
}

.summary-details {
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed rgba(0, 0, 0, 0.1);
}

.summary-label {
    font-weight: 500;
    color: var(--dark-color);
}

.summary-value {
    font-weight: 600;
}

.summary-value.price {
    color: var(--primary-dark);
}

.summary-flower-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 14px;
}

.flower-summary {
    max-height: 200px;
    overflow-y: auto;
    margin: 1rem 0;
    padding: 1rem;
    background-color: var(--light-color);
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.builder-navigation {
    display: flex;
    justify-content: space-between;
    margin-top: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn svg {
    width: 20px;
    height: 20px;
    margin: 0 4px;
    transition: all 0.3s ease;
}

.btn-prev {
    background-color: white;
    color: var(--dark-color);
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.btn-prev:hover {
    background-color: #f8f8f8;
    border-color: rgba(0, 0, 0, 0.2);
}

.btn-prev svg {
    margin-right: 8px;
}

.btn-next {
    background: linear-gradient(90deg, var(--primary-color), var(--primary-dark));
    color: white;
    box-shadow: 0 4px 12px var(--shadow-color);
}

.btn-next:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px var(--shadow-color);
}

.btn-next:disabled {
    background: #cccccc;
    box-shadow: none;
    transform: none;
    cursor: not-allowed;
}

.btn-next svg {
    margin-left: 8px;
    fill: white;
}

#add-to-cart-btn {
    background: linear-gradient(90deg, var(--success-color), #00b894);
    color: white;
    width: 100%;
    padding: 1rem;
    font-size: 1rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(85, 239, 196, 0.3);
    margin-top: 1rem;
}

#add-to-cart-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(85, 239, 196, 0.4);
}

.confetti-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1000;
    display: none;
}

.confetti {
    position: absolute;
    width: 10px;
    height: 10px;
    background-color: var(--primary-color);
    opacity: 0;
    animation: confetti-fall 5s linear forwards;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}

@media (max-width: 1200px) {
    .bouquet-circle {
        width: 250px;
        height: 250px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
}

@media (max-width: 992px) {
    .builder-content {
        flex-direction: column;
    }
    
    .bouquet-display {
        margin-bottom: 1rem;
    }
    
    .bouquet-summary {
        margin-top: 1rem;
        padding-top: 1rem;
    }
}

@media (max-width: 768px) {
    .gift-builder-container {
        padding: 1rem;
    }
    
    .steps-container {
        padding: 0 5%;
    }
    
    .step-icon {
        width: 36px;
        height: 36px;
    }
    
    .step-icon svg {
        width: 18px;
        height: 18px;
    }
    
    .step-label {
        font-size: 12px;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    .bouquet-circle {
        width: 200px;
        height: 200px;
    }
}

@media (max-width: 576px) {
    .progress-bar {
        margin: 0 15%;
    }
    
    .steps-container {
        padding: 0;
    }
    
    .step {
        transform: scale(0.8);
    }
    
    .step.active {
        transform: scale(0.9);
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    jQuery(function($) {
        // Constants
        const CURRENCY = '<?php echo CURRENCY; ?>';
        const BASE_URL = '<?php echo BASE_URL; ?>';
        
        // Global variables
        let currentStep = 1;
        let selectedPattern = null;
        let selectedCover = null;
        let selectedFlowers = {};
        let totalFlowers = 0;
        
        // Initialize builder
        function initBuilder() {
            loadStep(currentStep);
            updateUI();
            
            // Navigation buttons
            $('#next-btn').on('click', nextStep);
            $('#prev-btn').on('click', prevStep);
        }
        
        // Load step content
        function loadStep(step) {
            $('.products-grid').empty().hide();
            
            switch(step) {
                case 1: loadPatterns(); break;
                case 2: loadCovers(); break;
                case 3: loadFlowers(); break;
                case 4: showSummary(); break;
            }
            
            updateStepCounter();
            updateProgressBar();
            $('.products-grid').fadeIn(300);
        }
        
        // Load pattern selection
        function loadPatterns() {
            $.get('api/get_pattern.php', function(response) {
                if (response.success && response.data) {
                    let html = '';
                    response.data.forEach(pattern => {
                        html += `
                            <div class="product-item" data-id="${pattern.id}" data-name="${pattern.name}">
                                <img src="${BASE_URL}/admin/assets/images/patterns/${pattern.image_url}" 
                                     alt="${pattern.name}"
                                     onerror="this.onerror=null;this.src='${BASE_URL}/assets/images/default-pattern.png'">
                                <h4>${pattern.name}</h4>
                                <p>${pattern.description || ''}</p>
                            </div>
                        `;
                    });
                    
                    $('.products-grid').html(html);
                    
                    $('.product-item').on('click', function() {
                        $('.product-item').removeClass('selected');
                        $(this).addClass('selected');
                        selectedPattern = {
                            id: $(this).data('id'),
                            name: $(this).data('name'),
                            image: $(this).find('img').attr('src')
                        };
                        
                        // Update preview
                        $('.pattern-image').css('background-image', `url(${selectedPattern.image})`);
                        $('#pattern-summary').text(selectedPattern.name);
                        
                        // Animate selection
                        animateSelection($(this));
                    });
                }
            });
        }
        
        // Load cover selection
        function loadCovers() {
            $.get('api/get_bouquet_covers.php', function(covers) {
                let html = '';
                covers.forEach(cover => {
                    html += `
                        <div class="product-item" data-id="${cover.id}" data-name="${cover.name}" data-price="${cover.price}">
                            <img src="${BASE_URL}/admin/assets/images/covers/${cover.image_url}" 
                                 alt="${cover.name}"
                                 onerror="this.onerror=null;this.src='${BASE_URL}/assets/images/default-cover.png'">
                            <h4>${cover.name}</h4>
                            <p>${cover.price} ${CURRENCY}</p>
                        </div>
                    `;
                });
                
                $('.products-grid').html(html);
                
                $('.product-item').on('click', function() {
                    $('.product-item').removeClass('selected');
                    $(this).addClass('selected');
                    selectedCover = {
                        id: $(this).data('id'),
                        name: $(this).data('name'),
                        price: $(this).data('price'),
                        image: $(this).find('img').attr('src')
                    };
                    
                    // Update preview
                    $('.cover-image').css('background-image', `url(${selectedCover.image})`);
                    $('#cover-summary').text(selectedCover.name);
                    
                    // Animate selection
                    animateSelection($(this));
                });
            });
        }
        
        // Load flower selection with quantity picker
        function loadFlowers() {
            $.get('api/get_flowers.php', function(response) {
                if (response.success && response.data) {
                    let html = '';
                    response.data.forEach(flower => {
                        const quantity = selectedFlowers[flower.id]?.quantity || 0;
                        const isSelected = quantity > 0;
                        
                        html += `
                            <div class="product-item flower-item ${isSelected ? 'selected' : ''}" 
                                 data-id="${flower.id}" 
                                 data-name="${flower.name}"
                                 data-price="${flower.price_per_unit}">
                                                                <img src="${BASE_URL}/admin/assets/images/flowers/${flower.image_url}" 
                                     alt="${flower.name}"
                                     onerror="this.onerror=null;this.src='${BASE_URL}/assets/images/default-flower.png'">
                                <h4>${flower.name}</h4>
                                <p>${flower.price_per_unit} ${CURRENCY} per stem</p>
                                <div class="flower-quantity-selector">
                                    <button class="quantity-btn minus" data-id="${flower.id}">-</button>
                                    <input type="number" class="quantity-input" 
                                           data-id="${flower.id}" 
                                           value="${quantity}" 
                                           min="0" max="20">
                                    <button class="quantity-btn plus" data-id="${flower.id}">+</button>
                                </div>
                                ${quantity > 0 ? `<div class="quantity-badge">${quantity}</div>` : ''}
                            </div>
                        `;
                    });
                    
                    $('.products-grid').html(html);
                    
                    // Quantity controls
                    $('.quantity-btn').on('click', function() {
                        const flowerId = $(this).data('id');
                        const isPlus = $(this).hasClass('plus');
                        const input = $(`.quantity-input[data-id="${flowerId}"]`);
                        let value = parseInt(input.val()) || 0;
                        
                        if (isPlus) {
                            if (value < 20 && totalFlowers < 20) {
                                value++;
                            }
                        } else {
                            if (value > 0) {
                                value--;
                            }
                        }
                        
                        input.val(value);
                        updateFlowerSelection(flowerId, value);
                    });
                    
                    $('.quantity-input').on('change', function() {
                        const flowerId = $(this).data('id');
                        let value = parseInt($(this).val()) || 0;
                        
                        if (value < 0) value = 0;
                        if (value > 20) value = 20;
                        
                        $(this).val(value);
                        updateFlowerSelection(flowerId, value);
                    });
                }
            });
        }
        
        // Update flower selection
        function updateFlowerSelection(flowerId, quantity) {
            const flowerItem = $(`.flower-item[data-id="${flowerId}"]`);
            const badge = flowerItem.find('.quantity-badge');
            
            if (quantity > 0) {
                // Update total count
                const prevQuantity = selectedFlowers[flowerId]?.quantity || 0;
                totalFlowers = totalFlowers - prevQuantity + quantity;
                
                // Update selection
                selectedFlowers[flowerId] = {
                    id: flowerId,
                    name: flowerItem.data('name'),
                    price: flowerItem.data('price'),
                    quantity: quantity,
                    image: flowerItem.find('img').attr('src')
                };
                
                // Update UI
                flowerItem.addClass('selected');
                if (badge.length) {
                    badge.text(quantity);
                } else {
                    flowerItem.append(`<div class="quantity-badge">${quantity}</div>`);
                }
            } else {
                // Remove from selection
                totalFlowers -= selectedFlowers[flowerId]?.quantity || 0;
                delete selectedFlowers[flowerId];
                
                // Update UI
                flowerItem.removeClass('selected');
                badge.remove();
            }
            
            // Update flowers summary
            updateFlowersSummary();
            
            // Update preview
            updateFlowersPreview();
        }
        
        // Update flowers preview
        function updateFlowersPreview() {
            $('.flowers-preview').empty();
            $('#flowers-summary').text(`${totalFlowers} selected`);
            
            if (totalFlowers === 0) return;
            
            // Calculate positions in a circle
            const radius = 100;
            const centerX = 150;
            const centerY = 150;
            const angleStep = (2 * Math.PI) / totalFlowers;
            
            let index = 0;
            for (const flowerId in selectedFlowers) {
                const flower = selectedFlowers[flowerId];
                const angle = index * angleStep;
                const x = centerX + radius * Math.cos(angle) - 20;
                const y = centerY + radius * Math.sin(angle) - 20;
                
                // Create flower element
                const flowerEl = $(`<div class="flower-in-preview" 
                    style="background-image: url(${flower.image});
                    left: ${x}px; top: ${y}px;
                    transform: rotate(${angle}rad);
                    z-index: ${Math.floor(flower.quantity / 2)}"></div>`);
                
                $('.flowers-preview').append(flowerEl);
                
                // Add multiple flowers for quantity > 1
                for (let i = 1; i < flower.quantity; i++) {
                    const offsetX = 5 + Math.random() * 10;
                    const offsetY = 5 + Math.random() * 10;
                    const offsetEl = $(`<div class="flower-in-preview" 
                        style="background-image: url(${flower.image});
                        left: ${x + offsetX}px; top: ${y + offsetY}px;
                        transform: rotate(${angle}rad);
                        opacity: ${0.7 + (i * 0.1)};
                        z-index: ${Math.floor(flower.quantity / 2)}"></div>`);
                    
                    $('.flowers-preview').append(offsetEl);
                }
                
                index++;
            }
        }
        
        // Show summary before adding to cart
        function showSummary() {
            let html = `
                <div class="summary-item">
                    <span class="summary-label">Pattern:</span>
                    <span class="summary-value">${selectedPattern?.name || 'Not selected'}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Cover:</span>
                    <span class="summary-value">${selectedCover?.name || 'Not selected'}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Flowers:</span>
                    <span class="summary-value">${totalFlowers}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-label">Total Price:</span>
                    <span class="summary-value price">${calculateTotalPrice()} ${CURRENCY}</span>
                </div>
            `;
            
            // Add flowers breakdown
            if (totalFlowers > 0) {
                html += `<div class="flower-summary">`;
                for (const flowerId in selectedFlowers) {
                    const flower = selectedFlowers[flowerId];
                    html += `
                        <div class="summary-flower-item">
                            <span>${flower.name} (${flower.quantity})</span>
                            <span>${(flower.price * flower.quantity).toFixed(2)} ${CURRENCY}</span>
                        </div>
                    `;
                }
                html += `</div>`;
            }
            
            $('.products-grid').html(html);
            
            // Add "Add to Cart" button
            $('.products-grid').append(`
                <button id="add-to-cart-btn" class="btn">
                    <svg viewBox="0 0 24 24">
                        <path d="M17,18A2,2 0 0,1 19,20A2,2 0 0,1 17,22C15.89,22 15,21.1 15,20C15,18.89 15.89,18 17,18M1,2H4.27L5.21,4H20A1,1 0 0,1 21,5C21,5.17 20.95,5.34 20.88,5.5L17.3,11.97C16.96,12.58 16.3,13 15.55,13H8.1L7.2,14.63L7.17,14.75A0.25,0.25 0 0,0 7.42,15H19V17H7A2,2 0 0,1 5,15C5,14.65 5.09,14.32 5.24,14.04L6.6,11.59L3,4H1V2M7,18A2,2 0 0,1 9,20A2,2 0 0,1 7,22C5.89,22 5,21.1 5,20C5,18.89 5.89,18 7,18M16,11L18.78,6H6.14L8.5,11H16Z" />
                    </svg>
                    Add to Cart
                </button>
            `);
            
            $('#add-to-cart-btn').on('click', addToCart);
        }
        
        // Calculate total price
        function calculateTotalPrice() {
    let total = 0;

    if (selectedCover) {
        total += parseFloat(selectedCover.price) || 0;
    }

    for (const flowerId in selectedFlowers) {
        const flower = selectedFlowers[flowerId];
        const price = parseFloat(flower.price) || 0;
        const qty = parseInt(flower.quantity) || 0;
        total += price * qty;
    }

    return total.toFixed(2);
}

function updateFlowersSummary() {
    let summary = '';
    for (const flowerId in selectedFlowers) {
        const flower = selectedFlowers[flowerId];
        summary += `${flower.name} x ${flower.quantity}<br>`;
    }
    $('#flowers-summary').html(summary);
}
        
        
        
        
        
        
        async function generateBouquetImage() {
    // Create a canvas element
    const canvas = document.createElement('canvas');
    canvas.width = 800;
    canvas.height = 600;
    const ctx = canvas.getContext('2d');
    
    // Draw background
    ctx.fillStyle = '#fff5f7';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw cover (if selected)
    if (selectedCover) {
        const coverImg = await loadImage(selectedCover.image);
        ctx.drawImage(coverImg, 50, 50, 300, 400);
    }
    
    // Draw flowers in circular arrangement
    const centerX = 550;
    const centerY = 300;
    const radius = 150;
    
    if (totalFlowers > 0) {
        const angleStep = (2 * Math.PI) / totalFlowers;
        let index = 0;
        
        for (const flowerId in selectedFlowers) {
            const flower = selectedFlowers[flowerId];
            const angle = index * angleStep;
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            
            const flowerImg = await loadImage(flower.image);
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.drawImage(flowerImg, -20, -20, 40, 40);
            ctx.restore();
            
            // Draw quantity indicator
            if (flower.quantity > 1) {
                ctx.fillStyle = '#ff4757';
                ctx.beginPath();
                ctx.arc(x + 15, y - 15, 12, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = 'white';
                ctx.font = 'bold 12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(flower.quantity.toString(), x + 15, y - 15);
            }
            
            index++;
        }
    }
    
    // Add title and details
    ctx.fillStyle = '#2d3436';
    ctx.font = 'bold 24px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Custom Bouquet Design', canvas.width/2, 30);
    
    ctx.font = '16px Arial';
    ctx.fillText(`Cover: ${selectedCover?.name || 'None'}`, canvas.width/2, 550);
    ctx.fillText(`Total Flowers: ${totalFlowers}`, canvas.width/2, 580);
    
    // Convert canvas to data URL
    return canvas.toDataURL('image/png');
}

// Helper function to load images
function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}
        // Add custom bouquet to cart
      async function addToCart() {
    if (totalFlowers === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Please select at least one flower for your bouquet!'
        });
        return;
    }

    // Generate the bouquet image
    const imageData = await generateBouquetImage();
    
    const bouquetData = {
        cover_id: selectedCover.id,
        flower_count: totalFlowers,
        flowers: Object.values(selectedFlowers).map(flower => ({
            id: flower.id,
            quantity: flower.quantity
        })),
        total_price: calculateTotalPrice(),
        image_data: imageData // Add the generated image
    };

    $.ajax({
        url: 'api/add_custom_bouquet.php',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(bouquetData),
        success: function(response) {
            if (response.success) {
                showConfetti();
                Swal.fire({
                    icon: 'success',
                    title: 'Added to Cart!',
                    text: 'Your custom bouquet has been added to your cart.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = 'cart.php';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to add to cart. Please try again.'
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'An error occurred. Please try again.'
            });
        }
    });
}
        
        // Navigation functions
        function nextStep() {
            if (validateStep(currentStep)) {
                currentStep++;
                loadStep(currentStep);
                updateUI();
            }
        }
        
        function prevStep() {
            currentStep--;
            loadStep(currentStep);
            updateUI();
        }
        
        // Validate step before proceeding
        function validateStep(step) {
            switch(step) {
                case 1:
                    if (!selectedPattern) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pattern Required',
                            text: 'Please select a pattern for your bouquet.'
                        });
                        return false;
                    }
                    break;
                case 2:
                    if (!selectedCover) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Cover Required',
                            text: 'Please select a cover for your bouquet.'
                        });
                        return false;
                    }
                    break;
            }
            return true;
        }
        
        // Update UI elements
        function updateUI() {
            // Update step indicators
            $('.step').removeClass('active');
            $(`.step[data-step="${currentStep}"]`).addClass('active');
            
            // Update navigation buttons
            $('#prev-btn').prop('disabled', currentStep === 1);
            $('#next-btn').text(currentStep === 4 ? 'Complete' : 'Next');
            
            // Update progress bar
            updateProgressBar();
        }
        
        // Update progress bar
        function updateProgressBar() {
            const percentage = ((currentStep - 1) / 3) * 100;
            $('.progress-fill').css('width', `${percentage}%`);
        }
        
        // Update step counter
        function updateStepCounter() {
            $('.current-step').text(currentStep);
        }
        
        // Animate selection
        function animateSelection(element) {
            gsap.fromTo(element, 
                { scale: 0.95 }, 
                { scale: 1.05, duration: 0.2, yoyo: true, repeat: 1 }
            );
        }
        
        // Show confetti animation
        function showConfetti() {
            const container = $('.confetti-container');
            container.empty().show();
            
            for (let i = 0; i < 100; i++) {
                const confetti = $('<div class="confetti"></div>');
                confetti.css({
                    left: `${Math.random() * 100}%`,
                    backgroundColor: getRandomColor(),
                    animationDuration: `${3 + Math.random() * 4}s`,
                    animationDelay: `${Math.random() * 2}s`
                });
                container.append(confetti);
            }
            
            setTimeout(() => container.fadeOut(), 5000);
        }
        
        async function generateBouquetImage() {
    // Create a canvas element
    const canvas = document.createElement('canvas');
    canvas.width = 800;
    canvas.height = 600;
    const ctx = canvas.getContext('2d');
    
    // Draw background
    ctx.fillStyle = '#fff5f7';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    
    // Draw cover (if selected)
    if (selectedCover) {
        const coverImg = await loadImage(selectedCover.image);
        ctx.drawImage(coverImg, 50, 50, 300, 400);
    }
    
    // Draw flowers in circular arrangement
    const centerX = 550;
    const centerY = 300;
    const radius = 150;
    
    if (totalFlowers > 0) {
        const angleStep = (2 * Math.PI) / totalFlowers;
        let index = 0;
        
        for (const flowerId in selectedFlowers) {
            const flower = selectedFlowers[flowerId];
            const angle = index * angleStep;
            const x = centerX + radius * Math.cos(angle);
            const y = centerY + radius * Math.sin(angle);
            
            const flowerImg = await loadImage(flower.image);
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle);
            ctx.drawImage(flowerImg, -20, -20, 40, 40);
            ctx.restore();
            
            // Draw quantity indicator
            if (flower.quantity > 1) {
                ctx.fillStyle = '#ff4757';
                ctx.beginPath();
                ctx.arc(x + 15, y - 15, 12, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = 'white';
                ctx.font = 'bold 12px Arial';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(flower.quantity.toString(), x + 15, y - 15);
            }
            
            index++;
        }
    }
    
    // Add title and details
    ctx.fillStyle = '#2d3436';
    ctx.font = 'bold 24px Arial';
    ctx.textAlign = 'center';
    ctx.fillText('Custom Bouquet Design', canvas.width/2, 30);
    
    ctx.font = '16px Arial';
    ctx.fillText(`Cover: ${selectedCover?.name || 'None'}`, canvas.width/2, 550);
    ctx.fillText(`Total Flowers: ${totalFlowers}`, canvas.width/2, 580);
    
    // Convert canvas to data URL
    return canvas.toDataURL('image/png');
}

// Helper function to load images
function loadImage(src) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = src;
    });
}
        
        // Get random color for confetti
        function getRandomColor() {
            const colors = [
                '#ff6b8b', '#ff4757', '#ffb8c6', '#ff9eb5',
                '#55efc4', '#00b894', '#74b9ff', '#0984e3',
                '#fdcb6e', '#e17055', '#a29bfe', '#6c5ce7'
            ];
            return colors[Math.floor(Math.random() * colors.length)];
        }
        
        // Initialize the builder
        initBuilder();
    });
});
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
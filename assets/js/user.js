/**
 * Plant Store - Main JavaScript File
 * Handles chatbot, shop filtering, cart functionality, and language switching
 */

// Constants
const PATHS = {
    chatbot: `${BASE_URL}/pages/chatbot/chatbot.php`,
    addToCart: `${BASE_URL}/pages/cart/add_to_cart.php`, 
    cartCount: `${BASE_URL}/pages/cart/get_cart_count.php`
};
// Main Initialization
document.addEventListener('DOMContentLoaded', function() {
    try {
        initChatbot();

        if (document.getElementById('plant-list')) {
            initShopPage();
        }
        emptyCart();
        initCart();
        loadInitialCartCount();
        initLanguageSwitcher();
        applyRTLStyles();

    } catch (error) {
        
    }
});

// ==================== CHATBOT FUNCTIONALITY ====================
function initChatbot() {
    const elements = {
        toggle: document.getElementById('chatbot-toggle'),
        box: document.getElementById('chatbot-box'),
        close: document.getElementById('chatbot-close'),
        send: document.getElementById('chatbot-send'),
        input: document.getElementById('chatbot-input'),
        messages: document.getElementById('chatbot-messages')
    };

   

    // Set up event listeners
    elements.toggle.addEventListener('click', () => toggleChatbot(elements, true));
    elements.close.addEventListener('click', () => toggleChatbot(elements, false));
    elements.send.addEventListener('click', () => sendChatbotMessage(elements));
    elements.input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendChatbotMessage(elements);
    });

    // Add welcome message if empty
    if (elements.messages.children.length === 0) {
        addMessage(elements.messages, 'bot', 'Hello! I\'m your plant assistant. How can I help you today?');
    }
}

async function sendChatbotMessage(elements) {
    const message = elements.input.value.trim();
    if (!message) return;

    // Add user message and clear input
    addMessage(elements.messages, 'user', message);
    elements.input.value = '';
    elements.input.disabled = true;

    // Create loading indicator with unique ID
    const loadingId = 'loading-' + Date.now();
    const loadingDiv = document.createElement('div');
    loadingDiv.id = loadingId;
    loadingDiv.className = 'bot-message message-bubble';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Thinking...';

    // Safely add to DOM
    if (elements.messages) {
        elements.messages.appendChild(loadingDiv);
        elements.messages.scrollTop = elements.messages.scrollHeight;
    }

    try {
        const response = await fetch('pages/chatbot.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: 'query=' + encodeURIComponent(message),
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Display chatbot response
            displayMessage(data.response, 'bot');
          } else {
            console.error('Chatbot error:', data.response);
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
        });

        // Safely remove loading indicator
        removeLoadingIndicator(loadingId);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new TypeError("Invalid response format");
        }

        const data = await response.json();

        if (data.success) {
            addMessage(elements.messages, 'bot', data.response);
        } else {
            throw new Error(data.error || 'Unknown error from server');
        }
    } catch (error) {
        removeLoadingIndicator(loadingId);
        addMessage(elements.messages, 'bot', "I'm having trouble connecting. Please try again later.");
        console.error("Chatbot error:", error);
    } finally {
        elements.input.disabled = false;
        elements.input.focus();
    }
}

// Utility function to remove loading indicator
function removeLoadingIndicator(id) {
    const loadingElement = document.getElementById(id);
    if (loadingElement && loadingElement.parentNode) {
        loadingElement.parentNode.removeChild(loadingElement);
    }
}

function toggleChatbot(elements, show) {
    elements.box.classList.toggle('scale-0', !show);
    elements.toggle.classList.toggle('scale-0', show);
    setTimeout(() => {
        elements.close.classList.toggle('hidden', !show);
    }, 300);
}


// ==================== SHOP FUNCTIONALITY ====================
function initShopPage() {
    const searchInput = document.getElementById('search');
    const filterSelect = document.getElementById('filter');
    const plantItems = document.querySelectorAll('.plant-item');

    if (filterSelect) {
        filterSelect.addEventListener('change', filterPlants);
    }

    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterPlants, 300));
    }

    function filterPlants() {
        const category = filterSelect ? filterSelect.value.toLowerCase() : '';
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';

        plantItems.forEach(item => {
            const itemCategory = item.getAttribute('data-category')?.toLowerCase() || '';
            const plantName = item.querySelector('h5')?.textContent.toLowerCase() || '';
            
            const categoryMatch = category === '' || itemCategory.includes(category);
            const searchMatch = plantName.includes(searchTerm);
            
            item.style.display = (categoryMatch && searchMatch) ? 'flex' : 'none';
        });
    }
}

// ==================== CART FUNCTIONALITY ====================
function initCart() {
    // Quantity buttons
    document.querySelectorAll('.minus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.nextElementSibling;
            if (input.value > 1) input.value--;
        });
    });

    document.querySelectorAll('.plus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const max = parseInt(input.getAttribute('max')) || Infinity;
            if (parseInt(input.value) < max) input.value++;
        });
    });

    // Add to cart forms
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            const quantity = this.querySelector('.quantity').value;
            
            // Show loading state
            const submitBtn = this.querySelector('[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
            
            try {
                await addToCart(productId, quantity);
            } catch (error) {
                // Error already handled in addToCart
            } finally {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    });
    
    // Listen for cart updates from other tabs
    window.addEventListener('storage', (e) => {
        if (e.key === 'cartCount') {
            updateCartCount(parseInt(e.newValue));
        }
    });
}

function loadInitialCartCount() {
    // First check localStorage
    const storedCount = localStorage.getItem('cartCount');
    if (storedCount) {
        updateCartCount(parseInt(storedCount));
    }

    // Then verify with server
    fetch(PATHS.cartCount, {
        credentials: 'include'
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            updateCartCount(data.count);
            localStorage.setItem('cartCount', data.count);
        }
    })
    .catch(error => {
        console.error('Failed to load cart count:', error);
        // Maintain existing count from localStorage if available
    });
}

// ==================== LANGUAGE & RTL ====================
function initLanguageSwitcher() {
    document.querySelectorAll('.language-switcher a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.getAttribute('href').split('=')[1];
            window.location.href = `${window.location.pathname}?lang=${lang}${window.location.hash}`;
        });
    });
}

function applyRTLStyles() {
    if (document.documentElement.dir === 'rtl') {
        document.querySelectorAll('.plant-footer, .plant-price-stock').forEach(el => {
            el.style.flexDirection = 'row-reverse';
        });
    }
}

// ==================== UTILITY FUNCTIONS ====================
function addMessage(container, sender, message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `${sender}-message message-bubble`;
    
    const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    messageDiv.innerHTML = `
        <strong>${sender === 'user' ? 'You' : 'Assistant'}:</strong> ${message}
        <div class="message-time">${time}</div>
    `;
    
    container.appendChild(messageDiv);
    container.scrollTop = container.scrollHeight;
}

async function addToCart(productId, quantity = 1) {
    try {
        // Show loading state
        const submitBtn = document.querySelector(`.add-to-cart-form[data-product-id="${productId}"] [type="submit"]`);
        const originalText = submitBtn?.innerHTML;
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
        }

        const response = await fetch(PATHS.addToCart, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`,
            credentials: 'include'
        });

        // Check response type
        const contentType = response.headers.get('content-type');
        if (!contentType?.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Server returned: ${text}`);
        }

        const data = await response.json();
        
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to add to cart');
        }
        
        // Update cart count and UI
        await updateCartCount();
        showAlert('success', data.message || 'Added to cart successfully!');
        
        // Update any quantity displays
        // updateCartItemDisplays(productId, data.newQuantity);
        
        return data;
        
    } catch (error) {
        console.error('Cart error:', error);
        showAlert('danger', error.message || 'Failed to update cart');
        throw error;
    } finally {
        // Restore button state
        if (submitBtn && originalText) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }
}


function emptyCart() {
    localStorage.setItem('cartCount', 0);
    updateCartCount(0);
    
    // Send to server if logged in
    if (document.body.classList.contains('logged-in')) {
        fetch(`${BASE_URL}/pages/cart/update_cart_count.php?count=0`, {
            credentials: 'include'
        });
    }
}


function updateCartCount() {
    fetch('pages/cart/get_cart_count.php', {
        method: 'GET',
        credentials: 'same-origin' // Important for session cookies
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update all elements with class 'cart-count'
            document.querySelectorAll('.cart-count').forEach(element => {
                element.textContent = data.count;
            });
            // Update session cart count
            if (typeof updateCartBadge === 'function') {
                updateCartBadge(data.count);
            }
        }
    })
    .catch(error => {
        console.error('Error fetching cart count:', error);
    });
}

// Call this function whenever the cart changes
function handleCartChange() {
    updateCartCount();
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

function showAlert(type, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    alert.style.zIndex = '1100';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

function safeRemoveElement(id) {
    const element = document.getElementById(id);
    if (element && element.parentNode) {
        element.parentNode.removeChild(element);
    }
}

function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}
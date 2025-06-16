/**
 * PlantsStore Admin - Central JavaScript File
 * Combines all functionality for the admin panel
 */

// ==================== GLOBAL FUNCTIONS ====================
// Available immediately (not waiting for DOMContentLoaded)









function openAddProductModal() {
    const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
    modal.show();
}

async function getProductWithImages(productId) {
    try {
        showNotification('info', 'Loading plant data...', 2000);
        const response = await fetch(`actions/get_product.php?id=${productId}`);
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load plant');
        
        // ✅ The important fix:
        populateEditModal(data.data);
        
        showNotification('success', 'Plant loaded successfully');
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', `Failed to load plant: ${error.message}`);
    }
}



// Run payroll function
function runPayroll() {
    if (confirm('Are you sure you want to process payroll for all admins? This will calculate commissions and create payment records.')) {
        fetch('actions/run_payroll.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ csrf_token: window.csrfToken })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Payroll processed successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.error || 'Failed to process payroll'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing payroll');
        });
    }
}
function showNotification(type, message, duration = 5000) {
    const existingNotifications = document.querySelectorAll('.notification-container');
    existingNotifications.forEach(notification => notification.remove());
    
    const container = document.createElement('div');
    container.className = 'notification-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    
    const iconClass = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    }[type] || 'fa-info-circle';
    
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    document.body.appendChild(container);
    const alert = container.querySelector('.alert');
    const bsAlert = new bootstrap.Alert(alert);
    
    if (duration > 0) {
        setTimeout(() => bsAlert.close(), duration);
    }
    
    alert.addEventListener('closed.bs.alert', () => container.remove());
}

// ==================== DOM-READY FUNCTIONS ====================
document.addEventListener('DOMContentLoaded', function() {
    // Initialize core functionality
    initDarkMode();
    
    // setupImagePreview();
    setupAddProductImagePreview(); // Specific setup for add product modal
    setupProductSearch();
    setupFormValidation();
    setupPasswordStrengthIndicator();
    
    
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', toggleDarkMode);
    }
    
    // Sidebar toggle for mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }

    // Event delegation for dynamic elements
    document.addEventListener('click', function(e) {
        // Add product button
        if (e.target.id === 'addProductButton' || e.target.closest('#addProductButton')) {
            openAddProductModal();
        }
        
        // Product edit buttons
        if (e.target.classList.contains('edit-product-btn') || e.target.closest('.edit-product-btn')) {
            const productId = e.target.dataset.productId || e.target.closest('.edit-product-btn').dataset.productId;
            getProductWithImages(productId);
        }
        
        // Delete confirmation
        if (e.target.matches('a[data-confirm]') && !confirm(e.target.getAttribute('data-confirm'))) {
            e.preventDefault();
        }
    });

    // Check for URL notifications
    const urlParams = new URLSearchParams(window.location.search);
    const notification = urlParams.get('notification');
    const notificationType = urlParams.get('notification_type');
    
    if (notification && notificationType) {
        showNotification(notificationType, decodeURIComponent(notification));
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

// ==================== DARK MODE FUNCTIONS ====================
function initDarkMode() {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    document.body.classList.toggle('dark', isDarkMode);
    if (darkModeToggle) {
        darkModeToggle.checked = isDarkMode;
    }
}

function toggleDarkMode() {
    const isDarkMode = !document.body.classList.contains('dark');
    document.body.classList.toggle('dark', isDarkMode);
    localStorage.setItem('darkMode', isDarkMode);
    
    // Update charts if they exist
    if (window.updateChartsForDarkMode) {
        updateChartsForDarkMode(isDarkMode);
    }
}

// ==================== PRODUCT MANAGEMENT ====================
function populateEditModal(data) {
    const editModal = document.getElementById('editProductModal');
    if (!editModal) return;
    
    const product = data;  // ✅ now correct
    const images = data.images || [];
    
    document.getElementById('edit_product_id').value = product.product_id;
    document.getElementById('editProductModalBody').innerHTML = `
        <div class="row g-3">
            <!-- English Plant Name -->
            <div class="col-md-12">
                <label for="edit_name" class="form-label">Plant Name (English) *</label>
                <input type="text" class="form-control" name="name" id="edit_name" 
                       value="${escapeHtml(product.name)}" required>
            </div>
            
            <!-- Arabic Plant Name -->
            <div class="col-md-12">
                <label for="edit_name_ar" class="form-label">اسم النبات (العربية)</label>
                <input type="text" class="form-control" name="name_ar" id="edit_name_ar" 
                       value="${escapeHtml(product.name_ar || '')}" dir="rtl">
            </div>
            
            <!-- English Description -->
            <div class="col-md-12">
                <label for="edit_description" class="form-label">Description (English) *</label>
                <textarea class="form-control" name="description" id="edit_description" 
                          rows="3" required>${escapeHtml(product.description)}</textarea>
            </div>
            
            <!-- Arabic Description -->
            <div class="col-md-12">
                <label for="edit_description_ar" class="form-label">الوصف (العربية)</label>
                <textarea class="form-control" name="description_ar" id="edit_description_ar" 
                          rows="3" dir="rtl">${escapeHtml(product.description_ar || '')}</textarea>
            </div>
            
            <!-- Category and Price -->
            <div class="col-md-6">
                <label for="edit_category_id" class="form-label">Category *</label>
                <select class="form-select" name="category_id" id="edit_category_id" required>
                    ${generateCategoryOptions(product.category_id)}
                </select>
            </div>
            
            <div class="col-md-6">
                <label for="edit_price" class="form-label">Price (EGP) *</label>
                <input type="number" step="0.01" min="0" class="form-control" 
                       name="price" id="edit_price" value="${product.price}" required>
            </div>
            
            <!-- Stock and Environment -->
            <div class="col-md-6">
                <label for="edit_stock_quantity" class="form-label">Stock Quantity *</label>
                <input type="number" min="0" class="form-control" 
                       name="stock_quantity" id="edit_stock_quantity" value="${product.stock_quantity}" required>
            </div>
            
            <div class="col-md-6">
                <label for="edit_environment_suitability" class="form-label">Environment (English)</label>
                <input type="text" class="form-control" name="environment_suitability" 
                       id="edit_environment_suitability" value="${escapeHtml(product.environment_suitability || '')}" 
                       placeholder="Indoor, Outdoor, etc.">
            </div>
            
            <!-- Arabic Environment -->
            <div class="col-md-6">
                <label for="edit_environment_suitability_ar" class="form-label">البيئة المناسبة (العربية)</label>
                <input type="text" class="form-control" name="environment_suitability_ar" 
                       id="edit_environment_suitability_ar" value="${escapeHtml(product.environment_suitability_ar || '')}" 
                       placeholder="داخلي، خارجي، إلخ" dir="rtl">
            </div>
            
            <!-- English Care Instructions -->
            <div class="col-md-12">
                <label for="edit_care_instructions" class="form-label">Care Instructions (English)</label>
                <textarea class="form-control" name="care_instructions" 
                          id="edit_care_instructions" rows="2">${escapeHtml(product.care_instructions || '')}</textarea>
            </div>
            
            <!-- Arabic Care Instructions -->
            <div class="col-md-12">
                <label for="edit_care_instructions_ar" class="form-label">تعليمات العناية (العربية)</label>
                <textarea class="form-control" name="care_instructions_ar" 
                          id="edit_care_instructions_ar" rows="2" dir="rtl">${escapeHtml(product.care_instructions_ar || '')}</textarea>
            </div>
            
            <!-- Featured Plant -->
            <div class="col-md-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_featured" 
                           id="edit_is_featured" ${product.is_featured ? 'checked' : ''}>
                    <label class="form-check-label" for="edit_is_featured">Featured Plant</label>
                </div>
            </div>
            
            <!-- Existing Images -->
            <div class="col-md-12">
                <label class="form-label">Existing Images</label>
                <div class="d-flex flex-wrap gap-3 mb-3" id="existing-images-container">
                    ${images.map(image => `
                        <div class="position-relative">
                            <img src="assets/images/products/${escapeHtml(image.image_url)}" 
                                 class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                            <div class="position-absolute top-0 end-0">
                                <button type="button" class="btn btn-sm btn-danger rounded-circle p-0" 
                                        style="width: 20px; height: 20px;"
                                        onclick="deleteImage(this, ${image.image_id})">
                                    <i class="fas fa-times" style="font-size: 10px;"></i>
                                </button>
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="radio" name="set_primary" 
                                       id="primary_${image.image_id}" value="${image.image_id}" 
                                       ${image.is_primary ? 'checked' : ''}>
                                <label class="form-check-label small" for="primary_${image.image_id}">
                                    Primary
                                </label>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <!-- New Images -->
            <div class="col-md-12">
                <label class="form-label">Add New Images</label>
                <div class="border border-2 border-dashed rounded p-4 text-center">
                    <div class="mb-2">
                        <input type="file" id="new_images" name="new_images[]" multiple accept="image/*" class="d-none">
                        <label for="new_images" class="btn btn-sm btn-plant">
                            <i class="fas fa-upload me-1"></i> Upload files
                        </label>
                        <p class="small text-muted mt-2 mb-0">or drag and drop</p>
                        <p class="small text-muted">PNG, JPG, GIF up to 5MB each (max 5 images)</p>
                    </div>
                    <div id="new-image-preview" class="d-flex flex-wrap gap-2 mt-2 justify-content-center"></div>
                </div>
            </div>
        </div>
    `;
    
    setupEditImagePreview();
    new bootstrap.Modal(editModal).show();
}

function setupAddProductImagePreview() {
    const productImagesInput = document.getElementById('product_images');
    if (!productImagesInput) return;

    productImagesInput.addEventListener('change', function(e) {
        const preview = document.getElementById('image-preview');
        if (!preview) return;
        
        preview.innerHTML = '';
        
        if (this.files.length > 5) {
            showNotification('error', 'You can upload maximum 5 images');
            this.value = '';
            return;
        }
        
        Array.from(this.files).forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                showNotification('error', 'File exceeds 5MB limit');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                showNotification('error', 'Only image files are allowed');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'position-relative d-inline-block me-2 mb-2';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                
                // Add radio button for primary image selection
                const radioDiv = document.createElement('div');
                radioDiv.className = 'form-check mt-2';
                radioDiv.innerHTML = `
                    <input class="form-check-input" type="radio" name="primary_image" 
                           id="primary_${file.name}" value="${file.name}" ${preview.children.length === 0 ? 'checked' : ''}>
                    <label class="form-check-label small" for="primary_${file.name}">
                        Set as primary
                    </label>
                `;
                
                imgContainer.appendChild(img);
                imgContainer.appendChild(radioDiv);
                preview.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
        });
    });
}

function setupEditImagePreview() {
    const newImagesInput = document.getElementById('new_images');
    if (!newImagesInput) return;

    newImagesInput.addEventListener('change', function(e) {
        const preview = document.getElementById('new-image-preview');
        if (!preview) return;
        
        preview.innerHTML = '';
        
        if (this.files.length > 5) {
            showNotification('error', 'You can upload maximum 5 images');
            this.value = '';
            return;
        }
        
        Array.from(this.files).forEach(file => {
            if (file.size > 5 * 1024 * 1024) {
                showNotification('error', 'File exceeds 5MB limit');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            if (!file.type.startsWith('image/')) {
                showNotification('error', 'Only image files are allowed');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const imgContainer = document.createElement('div');
                imgContainer.className = 'position-relative d-inline-block me-2 mb-2';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'img-thumbnail';
                img.style.width = '100px';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                
                imgContainer.appendChild(img);
                preview.appendChild(imgContainer);
            };
            reader.readAsDataURL(file);
        });
    });
}

function closeAddProductModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
    if (modal) {
        modal.hide();
        const form = document.getElementById('addProductForm');
        if (form) form.reset();
        const preview = document.getElementById('image-preview');
        if (preview) preview.innerHTML = '';
    }
}

function deleteImage(button, imageId) {
    if (!confirm('Are you sure you want to delete this image?')) return;
    
    let deleteInput = document.querySelector('input[name="delete_images[]"]');
    if (!deleteInput) {
        deleteInput = document.createElement('input');
        deleteInput.type = 'hidden';
        deleteInput.name = 'delete_images[]';
        document.getElementById('editProductForm').appendChild(deleteInput);
    }
    
    const newInput = document.createElement('input');
    newInput.type = 'hidden';
    newInput.name = 'delete_images[]';
    newInput.value = imageId;
    document.getElementById('editProductForm').appendChild(newInput);
    
    button.closest('.position-relative').remove();
    showNotification('success', 'Image marked for deletion. Click Update to save changes.');
}

// ==================== UTILITY FUNCTIONS ====================
function escapeHtml(unsafe) {
    return unsafe?.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;") || '';
}

function generateCategoryOptions(selectedId) {
    const categories = [
        { category_id: 1, name: 'Indoor Plants' },
        { category_id: 2, name: 'Outdoor Plants' },
        { category_id: 3, name: 'Succulents' },
        { category_id: 4, name: 'Flowering Plants' },
        { category_id: 5, name: 'Plant Accessories' }
    ];
    
    return categories.map(cat => `
        <option value="${cat.category_id}" ${cat.category_id == selectedId ? 'selected' : ''}>
            ${escapeHtml(cat.name)}
        </option>
    `).join('');
}


function filterProducts(searchTerm = '', formData = null) {
    const rows = document.querySelectorAll('#productsTable tbody tr');
    const categoryFilter = formData?.get('category') || '';
    const featuredFilter = formData?.get('featured') || '';
    const stockFilter = formData?.get('stock') || '';
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        const rowCategory = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        const rowStock = parseInt(row.querySelector('td:nth-child(5)').textContent);
        const rowFeatured = row.querySelector('td:nth-child(6) .badge').textContent.toLowerCase();
        
        // Check search term match
        const searchMatch = !searchTerm || rowText.includes(searchTerm);
        
        // Check category filter
        const categoryMatch = !categoryFilter || 
                             rowCategory === document.querySelector(`#category option[value="${categoryFilter}"]`).text.toLowerCase();
        
        // Check featured filter
        const featuredMatch = !featuredFilter || 
                            (featuredFilter === '1' && rowFeatured === 'yes') ||
                            (featuredFilter === '0' && rowFeatured === 'no');
        
        // Check stock filter
        const stockMatch = !stockFilter || 
                         (stockFilter === 'low' && rowStock < 10) ||
                         (stockFilter === 'out' && rowStock === 0);
        
        // Show/hide row based on all filters
        row.style.display = (searchMatch && categoryMatch && featuredMatch && stockMatch) ? '' : 'none';
    });
}



function debounce(func, wait) {
    let timeout;
    return function(...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

// ==================== SEARCH FUNCTIONALITY ====================
function setupProductSearch() {
    const productSearch = document.getElementById('productSearch');
    if (!productSearch) return;

    // Handle instant search
    productSearch.addEventListener('input', debounce(function(e) {
        const searchTerm = e.target.value.toLowerCase();
        filterProducts(searchTerm);
    }, 300));

    // Handle form submission
    const filterForm = document.querySelector('#productsTable + .card-body form');
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const searchTerm = document.getElementById('productSearch').value.toLowerCase();
            filterProducts(searchTerm, formData);
        });
    }
}

// ==================== FORM VALIDATION ====================
function setupFormValidation() {
    // Add product form validation
    const addProductForm = document.getElementById('addProductForm');
    if (addProductForm) {
        addProductForm.addEventListener('submit', function(e) {
            const imagesInput = document.getElementById('product_images');
            if (imagesInput && imagesInput.files.length === 0) {
                e.preventDefault();
                showNotification('error', 'Please upload at least one plant image');
                return false;
            }
            return true;
        });
    }
    
    // Edit product form validation
    const editProductForm = document.getElementById('editProductForm');
    if (editProductForm) {
        editProductForm.addEventListener('submit', function(e) {
            return true;
        });
    }
}

// ==================== ORDER MANAGEMENT ====================
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`actions/get_order_details.php?id=${orderId}`);
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load order details');
        
        populateOrderDetailsModal(data);
        const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        modal.show();
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', `Failed to load order details: ${error.message}`);
    }
}

function populateOrderDetailsModal(data) {
    const order = data.order;
    const items = data.items;
    const payment = data.payment;
    
    document.getElementById('orderDetailsContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Order #${order.order_id}</h5>
                <p><strong>Date:</strong> ${new Date(order.order_date).toLocaleString()}</p>
                <p><strong>Status:</strong> <span class="badge ${getStatusBadgeClass(order.status)}">${order.status}</span></p>
            </div>
            <div class="col-md-6">
                <h5 class="mb-3">Customer Details</h5>
                <p><strong>Name:</strong> ${escapeHtml(order.customer_name)}</p>
                <p><strong>Email:</strong> ${escapeHtml(order.customer_email)}</p>
                <p><strong>Phone:</strong> ${escapeHtml(order.customer_phone || 'N/A')}</p>
            </div>

            <div class="col-12 mt-4">
                <h5 class="mb-3">Order Items</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="bg-plant text-white">
                            <tr>
                                <th>Plant</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                          ${items.map(item => {
                            const price = parseFloat(item.unit_price) || 0;
                            const quantity = parseInt(item.quantity) || 0;
                            const total = price * quantity;

                            return `
                              <tr>
                                <td>${escapeHtml(item.product_name)}</td>
                                <td>EGP ${price.toFixed(2)}</td>
                                <td>${quantity}</td>
                                <td>EGP ${total.toFixed(2)}</td>
                              </tr>
                            `;
                          }).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}


function printOrderDetails() {
    const printContent = document.getElementById('orderDetailsContent').innerHTML;
    const originalContent = document.body.innerHTML;
    
    document.body.innerHTML = `
        <div class="container mt-4">
            <h2 class="mb-4">Order Details - PlantsStore</h2>
            ${printContent}
            <div class="text-muted small mt-4">Printed on ${new Date().toLocaleString()}</div>
        </div>
    `;
    
    window.print();
    document.body.innerHTML = originalContent;
    if (typeof initDarkMode === 'function') initDarkMode();
}

function getStatusBadgeClass(status) {
    const statusClasses = {
        'Pending': 'bg-warning text-dark',
        'Confirmed': 'bg-info',
        'Processing': 'bg-plant-light',
        'Shipped': 'bg-secondary',
        'Delivered': 'bg-success',
        'Cancelled': 'bg-danger'
    };
    return statusClasses[status] || 'bg-secondary';
}

// ==================== USER MANAGEMENT ====================
function openAddAdminModal() {
    const modal = new bootstrap.Modal(document.getElementById('addAdminModal'));
    modal.show();
}

async function openEditAdminModal(adminId) {
    try {
        const response = await fetch(`actions/get_admin.php?id=${adminId}`);
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.error || 'Failed to load admin data');
        
        populateEditAdminModal(data.admin);
        const modal = new bootstrap.Modal(document.getElementById('editAdminModal'));
        modal.show();
    } catch (error) {
        console.error('Error:', error);
        showNotification('error', `Failed to load admin data: ${error.message}`);
    }
}

function populateEditAdminModal(admin) {
    document.getElementById('edit_admin_id').value = admin.admin_id;
    document.getElementById('editAdminModalBody').innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <label for="edit_first_name" class="form-label">First Name *</label>
                <input type="text" class="form-control" name="first_name" id="edit_first_name" 
                       value="${escapeHtml(admin.first_name)}" required>
            </div>
            <div class="col-md-6">
                <label for="edit_last_name" class="form-label">Last Name *</label>
                <input type="text" class="form-control" name="last_name" id="edit_last_name" 
                       value="${escapeHtml(admin.last_name)}" required>
            </div>
            <div class="col-md-12">
                <label for="edit_email" class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" id="edit_email" 
                       value="${escapeHtml(admin.email)}" required>
            </div>
            ${admin.role === 'Super Admin' ? `
                <div class="col-md-6">
                    <label for="edit_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" name="password" id="edit_password">
                    <small class="text-muted">Leave blank to keep current password</small>
                </div>
                <div class="col-md-6">
                    <label for="edit_confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" name="confirm_password" id="edit_confirm_password">
                </div>
            ` : ''}
            <div class="col-md-12">
                <label for="edit_role" class="form-label">Role *</label>
                <select class="form-select" name="role" id="edit_role" required>
                    ${generateRoleOptions(admin.role)}
                </select>
            </div>
        </div>
    `;
}

function generateRoleOptions(selectedRole) {
    const roles = [
        { value: 'Super Admin', label: 'Super Admin' },
        { value: 'Product Manager', label: 'Plant Manager' },
        { value: 'Order Manager', label: 'Order Manager' }
    ];
    
    return roles.map(role => `
        <option value="${role.value}" ${role.value === selectedRole ? 'selected' : ''}>
            ${role.label}
        </option>
    `).join('');
}

// ==================== PASSWORD STRENGTH ====================
function setupPasswordStrengthIndicator() {
    const passwordInput = document.getElementById('new_password');
    if (!passwordInput) return;
    
    const strengthIndicator = document.createElement('div');
    strengthIndicator.className = 'password-strength mt-2 small';
    passwordInput.parentNode.appendChild(strengthIndicator);
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;
        
        const strengthText = ['Very Weak', 'Weak', 'Moderate', 'Strong', 'Very Strong', 'Excellent'];
        const strengthColors = ['danger', 'danger', 'warning', 'success', 'success', 'success'];
        
        strengthIndicator.textContent = `Strength: ${strengthText[strength]}`;
        strengthIndicator.className = `password-strength mt-2 small text-${strengthColors[strength]}`;
    });
}

// ==================== GLOBAL EXPORTS ====================
window.showNotification = showNotification;
window.toggleDarkMode = toggleDarkMode;
window.openAddProductModal = openAddProductModal;
window.closeAddProductModal = closeAddProductModal;
window.getProductWithImages = getProductWithImages;
window.showNotification = showNotification;
window.deleteImage = deleteImage;
window.viewOrderDetails = viewOrderDetails;
window.printOrderDetails = printOrderDetails;
window.getStatusBadgeClass = getStatusBadgeClass;
window.openAddAdminModal = openAddAdminModal;
window.openEditAdminModal = openEditAdminModal;
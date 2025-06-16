<nav class="navbar navbar-expand-lg navbar-dark bg-plant-primary fixed-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
            <i class="fas fa-leaf me-2 animate-pulse"></i>
            <span class="brand-text">PlantsStore Admin</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-expanded="false">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Main Navigation -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link hover-underline" href="dashboard.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link hover-underline" href="orders.php">
                        <i class="fas fa-shopping-cart me-1"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link hover-underline" href="products.php">
                        <i class="fas fa-leaf me-1"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link hover-underline" href="users.php">
                        <i class="fas fa-users me-1"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link hover-underline" href="settings.php">
                        <i class="fas fa-cog me-1"></i> Settings
                    </a>
                </li>
            </ul>
            
            <!-- Right Side Controls -->
            <ul class="navbar-nav ms-auto">
                <!-- Dark Mode Toggle -->
                <li class="nav-item">
                    <button class="nav-link theme-toggle" onclick="toggleDarkMode()">
                        <i class="fas fa-moon dark-mode-icon"></i>
                        <i class="fas fa-sun light-mode-icon d-none"></i>
                    </button>
                </li>
                
                <!-- Notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle notification-bell" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger pulse">
                            3
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-notifications shadow-lg">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <li><a class="dropdown-item" href="#">New order received</a></li>
                        <li><a class="dropdown-item" href="#">Product low in stock</a></li>
                        <li><a class="dropdown-item" href="#">New user registered</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="#">View all</a></li>
                    </ul>
                </li>
                
                <!-- User Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center user-profile" href="#" role="button" data-bs-toggle="dropdown">
                        <div class="avatar me-2">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <span class="profile-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-profile shadow-lg">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
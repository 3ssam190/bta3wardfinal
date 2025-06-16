<aside id="sidebar" 
       x-data="{ mobileOpen: false }"
       class="fixed top-0 left-0 z-40 h-full bg-green-700 text-white transition-all duration-300 ease-in-out w-64 overflow-hidden"
       :class="{
           'transform -translate-x-full md:translate-x-0': !mobileOpen && window.innerWidth < 768,
           'transform translate-x-0': mobileOpen || window.innerWidth >= 768
       }"
       style="top: 4rem; height: calc(100vh - 4rem);"
       @resize.window="
           if (window.innerWidth >= 768) {
               mobileOpen = true;
           } else {
               mobileOpen = false;
           }
       ">
  
  <!-- Mobile close button (visible only on mobile) -->
  <button @click="mobileOpen = false" 
          class="md:hidden flex items-center justify-end p-3 text-white hover:text-gray-300 w-full">
    <i class="fas fa-times text-xl"></i>
    <span class="sr-only">Close menu</span>
  </button>

  <div class="h-full flex flex-col pt-2 space-y-1 overflow-y-auto">
    <!-- Dashboard Link -->
    <a href="dashboard.php" 
       class="flex items-center gap-3 p-3 rounded mx-2 hover:bg-green-600 transition-all"
       :class="{ 'bg-green-800': window.location.pathname.includes('dashboard.php') }">
      <i class="fas fa-home text-lg w-6 text-center"></i>
      <span class="sidebar-text">Dashboard</span>
    </a>
    
    <!-- Orders Link -->
    <a href="orders.php" 
       class="flex items-center gap-3 p-3 rounded mx-2 hover:bg-green-600 transition-all"
       :class="{ 'bg-green-800': window.location.pathname.includes('orders.php') }">
      <i class="fas fa-shopping-cart text-lg w-6 text-center"></i>
      <span class="sidebar-text">Orders</span>
    </a>
    
    <!-- Products Link -->
    <a href="products.php" 
       class="flex items-center gap-3 p-3 rounded mx-2 hover:bg-green-600 transition-all"
       :class="{ 'bg-green-800': window.location.pathname.includes('products.php') }">
      <i class="fas fa-box-open text-lg w-6 text-center"></i>
      <span class="sidebar-text">Products</span>
    </a>
    
    <!-- Users Link -->
    <a href="users.php" 
       class="flex items-center gap-3 p-3 rounded mx-2 hover:bg-green-600 transition-all"
       :class="{ 'bg-green-800': window.location.pathname.includes('users.php') }">
      <i class="fas fa-users text-lg w-6 text-center"></i>
      <span class="sidebar-text">Users</span>
    </a>
    
    <!-- Settings Link -->
    <a href="settings.php" 
       class="flex items-center gap-3 p-3 rounded mx-2 hover:bg-green-600 transition-all"
       :class="{ 'bg-green-800': window.location.pathname.includes('settings.php') }">
      <i class="fas fa-cog text-lg w-6 text-center"></i>
      <span class="sidebar-text">Settings</span>
    </a>
  </div>
</aside>

<!-- Mobile overlay (only visible when sidebar is open on mobile) -->
<div x-show="mobileOpen && window.innerWidth < 768" 
     @click="mobileOpen = false"
     class="fixed inset-0 bg-black bg-opacity-50 z-30 md:hidden"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
</div>
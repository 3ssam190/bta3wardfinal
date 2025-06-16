<?php if (isset($_SESSION['notify'])): ?>
  <div id="notif" class="fixed top-4 right-4 bg-leaf text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-500 animate-slide-in">
    <?= $_SESSION['notify'] ?>
  </div>
  <script>
    setTimeout(() => document.getElementById("notif").classList.add("opacity-0"), 3000);
    setTimeout(() => document.getElementById("notif").remove(), 4000);
  </script>
  <style>
    @keyframes slide-in {
      0% { transform: translateX(100%); opacity: 0; }
      100% { transform: translateX(0); opacity: 1; }
    }
    .animate-slide-in {
      animation: slide-in 0.5s ease-out;
    }
  </style>
<?php unset($_SESSION['notify']); endif; ?>

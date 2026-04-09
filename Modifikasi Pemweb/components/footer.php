<?php if (!isset($is_auth_page) || !$is_auth_page): ?>
    
    <footer class="bg-slate-100 py-12 mt-auto">
      <div class="container mx-auto px-4 text-center">
        <p class="text-slate-400 text-sm">
          &copy; 2026 <span class="text-teal-500 font-semibold">SI BanTal</span>. Seluruh informasi data bersifat transparan dan dapat dipertanggungjawabkan.
        </p>
      </div>
    </footer>
    
    <?php endif; ?>

    <script>
      const hamburger = document.querySelector('#hamburger');
      const navMenu = document.querySelector('#nav-menu');
      const header = document.querySelector('#header');

      // Pengecekan agar JS tidak error saat navbar disembunyikan
      if (hamburger && navMenu) {
          hamburger.addEventListener('click', () => {
            // Efek menu
            hamburger.classList.toggle('hamburger-active');
            navMenu.classList.toggle('hidden');
          });
      }

      if (header) {
          window.onscroll = function() {
            if (window.pageYOffset > 50) {
              header.classList.add('navbar-fixed');
              header.classList.remove('absolute', 'bg-transparent');
            } else {
              header.classList.remove('navbar-fixed');
              header.classList.add('absolute', 'bg-transparent');
            }
          };
      }
    </script>
  </body>
</html>
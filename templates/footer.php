</main>
        <nav class="bottom-nav">
            <?php
            $currentPage = $_GET['page'] ?? 'dashboard';
            $navItems = [
                'dashboard' => ['icon' => '🏠', 'label' => 'Home'],
                'wallet' => ['icon' => '💼', 'label' => 'Wallet'],
                'loans' => ['icon' => '💰', 'label' => 'Loans'],
                'savings' => ['icon' => '🏦', 'label' => 'Savings'],
                'profile' => ['icon' => '👤', 'label' => 'Profile']
            ];
            ?>
            <?php foreach ($navItems as $page => $item): ?>
                <a href="index.php?page=<?= $page ?>" class="nav-item <?= ($currentPage === $page) ? 'active' : '' ?>">
                    <div class="nav-icon"><?= $item['icon'] ?></div>
                    <div class="nav-label"><?= $item['label'] ?></div>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
    <script src="assets/js/app.js?v=1.1"></script>
    <script>
        // Other inline scripts can go here if needed, but logic is in app.js
        // For example, initializing the Telegram Web App
        if (window.Telegram && window.Telegram.WebApp) {
            window.Telegram.WebApp.ready();
        }

        // Expand the app to full height
        window.Telegram.WebApp.expand();

        // Set background color to match the app's theme
        window.Telegram.WebApp.setHeaderColor('bg_color');
        window.Telegram.WebApp.setBackgroundColor('secondary_bg_color');

        // Haptic feedback example
        const navLinks = document.querySelectorAll('.nav-item');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                window.Telegram.WebApp.HapticFeedback.impactOccurred('light');
            });
        });
    </script>
</body>
</html>

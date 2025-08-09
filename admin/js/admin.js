document.addEventListener('DOMContentLoaded', function() {
    const adminUsernameEl = document.getElementById('admin-username');
    const logoutBtn = document.getElementById('logout-btn');

    // --- AUTHENTICATION CHECK ---
    function checkAuth() {
        // Check if user is logged in by calling the auth endpoint
        fetch('/admin/api/auth.php')
            .then(response => response.json())
            .then(data => {
                if (data.loggedIn) {
                    console.log('Admin user is logged in:', data);
                    if(adminUsernameEl) {
                        adminUsernameEl.textContent = data.username;
                    }
                } else {
                    // Not logged in, redirect to login page
                    window.location.href = '/admin/login.html';
                }
            })
            .catch(err => {
                console.error('Auth check failed', err);
                window.location.href = '/admin/login.html';
            });
    }

    // --- VIEW MANAGEMENT ---
    window.showView = function(viewId) {
        document.querySelectorAll('.view').forEach(view => {
            view.style.display = 'none';
        });
        const activeView = document.getElementById(viewId + '-view');
        if (activeView) {
            activeView.style.display = 'block';
        }

        const viewTitle = document.getElementById('view-title');
        if(viewTitle) {
            // Capitalize first letter
            viewTitle.textContent = viewId.charAt(0).toUpperCase() + viewId.slice(1).replace('-', ' ');
        }

        // Load data for the view
        // loadDataForView(viewId);
    }

    // --- LOGOUT ---
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // In a real app, you'd call a logout endpoint to destroy the session
            // For now, just redirecting
            fetch('/admin/api/logout.php') // This needs to be created
                .then(() => {
                    localStorage.removeItem('admin_loggedIn');
                    window.location.href = '/admin/login.html';
                });
        });
    }

    // --- INITIALIZATION ---
    checkAuth();
    // Show dashboard by default
    showView('dashboard');
});

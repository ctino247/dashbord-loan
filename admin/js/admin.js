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
        loadDataForView(viewId);
    }

    function loadDataForView(viewId) {
        if (viewId === 'kyc') {
            loadKycData();
        }
    }

    function loadKycData() {
        fetch('/admin/api/kyc.php?status=pending')
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('kyc-table-body');
                tableBody.innerHTML = '';
                if (data.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No pending KYC submissions.</td></tr>';
                    return;
                }
                data.forEach(item => {
                    const row = `
                        <tr>
                            <td>${item.user_id}</td>
                            <td>${item.username}</td>
                            <td>${item.id_number}</td>
                            <td>${new Date(item.created_at).toLocaleString()}</td>
                            <td>
                                <a href="/${item.id_image_path}" target="_blank" class="btn btn-sm btn-info">View ID</a>
                                <button class="btn btn-sm btn-success" onclick="updateKycStatus(${item.id}, 'approved')">Approve</button>
                                <button class="btn btn-sm btn-danger" onclick="updateKycStatus(${item.id}, 'rejected')">Reject</button>
                            </td>
                        </tr>`;
                    tableBody.innerHTML += row;
                });
            });
    }

    window.updateKycStatus = function(kycId, status) {
        fetch('/admin/api/kyc.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ kyc_id: kycId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            loadKycData(); // Refresh the list
        });
    }

    function handleMailerSubmit(event) {
        event.preventDefault();
        const target = document.getElementById('target-user').value;
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;

        fetch('/admin/api/mailer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ target_user_id: target, subject, message })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.message.includes('Successfully')) {
                event.target.reset();
            }
        });
    }

    // --- EVENT LISTENERS ---
    document.getElementById('mailer-form').addEventListener('submit', handleMailerSubmit);


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

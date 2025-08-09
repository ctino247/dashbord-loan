document.addEventListener('DOMContentLoaded', function () {
    const API_BASE_URL = '/backend/api';
    let currentUser = null;

    // Initialize Telegram Web App
    const tg = window.Telegram.WebApp;
    tg.ready();

    // --- USER AUTHENTICATION ---
    function authenticateUser() {
        // For testing outside Telegram, use mock data
        if (!tg.initDataUnsafe || !tg.initDataUnsafe.user) {
            console.log("Running in browser, using mock user data.");
            const mockUser = {
                id: 123456789,
                first_name: 'Test',
                last_name: 'User',
                username: 'testuser'
            };
            handleAuthResponse(mockUser);
            return;
        }

        // Real authentication with Telegram data
        fetch(`${API_BASE_URL}/users.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                telegram_id: tg.initDataUnsafe.user.id,
                username: tg.initDataUnsafe.user.username,
                first_name: tg.initDataUnsafe.user.first_name,
                last_name: tg.initDataUnsafe.user.last_name
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.id) {
                handleAuthResponse(data);
            } else {
                showError('Authentication failed.');
            }
        })
        .catch(err => showError('Error authenticating user: ' + err));
    }

    function handleAuthResponse(userData) {
        currentUser = userData;
        console.log("Logged in as:", currentUser);
        // Load initial view data
        loadDashboardData();
        // Set user info in profile view
        document.getElementById('user-firstname').textContent = currentUser.first_name;
        document.getElementById('profile-tg-id').textContent = currentUser.telegram_id;
        document.getElementById('profile-tg-username').textContent = currentUser.username;
        document.getElementById('profile-email').value = currentUser.email || '';
    }

    // --- VIEW MANAGEMENT ---
    window.showView = function(viewId) {
        document.querySelectorAll('.view').forEach(view => {
            view.classList.add('hidden');
        });
        const newView = document.getElementById(viewId);
        if (newView) {
            newView.classList.remove('hidden');
        }

        // Update header title based on view
        const viewTitles = {
            'dashboard-view': `Hi, ${currentUser ? currentUser.first_name : 'User'}!`,
            'loans-view': 'Loan Activity',
        };
        document.getElementById('app-title').textContent = viewTitles[viewId] || 'Loan Platform';

        // Load data for the new view
        switch(viewId) {
            case 'dashboard-view': loadDashboardData(); break;
            case 'loans-view': loadLoansData(); break;
            case 'wallet-view': loadWalletData(); break;
            case 'savings-view': loadSavingsData(); break;
            case 'support-view': loadSupportData(); break;
        }
    }

    // --- DATA LOADING FUNCTIONS ---
    function loadDashboardData() {
        if (!currentUser) return;
        // Fetch wallet balance, loan summary, savings balance
        // Placeholder data for now
        document.getElementById('wallet-balance').textContent = '$...';
        document.getElementById('active-loan-summary').textContent = '...';
        document.getElementById('savings-balance').textContent = '$...';

        // TODO: Implement actual API calls
        // getWalletBalance();
        // getActiveLoan();
        // getSavingsBalance();
    }

    function loadLoansData() {
        console.log("Loading loans data...");
        // TODO: Implement API call to GET /loans.php?user_id=...
    }

    function loadWalletData() {
        console.log("Loading wallet data...");
        // TODO: Implement API call to GET /transactions.php?user_id=...
    }

    function loadSavingsData() {
        console.log("Loading savings data...");
        // TODO: Implement API call to GET /savings.php?user_id=...
    }

    function loadSupportData() {
        console.log("Loading support tickets...");
        // TODO: Implement API call to GET /tickets.php?user_id=...
    }

    // --- EVENT LISTENERS ---
    // Navigation buttons
    document.getElementById('apply-loan-btn').addEventListener('click', () => showView('loan-application-view'));
    document.getElementById('withdraw-funds-btn').addEventListener('click', () => showView('withdrawal-view'));
    document.getElementById('create-ticket-btn').addEventListener('click', () => showView('create-ticket-view')); // Need to create this view

    // --- UTILITY FUNCTIONS ---
    function showError(message) {
        console.error(message);
        // In a real app, you'd show this in a modal or toast
        tg.showAlert(message);
    }

    // --- INITIALIZATION ---
    authenticateUser();
});

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
            'profile-view': 'My Profile',
            'kyc-view': 'KYC Verification'
        };
        document.getElementById('app-title').textContent = viewTitles[viewId] || 'Loan Platform';

        // Load data for the new view
        switch(viewId) {
            case 'dashboard-view': loadDashboardData(); break;
            case 'loans-view': loadLoansData(); break;
            case 'profile-view': loadProfileData(); break;
            // Other data loading calls can be added here
        }
    }

    // --- DATA LOADING FUNCTIONS ---
    function loadDashboardData() {
        if (!currentUser) return;
        fetch(`${API_BASE_URL}/dashboard.php?user_id=${currentUser.id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('wallet-balance').textContent = `$${parseFloat(data.wallet_balance).toFixed(2)}`;
                document.getElementById('loans-amount-summary').textContent = `$${parseFloat(data.total_loan_amount).toFixed(2)}`;
                // Assuming yearly payment is same as loan amount for this mock
                document.getElementById('yearly-payment-summary').textContent = `$${parseFloat(data.total_loan_amount).toFixed(2)}`;
                document.querySelector('#dashboard-view .text-gray-500').textContent = `Currently you have ${data.active_loan_count} loans.`;
                updateKycStatus(data.kyc_status);
            })
            .catch(err => showError('Failed to load dashboard data.'));
    }

    function loadLoansData() {
        if (!currentUser) return;
        fetch(`${API_BASE_URL}/loans.php?user_id=${currentUser.id}`)
            .then(response => response.json())
            .then(data => {
                const loanList = document.getElementById('loan-list');
                loanList.innerHTML = ''; // Clear old data
                if (data.message) {
                    loanList.innerHTML = `<li class="text-gray-500 text-center">${data.message}</li>`;
                } else {
                    data.forEach(loan => {
                        const statusClass = loan.status === 'repaid' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600';
                        const item = `
                            <li class="bg-white rounded-3xl p-4 flex items-center shadow-md">
                                <div class="w-12 h-12 bg-green-100 rounded-xl mr-4"></div>
                                <div class="flex-grow">
                                    <p class="font-bold">${loan.product_name}</p>
                                    <p class="text-sm text-gray-500">Applied: ${new Date(loan.created_at).toLocaleDateString()}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold">$${parseFloat(loan.amount_requested).toFixed(2)}</p>
                                    <span class="text-xs font-semibold ${statusClass} px-2 py-1 rounded-full">${loan.status}</span>
                                </div>
                            </li>`;
                        loanList.innerHTML += item;
                    });
                }
            })
            .catch(err => showError('Failed to load loan data.'));
    }

    function loadProfileData() {
        if (!currentUser) return;
        document.getElementById('profile-email').textContent = currentUser.email || 'Not set';
        document.getElementById('profile-tg-id').textContent = currentUser.telegram_id;
        // KYC status is loaded from dashboard data
    }

    function updateKycStatus(status) {
        const kycStatusEl = document.getElementById('kyc-status');
        const kycButton = document.querySelector('button[onclick="showView(\'kyc-view\')"]');
        if (!kycStatusEl || !kycButton) return;

        kycStatusEl.textContent = status;
        switch(status.toLowerCase()) {
            case 'approved':
                kycStatusEl.className = 'font-semibold text-green-600';
                kycButton.classList.add('hidden');
                break;
            case 'pending':
                kycStatusEl.className = 'font-semibold text-yellow-500';
                kycButton.textContent = 'KYC Submitted for Review';
                kycButton.disabled = true;
                break;
            default: // Not Submitted or Rejected
                kycStatusEl.className = 'font-semibold text-red-600';
                kycButton.textContent = 'Complete KYC';
                kycButton.disabled = false;
        }
    }

    // --- EVENT LISTENERS & FORM HANDLERS ---

    // Loan Application Form
    // (This view is not in the new design, but the logic might be needed later)
    // function handleLoanApplication(event) { ... }

    // --- UTILITY FUNCTIONS ---
    function showError(message) {
        console.error(message);
        // In a real app, you'd show this in a modal or toast
        tg.showAlert(message);
    }

    // --- KYC & PROFILE LOGIC ---
    function handleProfileImageUpload(event) {
        const file = event.target.files[0];
        if (!file || !currentUser) return;

        const formData = new FormData();
        formData.append('action', 'upload_profile_image');
        formData.append('user_id', currentUser.id);
        formData.append('profile_image', file);

        fetch(`${API_BASE_URL}/profile.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tg.showAlert('Profile image updated successfully!');
                // Optionally, update the image on the page
            } else {
                showError(data.message || 'Failed to upload image.');
            }
        })
        .catch(err => showError('Error uploading image: ' + err));
    }

    function handleKycSubmit(event) {
        event.preventDefault();
        if (!currentUser) return;

        const formData = new FormData(event.target);
        formData.append('action', 'submit_kyc');
        formData.append('user_id', currentUser.id);

        fetch(`${API_BASE_URL}/profile.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.message.includes('successfully')) {
                tg.showAlert('KYC documents submitted for review.');
                showView('profile-view');
            } else {
                showError(data.message || 'Failed to submit KYC documents.');
            }
        })
        .catch(err => showError('Error submitting KYC: ' + err));
    }


    // Add event listeners for new forms
    document.getElementById('profile-image-upload').addEventListener('change', handleProfileImageUpload);
    document.getElementById('kyc-form').addEventListener('submit', handleKycSubmit);


    // --- INITIALIZATION ---
    authenticateUser();
});

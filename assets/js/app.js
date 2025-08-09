// --- Main Entry Point ---
document.addEventListener('DOMContentLoaded', () => {
    // Inject toast notification CSS
    injectToastStyles();

    // Simple router based on URL parameter
    const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';

    switch (page) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'profile':
            loadProfileData();
            setupProfileFormListeners();
            break;
        // Add cases for 'wallet', 'loans', 'savings' as they are built
    }

    // Initialize Telegram Web App features
    if (window.Telegram && window.Telegram.WebApp) {
        window.Telegram.WebApp.ready();
        window.Telegram.WebApp.expand();
    }
});


// --- Dashboard ---
function loadDashboardData() {
    document.body.classList.add('loading');
    fetch('api/get_dashboard_data.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                updateDashboardUI(result.data);
            } else {
                showToast('Failed to load dashboard data.', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            showToast('Network error. Could not load dashboard.', 'error');
        })
        .finally(() => document.body.classList.remove('loading'));
}

function updateDashboardUI(data) {
    const updateText = (selector, text) => {
        const el = document.querySelector(selector);
        if (el) el.textContent = text;
    };
    updateText('.app-header h1', `Welcome, ${data.user.first_name}!`);
    updateText('.dashboard-grid .card:nth-child(1) .balance-large', `$${data.wallet.total_usd}`);
    if (data.loan.has_active_loan) {
        updateText('.dashboard-grid .card:nth-child(2) .balance-large', `$${parseFloat(data.loan.amount).toFixed(2)}`);
        updateText('.dashboard-grid .card:nth-child(2) small', `Next payment: ${data.loan.next_payment_due}`);
    } else {
        updateText('.dashboard-grid .card:nth-child(2) .balance-large', 'N/A');
        updateText('.dashboard-grid .card:nth-child(2) small', 'No active loans');
    }
    updateText('.dashboard-grid .card:nth-child(3) .balance-large', `$${parseFloat(data.savings.balance).toFixed(2)}`);
    updateText('.dashboard-grid .card:nth-child(3) small', `${data.savings.apy}% APY`);
    const kycStatusEl = document.querySelector('.dashboard-grid .card.kyc-status p');
    if (kycStatusEl) {
        const status = data.user.kyc_status || 'unknown';
        kycStatusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        kycStatusEl.className = `status-${status}`;
    }
    const txList = document.querySelector('.recent-transactions ul');
    if (txList) {
        txList.innerHTML = '';
        if (data.transactions && data.transactions.length > 0) {
            data.transactions.forEach(tx => {
                const li = document.createElement('li');
                li.innerHTML = `<span class="tx-type ${tx.type}">${tx.type.replace('_', ' ')}</span><span class="tx-amount">${tx.description}</span><span class="tx-date">${tx.date}</span>`;
                txList.appendChild(li);
            });
        } else {
            txList.innerHTML = '<li>No recent transactions.</li>';
        }
    }
}


// --- Profile ---
function loadProfileData() {
    fetch('api/get_profile_data.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                updateProfileUI(result.data);
            }
        });
}

function updateProfileUI(data) {
    document.querySelector('.profile-avatar').src = data.profile_picture_url;
    document.querySelector('.profile-header h2').textContent = `${data.first_name} ${data.last_name || ''}`;
    document.querySelector('.profile-header p').textContent = `@${data.username || 'N/A'}`;
    document.querySelector('#email').value = data.email || '';
    document.querySelector('#id_number').value = data.id_number || '';
    const kycStatusEl = document.querySelector('.card p .status-pending');
    if (kycStatusEl) {
        kycStatusEl.textContent = data.kyc_status.charAt(0).toUpperCase() + data.kyc_status.slice(1);
        kycStatusEl.className = `status-${data.kyc_status}`;
    }
}

function setupProfileFormListeners() {
    handleFormSubmit('profile-form', 'update_email');
    handleFormSubmit('kyc-form', 'submit_kyc');
}

function handleFormSubmit(formId, action) {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', event => {
        event.preventDefault();
        const formData = new FormData(form);
        formData.append('action', action);

        const submitButton = form.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Submitting...';

        fetch('api/update_profile.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                showToast(data.message, data.status === 'success' ? 'success' : 'error');
                if(data.status === 'success' && action === 'submit_kyc') {
                    // Update KYC status on the page
                    loadProfileData();
                }
            })
            .catch(error => {
                showToast('A network error occurred. Please try again.', 'error');
                console.error('Form submission error:', error);
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            });
    });
}


// --- UI Utilities ---
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type} show`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 500);
    }, 3000);
}

function injectToastStyles() {
    const style = document.createElement('style');
    style.textContent = `
    .toast {
        position: fixed;
        bottom: -100px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #333;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        z-index: 1000;
        transition: bottom 0.5s ease-in-out;
        opacity: 0.9;
        font-size: 0.9rem;
    }
    .toast.show {
        bottom: 70px; /* Position above nav bar */
    }
    .toast-success { background-color: var(--success-color); }
    .toast-error { background-color: var(--destructive-color); }
    `;
    document.head.appendChild(style);
}

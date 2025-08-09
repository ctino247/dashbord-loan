document.addEventListener('DOMContentLoaded', () => {
    const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';

    switch (page) {
        case 'users':
            loadAdminData('users', populateUsersTable);
            break;
        case 'loans':
            loadAdminData('loans', populateLoansTable);
            break;
        case 'withdrawals':
            loadAdminData('withdrawals', populateWithdrawalsTable);
            break;
        case 'settings':
            loadAdminData('settings', populateSettingsForm);
            setupSettingsForm();
            break;
    }
});

function loadAdminData(dataType, populator) {
    fetch(`../api/admin/get_${dataType}.php`)
        .then(res => res.json())
        .then(result => {
            if (result.status === 'success') {
                populator(result.data);
            } else {
                alert(`Error: ${result.message}`);
            }
        });
}

function populateUsersTable(users) {
    const tbody = document.querySelector('#users-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    users.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.telegram}</td>
            <td>${user.email}</td>
            <td><span class="status-${user.kyc_status}">${user.kyc_status}</span></td>
            <td>
                ${user.kyc_status === 'pending' ? `
                <button class="button button-small" onclick="updateKycStatus(${user.id}, 'approved')">Approve</button>
                <button class="button button-small button-destructive" onclick="updateKycStatus(${user.id}, 'rejected')">Reject</button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function populateLoansTable(loans) {
    const tbody = document.querySelector('#loans-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    loans.forEach(loan => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${loan.id}</td>
            <td>${loan.user_name}</td>
            <td>$${loan.amount.toFixed(2)}</td>
            <td>${loan.term} Months</td>
            <td>${loan.date}</td>
            <td><span class="status-${loan.status}">${loan.status}</span></td>
            <td>
                ${loan.status === 'pending' ? `
                <button class="button button-small" onclick="updateLoanStatus('${loan.id}', 'approved')">Approve</button>
                <button class="button button-small button-destructive" onclick="updateLoanStatus('${loan.id}', 'rejected')">Reject</button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function populateWithdrawalsTable(withdrawals) {
    const tbody = document.querySelector('#withdrawals-table tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    withdrawals.forEach(w => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${w.id}</td>
            <td>${w.user_name}</td>
            <td>${w.amount}</td>
            <td>${w.currency}</td>
            <td title="${w.address}">${w.address.substring(0, 10)}...</td>
            <td>${w.date}</td>
            <td><span class="status-${w.status}">${w.status}</span></td>
            <td>
                ${w.status === 'pending' ? `
                <button class="button button-small" onclick="updateWithdrawalStatus('${w.id}', 'approved')">Approve</button>
                <button class="button button-small button-destructive" onclick="updateWithdrawalStatus('${w.id}', 'rejected')">Reject</button>
                ` : ''}
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function populateSettingsForm(settings) {
    for (const key in settings) {
        const input = document.getElementById(key);
        if (input) input.value = settings[key];
    }
}

function setupSettingsForm() {
    const form = document.getElementById('settings-form');
    if (!form) return;
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        fetch('../api/admin/update_settings.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(result => {
                alert(result.message);
            });
    });
}

// --- Action Functions ---
function updateKycStatus(userId, status) {
    if (!confirm(`Are you sure you want to ${status} KYC for user ${userId}?`)) return;
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('status', status);
    fetch('../api/admin/update_kyc.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(result => {
            alert(result.message);
            if (result.status === 'success') loadAdminData('users', populateUsersTable);
        });
}

function updateLoanStatus(loanId, status) {
    if (!confirm(`Are you sure you want to ${status} loan ${loanId}?`)) return;
    const formData = new FormData();
    formData.append('loan_id', loanId);
    formData.append('status', status);
    fetch('../api/admin/update_loan_status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(result => {
            alert(result.message);
            if (result.status === 'success') loadAdminData('loans', populateLoansTable);
        });
}

function updateWithdrawalStatus(withdrawalId, status) {
    if (!confirm(`Are you sure you want to ${status} withdrawal ${withdrawalId}?`)) return;
    const formData = new FormData();
    formData.append('withdrawal_id', withdrawalId);
    formData.append('status', status);
    fetch('../api/admin/update_withdrawal_status.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(result => {
            alert(result.message);
            if (result.status === 'success') loadAdminData('withdrawals', populateWithdrawalsTable);
        });
}

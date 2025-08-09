<div class="loans-overview card">
    <h2>Loan Management</h2>
    <p>Manage your loans or apply for a new one.</p>
</div>

<div class="action-buttons">
    <button class="button" onclick="showLoanPage('status')">My Loans</button>
    <button class="button" onclick="showLoanPage('apply')">Apply for New Loan</button>
</div>

<div id="loan-pages">
    <!-- Loan Status Page -->
    <div id="page-loan-status" class="loan-page">
        <h3>Your Loan Status</h3>
        <div class="active-loan card">
            <h4>Active Loan Details</h4>
            <p><strong>Amount:</strong> $500.00</p>
            <p><strong>Interest Rate:</strong> 12%</p>
            <p><strong>Term:</strong> 6 Months</p>
            <p><strong>Status:</strong> <span class="status-active">Active</span></p>
            <p><strong>Next Payment Due:</strong> 2024-08-15</p>
            <div class="progress-bar">
                <div class="progress" style="width: 33%;"></div>
            </div>
            <p>2 of 6 payments made</p>
            <button class="button">Make Repayment</button>
        </div>
        <div class="past-loans">
            <h4>Loan History</h4>
            <ul>
                <li>Loan of $1000 (Paid Off) - Jan 2024</li>
                <li>Loan of $200 (Rejected) - Dec 2023</li>
            </ul>
        </div>
    </div>

    <!-- Apply for Loan Page -->
    <div id="page-loan-apply" class="loan-page" style="display:none;">
        <h3>Apply for a New Loan</h3>
        <form id="loan-application-form">
            <div class="form-group">
                <label for="loan-amount">Loan Amount ($)</label>
                <input type="number" id="loan-amount" name="amount" placeholder="e.g., 1000" min="100" max="10000">
            </div>
            <div class="form-group">
                <label for="loan-term">Loan Term (Months)</label>
                <select id="loan-term" name="term">
                    <option value="3">3 Months</option>
                    <option value="6">6 Months</option>
                    <option value="12">12 Months</option>
                </select>
            </div>
            <div class="form-group">
                <label for="loan-purpose">Purpose of Loan</label>
                <textarea id="loan-purpose" name="purpose" rows="3" placeholder="e.g., For a small project"></textarea>
            </div>
            <div class="loan-summary">
                <p>Estimated Interest Rate: <strong id="est-rate">10%</strong></p>
                <p>Estimated Monthly Payment: <strong id="est-payment">$0.00</strong></p>
            </div>
            <button type="submit" class="button">Submit Application</button>
            <p><small>Loan applications are subject to approval based on your KYC status and financial history.</small></p>
        </form>
    </div>
</div>

<script>
function showLoanPage(page) {
    document.querySelectorAll('.loan-page').forEach(p => p.style.display = 'none');
    document.getElementById('page-loan-' + page).style.display = 'block';
}
// Default to status
document.addEventListener('DOMContentLoaded', () => showLoanPage('status'));
</script>

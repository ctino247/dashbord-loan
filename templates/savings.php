<div class="savings-overview card">
    <h2>Savings Account</h2>
    <p class="total-balance">$2,000.00</p>
    <small>Current Balance</small>
    <div class="apy-info">
        <p>Annual Percentage Yield (APY)</p>
        <strong>5.00%</strong>
    </div>
</div>

<div class="action-buttons">
    <button class="button" onclick="showSavingsPage('deposit')">Deposit to Savings</button>
    <button class="button" onclick="showSavingsPage('withdraw')">Withdraw from Savings</button>
</div>

<div id="savings-pages">
    <!-- Deposit Page -->
    <div id="page-savings-deposit" class="savings-page" style="display:none;">
        <h3>Deposit to Savings</h3>
        <p>Transfer funds from your main wallet to your savings account to start earning interest.</p>
        <form id="savings-deposit-form">
            <div class="form-group">
                <label for="deposit-amount">Amount to Deposit ($)</label>
                <input type="number" id="deposit-amount" name="amount" placeholder="0.00">
                <small>Available in wallet: $1,234.56</small>
            </div>
            <button type="submit" class="button">Confirm Deposit</button>
        </form>
    </div>

    <!-- Withdraw Page -->
    <div id="page-savings-withdraw" class="savings-page" style="display:none;">
        <h3>Withdraw from Savings</h3>
        <p>Transfer funds from your savings account back to your main wallet.</p>
        <form id="savings-withdrawal-form">
            <div class="form-group">
                <label for="withdraw-amount">Amount to Withdraw ($)</label>
                <input type="number" id="withdraw-amount" name="amount" placeholder="0.00">
                <small>Available in savings: $2,000.00</small>
            </div>
            <button type="submit" class="button">Confirm Withdrawal</button>
            <p><small>Withdrawals from savings may require admin approval.</small></p>
        </form>
    </div>
</div>

<div class="recent-transactions card">
    <h3>Savings Transactions</h3>
    <ul>
        <li>
            <span class="tx-type deposit">Deposit</span>
            <span class="tx-amount">+$500.00</span>
            <span class="tx-date">2024-07-20</span>
        </li>
        <li>
            <span class="tx-type interest">Interest</span>
            <span class="tx-amount">+$8.33</span>
            <span class="tx-date">2024-07-31</span>
        </li>
    </ul>
</div>

<script>
function showSavingsPage(page) {
    document.querySelectorAll('.savings-page').forEach(p => p.style.display = 'none');
    document.getElementById('page-savings-' + page).style.display = 'block';
}
</script>

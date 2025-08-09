<div class="wallet-overview card">
    <h2>My Wallet</h2>
    <p class="total-balance">$1,234.56</p>
    <small>Total Estimated Value</small>
</div>

<div class="action-buttons">
    <button class="button" onclick="showPage('deposit')">Deposit</button>
    <button class="button" onclick="showPage('withdraw')">Withdraw</button>
    <button class="button" onclick="showPage('transfer')">Transfer</button>
</div>


<div id="wallet-pages">
    <!-- Balances Page -->
    <div id="page-balances" class="wallet-page active">
        <h3>Asset Balances</h3>
        <ul class="asset-list">
            <li>
                <div class="asset-info">
                    <img src="assets/images/btc.png" alt="BTC" class="asset-icon">
                    <div>
                        <strong>Bitcoin</strong>
                        <small>BTC</small>
                    </div>
                </div>
                <div class="asset-balance">
                    <strong>0.05 BTC</strong>
                    <small>$3,000.00</small>
                </div>
            </li>
            <li>
                <div class="asset-info">
                    <img src="assets/images/usdt.png" alt="USDT" class="asset-icon">
                    <div>
                        <strong>Tether</strong>
                        <small>USDT-TRC20</small>
                    </div>
                </div>
                <div class="asset-balance">
                    <strong>1,000.00 USDT</strong>
                    <small>$1,000.00</small>
                </div>
            </li>
            <li>
                <div class="asset-info">
                    <img src="assets/images/eth.png" alt="ETH" class="asset-icon">
                    <div>
                        <strong>Ethereum</strong>
                        <small>ETH</small>
                    </div>
                </div>
                <div class="asset-balance">
                    <strong>0.1 ETH</strong>
                    <small>$350.00</small>
                </div>
            </li>
        </ul>
    </div>

    <!-- Deposit Page -->
    <div id="page-deposit" class="wallet-page" style="display:none;">
        <h3>Deposit Crypto</h3>
        <div class="form-group">
            <label for="deposit-currency">Select Currency</label>
            <select id="deposit-currency">
                <option value="btc">Bitcoin (BTC)</option>
                <option value="usdt">Tether (USDT-TRC20)</option>
                <option value="eth">Ethereum (ETH)</option>
            </select>
        </div>
        <div class="deposit-instructions">
            <p>Send only BTC to this address:</p>
            <div class="qr-code">
                <img src="assets/images/qr-code-placeholder.png" alt="QR Code">
            </div>
            <p class="wallet-address">bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh</p>
            <button class="button button-secondary">Copy Address</button>
        </div>
    </div>

    <!-- Withdraw Page -->
    <div id="page-withdraw" class="wallet-page" style="display:none;">
        <h3>Withdraw Crypto</h3>
        <form id="withdrawal-form">
            <div class="form-group">
                <label for="withdraw-currency">Currency</label>
                <select id="withdraw-currency" name="currency">
                    <option value="btc">Bitcoin (BTC)</option>
                    <option value="usdt">Tether (USDT-TRC20)</option>
                    <option value="eth">Ethereum (ETH)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="withdraw-address">Recipient Address</label>
                <input type="text" id="withdraw-address" name="address" placeholder="Enter destination address">
            </div>
            <div class="form-group">
                <label for="withdraw-amount">Amount</label>
                <input type="number" id="withdraw-amount" name="amount" placeholder="0.00">
            </div>
            <button type="submit" class="button">Submit Withdrawal</button>
            <p><small>Withdrawals require admin approval and may take up to 24 hours.</small></p>
        </form>
    </div>
</div>

<script>
function showPage(page) {
    document.querySelectorAll('.wallet-page').forEach(p => p.style.display = 'none');
    document.getElementById('page-' + page).style.display = 'block';
}
// Default to balances
document.addEventListener('DOMContentLoaded', () => showPage('balances'));
</script>

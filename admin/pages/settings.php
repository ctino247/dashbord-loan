<div class="card">
    <h2>System Settings</h2>
    <p>Configure global settings for the application.</p>

    <form id="settings-form">
        <div class="form-group">
            <label for="loan_interest_rate">Default Loan Interest Rate (%)</label>
            <input type="number" step="0.01" id="loan_interest_rate" name="loan_interest_rate" value="">
        </div>
        <div class="form-group">
            <label for="savings_interest_rate">Savings Interest Rate (APY %)</label>
            <input type="number" step="0.01" id="savings_interest_rate" name="savings_interest_rate" value="">
        </div>
        <hr>
        <h3>Crypto Addresses</h3>
        <div class="form-group">
            <label for="btc_address">BTC Deposit Address</label>
            <input type="text" id="btc_address" name="btc_address" value="">
        </div>
        <div class="form-group">
            <label for="usdt_trc20_address">USDT-TRC20 Deposit Address</label>
            <input type="text" id="usdt_trc20_address" name="usdt_trc20_address" value="">
        </div>
        <div class="form-group">
            <label for="eth_address">ETH Deposit Address</label>
            <input type="text" id="eth_address" name="eth_address" value="">
        </div>

        <button type="submit" class="button">Save Settings</button>
    </form>
</div>

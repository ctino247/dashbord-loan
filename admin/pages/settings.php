<div class="card">
    <h2>System Settings</h2>
    <p>Configure global settings for the application.</p>

    <form id="settings-form">
        <div class="form-group">
            <label for="loan-interest-rate">Default Loan Interest Rate (%)</label>
            <input type="number" step="0.01" id="loan-interest-rate" name="loan_interest_rate" value="10.00">
        </div>
        <div class="form-group">
            <label for="savings-interest-rate">Savings Interest Rate (APY %)</label>
            <input type="number" step="0.01" id="savings-interest-rate" name="savings_interest_rate" value="5.00">
        </div>
        <hr>
        <h3>Crypto Addresses</h3>
        <div class="form-group">
            <label for="btc-address">BTC Deposit Address</label>
            <input type="text" id="btc-address" name="btc_address" value="YOUR_BTC_ADDRESS_HERE">
        </div>
        <div class="form-group">
            <label for="usdt-address">USDT-TRC20 Deposit Address</label>
            <input type="text" id="usdt-address" name="usdt_trc20_address" value="YOUR_USDT_TRC20_ADDRESS_HERE">
        </div>
        <div class="form-group">
            <label for="eth-address">ETH Deposit Address</label>
            <input type="text" id="eth-address" name="eth_address" value="YOUR_ETH_ADDRESS_HERE">
        </div>

        <button type="submit" class="button">Save Settings</button>
    </form>
</div>

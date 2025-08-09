<div class="card">
    <h2>Withdrawal Requests</h2>
    <p>Review and approve or reject user withdrawal requests.</p>

    <table id="withdrawals-table">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>User Name</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Address</th>
                <th>Date Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>W001</td>
                <td>Mark Johnson</td>
                <td>0.01</td>
                <td>BTC</td>
                <td>bc1q...</td>
                <td>2024-07-29</td>
                <td><a href="#" class="button button-small">Review</a></td>
            </tr>
             <tr>
                <td>W002</td>
                <td>Sarah Lee</td>
                <td>500.00</td>
                <td>USDT-TRC20</td>
                <td>T...</td>
                <td>2024-07-28</td>
                <td><a href="#" class="button button-small">Review</a></td>
            </tr>
        </tbody>
    </table>
</div>

<style>
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--bg-color);
}
thead {
    background-color: var(--secondary-bg-color);
}
.button-small {
    padding: 5px 10px;
    font-size: 0.8rem;
    width: auto;
}
</style>

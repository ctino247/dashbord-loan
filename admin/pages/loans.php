<div class="card">
    <h2>Loan Application Approvals</h2>
    <p>Review and approve or reject new loan applications.</p>

    <table>
        <thead>
            <tr>
                <th>Application ID</th>
                <th>User Name</th>
                <th>Amount</th>
                <th>Term</th>
                <th>Date Submitted</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>L001</td>
                <td>John Smith</td>
                <td>$1,000.00</td>
                <td>12 Months</td>
                <td>2024-07-28</td>
                <td><span class="status-pending">Pending</span></td>
                <td><a href="#" class="button button-small">Review</a></td>
            </tr>
            <tr>
                <td>L002</td>
                <td>Emily White</td>
                <td>$500.00</td>
                <td>6 Months</td>
                <td>2024-07-27</td>
                <td><span class="status-pending">Pending</span></td>
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

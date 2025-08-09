<div class="card">
    <h2>User Management</h2>
    <p>Here you can view, manage, and approve KYC for users.</p>

    <!-- Add user search/filter controls here -->

    <table id="users-table">
        <thead>
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Telegram</th>
                <th>Email</th>
                <th>KYC Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Rows will be populated dynamically -->
            <tr>
                <td>1</td>
                <td>Dev User</td>
                <td>@devuser</td>
                <td>dev.user@example.com</td>
                <td><span class="status-approved">Approved</span></td>
                <td><a href="#" class="button button-small">View</a></td>
            </tr>
            <tr>
                <td>2</td>
                <td>Jane Doe</td>
                <td>@janedoe</td>
                <td>jane@example.com</td>
                <td><span class="status-pending">Pending</span></td>
                <td><a href="#" class="button button-small">View</a></td>
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

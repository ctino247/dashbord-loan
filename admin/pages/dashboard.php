<div class="card">
    <h2>Overview</h2>
    <p>Welcome to the admin dashboard. Here is a summary of the platform's activity.</p>

    <div class="dashboard-stats">
        <div class="stat-card">
            <h4>Total Users</h4>
            <p>150</p>
        </div>
        <div class="stat-card">
            <h4>Pending KYC</h4>
            <p>12</p>
        </div>
        <div class="stat-card">
            <h4>Active Loans</h4>
            <p>35</p>
        </div>
        <div class="stat-card">
            <h4>Pending Withdrawals</h4>
            <p>3</p>
        </div>
    </div>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}
.stat-card {
    background-color: var(--bg-color);
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}
.stat-card h4 {
    margin-top: 0;
    color: var(--hint-color);
}
.stat-card p {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0;
}
</style>

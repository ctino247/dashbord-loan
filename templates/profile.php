<div class="profile-header">
    <img src="<?= htmlspecialchars($user['profile_picture_url'] ?? 'assets/images/default-avatar.png') ?>" alt="Profile Picture" class="profile-avatar">
    <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
    <p>@<?= htmlspecialchars($user['username'] ?? 'N/A') ?></p>
</div>

<div class="card">
    <h3>Personal Information</h3>
    <form id="profile-form">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="you@example.com">
        </div>
        <button type="submit" class="button">Update Email</button>
    </form>
</div>

<div class="card">
    <h3>KYC Verification</h3>
    <p>Status: <span class="status-pending">Pending</span></p>
    <form id="kyc-form" enctype="multipart/form-data">
        <div class="form-group">
            <label for="id_number">ID Number</label>
            <input type="text" id="id_number" name="id_number" placeholder="Enter your ID number">
        </div>
        <div class="form-group">
            <label for="id_image">ID Image</label>
            <input type="file" id="id_image" name="id_image" accept="image/*">
        </div>
        <button type="submit" class="button">Upload for Verification</button>
    </form>
</div>

<div class="card">
    <h3>Settings</h3>
    <button id="logout-button" class="button button-destructive">Logout</button>
</div>

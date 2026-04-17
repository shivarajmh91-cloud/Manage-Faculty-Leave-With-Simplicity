<?php
session_start();
if(!isset($_SESSION['user_email'])){
    header("Location: login.php");
    exit();
}
include("db.php");

// Get pending leaves count for notification
$pending_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leaves WHERE email='".mysqli_real_escape_string($conn, $_SESSION['user_email'])."' AND status='Pending'");
$pending_count = mysqli_fetch_assoc($pending_count_query)['count'];

$user_email = $_SESSION['user_email'];
$user_role = $_SESSION['user_role'];

$result = mysqli_query($conn, "SELECT * FROM users WHERE email='$user_email'");
$user = $result ? mysqli_fetch_assoc($result) : null;
$name_val = $user ? $user['name'] : '';
$email_val = $user ? $user['email'] : '';

if(isset($_POST['update'])){
    $name = $_POST['name'];
    $email = $_POST['email'];

    mysqli_query($conn, "UPDATE users SET name='$name', email='$email' WHERE email='$user_email'");

    echo "<script>alert('Profile Updated Successfully'); window.location='settings.php';</script>";
}

if(isset($_POST['password_update'])){
    $password = $_POST['password'];

    mysqli_query($conn, "UPDATE users SET password='$password' WHERE email='$user_email'");

    echo "<script>alert('Password Updated Successfully');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Settings - Faculty Leave System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>🎓 Faculty</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="calendar.php">📅 Calendar</a>
    <a href="apply_leave.php">📝 Apply Leave</a>
    <a href="leave_history.php">📋 Leave History</a>
    <a href="reports.php">📊 Reports</a>
    <a href="settings.php">⚙️ Settings</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main Content -->
<div class="main">

    <!-- Message Notification -->
    <div id="messageBox" class="message-box"></div>

    <!-- Topbar -->
    <div class="topbar">
        <h2>Account Settings</h2>
        <div class="topbar-right">
            <button class="role-btn" title="Your Role"><?php echo strtoupper($user_role); ?></button>
            <button class="notification-btn" title="Notifications" onclick="showNotifications()">🔔<?php if($pending_count > 0) echo '<span class="badge">'.$pending_count.'</span>'; ?></button>
            <div class="user">👩‍🏫 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        </div>
    </div>

    <p style="color: #666; margin-bottom: 25px;">Manage your personal information and security details.</p>

    <div class="settings-grid">

        <!-- Profile Card -->
        <div class="settings-card">
            <h3>👤 Profile Information</h3>

            <form method="POST">
                <label style="display:block; text-align:left; margin-top:10px; font-weight:500;">Full Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name_val) ?>" required>

                <label style="display:block; text-align:left; margin-top:10px; font-weight:500;">Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email_val) ?>" required>

                <button name="update">Save Changes</button>
            </form>
        </div>

        <!-- Password Card -->
        <div class="settings-card">
            <h3>🔐 Security</h3>

            <form method="POST">
                <label style="display:block; text-align:left; margin-top:10px; font-weight:500;">New Password</label>
                <input type="password" name="password" placeholder="Enter new password" required>

                <button name="password_update">Update Password</button>
            </form>
        </div>

    </div>

</div>

<script>
    function showNotifications() {
        const count = <?= $pending_count ?>;
        if (count > 0) {
            showMessage(`You have ${count} pending leave request(s). Check your leave history for details.`, 'info');
        } else {
            showMessage('No new notifications.', 'info');
        }
    }

    function showMessage(message, type) {
        const messageBox = document.getElementById('messageBox');
        messageBox.innerHTML = `<div class="message message-${type}">${message}</div>`;
        messageBox.style.display = 'block';
        setTimeout(() => {
            messageBox.style.display = 'none';
        }, 3000);
    }
</script>

</body>
</html>
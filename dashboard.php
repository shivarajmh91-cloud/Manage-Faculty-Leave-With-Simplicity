<?php
session_start();
if(!isset($_SESSION['user_email'])){
    header("Location: login.php");
    exit();
}
include("db.php");

// Get user role and branch
$user_role = $_SESSION['user_role'];
$user_query = mysqli_query($conn, "SELECT branch FROM users WHERE email='".mysqli_real_escape_string($conn, $_SESSION['user_email'])."'");
$user_data = mysqli_fetch_assoc($user_query);
$user_branch = $user_data['branch'];

// Build filter based on role
$filter = "";
if ($user_role == 'faculty') {
    $filter = " email='".mysqli_real_escape_string($conn, $_SESSION['user_email'])."'";
} elseif ($user_role == 'hod') {
    $filter = " branch='".mysqli_real_escape_string($conn, $user_branch)."'";
}

// Get pending leaves count for notification
$pending_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leaves WHERE status='Pending'" . ($filter ? " AND $filter" : ""));
$pending_count = mysqli_fetch_assoc($pending_count_query)['count'];

// Fetch counts
$total_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leaves" . ($filter ? " WHERE $filter" : ""));
$total_leaves = $total_query ? mysqli_fetch_assoc($total_query)['count'] : 0;

$approved_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leaves WHERE status='Approved'" . ($filter ? " AND $filter" : ""));
$approved_leaves = $approved_query ? mysqli_fetch_assoc($approved_query)['count'] : 0;

$pending_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM leaves WHERE status='Pending'" . ($filter ? " AND $filter" : ""));
$pending_leaves = $pending_query ? mysqli_fetch_assoc($pending_query)['count'] : 0;

// Fetch recent leaves
$recent_leaves = mysqli_query($conn, "SELECT * FROM leaves" . ($filter ? " WHERE $filter" : "") . " ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Faculty Leave System</title>
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

<!-- Main -->
<div class="main">

    <!-- Message Notification -->
    <div id="messageBox" class="message-box"></div>

    <!-- Topbar -->
<div class="topbar">
        <div style="display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="toggleSidebar()">
            <span>☰</span>
            <h2>Dashboard</h2>
        </div>
        <div class="topbar-right">
            <button class="role-btn" title="Your Role"><?php echo strtoupper($user_role); ?></button>
            <button class="notification-btn" title="Notifications" onclick="showNotifications()">🔔<?php if($pending_count > 0) echo '<span class="badge">'.$pending_count.'</span>'; ?></button>
            <div class="user">👩‍🏫 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        </div>
    </div>

    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>

    <!-- Cards -->
    <div class="cards">
        <div class="card-box gradient1">
            <h3><?= $total_leaves ?></h3>
            <p>Total Leaves</p>
        </div>

        <div class="card-box gradient2">
            <h3><?= $approved_leaves ?></h3>
            <p>Approved</p>
        </div>

        <div class="card-box gradient3">
            <h3><?= $pending_leaves ?></h3>
            <p>Pending</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions">
        <a href="apply_leave.php" class="action-btn">➕ Apply Leave</a>
        <a href="leave_history.php" class="action-btn">📋 View Leaves</a>
    </div>

    <!-- Table -->
    <div class="table-box">
        <h3>Recent Leave Requests</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Branch</th>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Document</th>
                <th>Status</th>
                <?php if ($user_role == 'hod' || $user_role == 'principal') echo '<th>Action</th>'; ?>
            </tr>

            <?php 
            if($recent_leaves && mysqli_num_rows($recent_leaves) > 0) {
                while($row = mysqli_fetch_assoc($recent_leaves)) { 
                    $statusClass = strtolower($row['status']);
                    if ($statusClass == 'pending') $statusClass = 'pending';
                    elseif ($statusClass == 'approved') $statusClass = 'approved';
                    else $statusClass = 'rejected';
            ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['branch'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                <td><?= htmlspecialchars($row['from_date']) ?></td>
                <td><?= htmlspecialchars($row['to_date']) ?></td>
                <td>
                    <?php if($row['document_file']): ?>
                        <a href="download_document.php?file=<?= urlencode($row['document_file']) ?>&leave_id=<?= $row['id'] ?>" 
                           class="doc-btn" download>📥 Download</a>
                    <?php else: ?>
                        <span style="color: #999;">No document</span>
                    <?php endif; ?>
                </td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></td>
                <?php if (($user_role == 'hod' || $user_role == 'principal') && $row['status'] == 'Pending'): ?>
                    <td>
                        <button class="btn-approve" onclick="approveLeave(<?= $row['id'] ?>)">Approve</button>
                        <button class="btn-reject" onclick="rejectLeave(<?= $row['id'] ?>)">Reject</button>
                    </td>
                <?php elseif ($user_role == 'hod' || $user_role == 'principal'): ?>
                    <td><span class="action-disabled">No action</span></td>
                <?php endif; ?>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='8'>No recent leave requests found.</td></tr>";
            }
            ?>
        </table>
    </div>

</div>

<script>
    function showNotifications() {
        const count = <?= $pending_count ?>;
        if (count > 0) {
            alert(`You have ${count} pending leave request(s). Check your leave history for details.`);
        } else {
            alert('No new notifications.');
        }
    }

    function approveLeave(leaveId) {
        if (confirm('Are you sure you want to approve this leave?')) {
            fetch('approve_reject.php?action=approve&leave_id=' + leaveId)
                .then(response => response.text())
                .then(data => {
                    if (data == 'Success') {
                        showMessage('Leave approved successfully', 'success');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showMessage('Error: ' + data, 'error');
                    }
                })
                .catch(error => showMessage('Error: ' + error, 'error'));
        }
    }

    function rejectLeave(leaveId) {
        if (confirm('Are you sure you want to reject this leave?')) {
            fetch('approve_reject.php?action=reject&leave_id=' + leaveId)
                .then(response => response.text())
                .then(data => {
                    if (data == 'Success') {
                        showMessage('Leave rejected successfully', 'warning');
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showMessage('Error: ' + data, 'error');
                    }
                })
                .catch(error => showMessage('Error: ' + error, 'error'));
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

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }
</script>

</body>
</html>
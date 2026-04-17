<?php
session_start();
if(!isset($_SESSION['user_email']) || $_SESSION['user_role'] != 'admin') {
    header("Location: login.php");
    exit();
}
include("db.php");

// Handle Approval / Rejection
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if($action == 'approve') {
        mysqli_query($conn, "UPDATE leaves SET status='Approved' WHERE id=$id");
    } elseif($action == 'reject') {
        mysqli_query($conn, "UPDATE leaves SET status='Rejected' WHERE id=$id");
    }
    header("Location: admin_dashboard.php");
    exit();
}

$leaves = mysqli_query($conn, "SELECT * FROM leaves ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Faculty Leave System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" style="background: linear-gradient(180deg, #111, #333);">
    <h2>🛡️ Admin Panel</h2>
    <a href="admin_dashboard.php">🏠 All Leaves</a>
    <a href="calendar.php">📅 Calendar</a>
    <a href="reports.php">📊 Reports</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<!-- Main -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <h2>Leave Management (Admin)</h2>
        <div class="user">🛡️ Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
    </div>

    <!-- Table -->
    <div class="table-box">
        <h3>Manage Faculty Leave Requests</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php 
            if($leaves && mysqli_num_rows($leaves) > 0) {
                while($row = mysqli_fetch_assoc($leaves)) { 
                    $statusClass = strtolower($row['status']);
                    if ($statusClass == 'pending') $statusClass = 'pending';
                    elseif ($statusClass == 'approved') $statusClass = 'approved';
                    else $statusClass = 'rejected';
            ?>
            <tr>
                <td><?= htmlspecialchars($row['id']) ?></td>
                <td><?= htmlspecialchars($row['leave_type']) ?></td>
                <td><?= htmlspecialchars($row['from_date']) ?></td>
                <td><?= htmlspecialchars($row['to_date']) ?></td>
                <td><?= htmlspecialchars($row['reason']) ?></td>
                <td class="<?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></td>
                <td>
                    <?php if(strtolower($row['status']) == 'pending') { ?>
                        <a href="admin_dashboard.php?action=approve&id=<?= $row['id'] ?>" style="background: green; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px; margin-right: 5px;">Approve</a>
                        <a href="admin_dashboard.php?action=reject&id=<?= $row['id'] ?>" style="background: red; color: white; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 12px;">Reject</a>
                    <?php } else { ?>
                        <span style="color: gray; font-size: 12px;">Processed</span>
                    <?php } ?>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='7'>No leave requests found.</td></tr>";
            }
            ?>
        </table>
    </div>

</div>

</body>
</html>

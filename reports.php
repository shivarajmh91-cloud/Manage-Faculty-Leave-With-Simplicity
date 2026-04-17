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

// 1. Data for Pie Chart (Status Breakdown)
$status_query = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM leaves" . ($filter ? " WHERE $filter" : "") . " GROUP BY status");
$status_labels = [];
$status_data = [];
$status_colors = [];
while($row = mysqli_fetch_assoc($status_query)) {
    $status_labels[] = $row['status'];
    $status_data[] = $row['count'];
    if(strtolower($row['status']) == 'approved') $status_colors[] = '#10b981'; // Emerald
    elseif(strtolower($row['status']) == 'pending') $status_colors[] = '#f59e0b'; // Amber
    else $status_colors[] = '#ef4444'; // Red
}

// 2. Data for Bar Chart (Type Breakdown)
$type_query = mysqli_query($conn, "SELECT leave_type, COUNT(*) as count FROM leaves" . ($filter ? " WHERE $filter" : "") . " GROUP BY leave_type");
$type_labels = [];
$type_data = [];
while($row = mysqli_fetch_assoc($type_query)) {
    $type_labels[] = $row['leave_type'];
    $type_data[] = $row['count'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reports - Faculty Leave System</title>
    <link rel="stylesheet" href="style.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .chart-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .chart-card h3 {
            margin-bottom: 20px;
            color: #1e293b;
            font-size: 18px;
        }
        canvas {
            max-width: 100%;
        }
    </style>
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
        <h2>Analytics & Reports</h2>
        <div class="topbar-right">
            <button class="role-btn" title="Your Role"><?php echo strtoupper($user_role); ?></button>
            <button class="notification-btn" title="Notifications" onclick="showNotifications()">🔔<?php if($pending_count > 0) echo '<span class="badge">'.$pending_count.'</span>'; ?></button>
            <div class="user">👩‍🏫 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        </div>
    </div>

    <div class="charts-container">
        
        <!-- Status Chart -->
        <div class="chart-card">
            <h3>Leave Status Breakdown</h3>
            <canvas id="statusChart"></canvas>
        </div>

        <!-- Type Chart -->
        <div class="chart-card">
            <h3>Leave Types Utilized</h3>
            <canvas id="typeChart"></canvas>
        </div>

    </div>

</div>

<script>
    // Pie Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($status_labels) ?>,
            datasets: [{
                data: <?= json_encode($status_data) ?>,
                backgroundColor: <?= json_encode($status_colors) ?>,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            plugins: {
                legend: { position: 'bottom' }
            },
            cutout: '70%'
        }
    });

    // Bar Chart
    const ctxType = document.getElementById('typeChart').getContext('2d');
    new Chart(ctxType, {
        type: 'bar',
        data: {
            labels: <?= json_encode($type_labels) ?>,
            datasets: [{
                label: 'Number of Leaves',
                data: <?= json_encode($type_data) ?>,
                backgroundColor: '#4f46e5',
                borderRadius: 8
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });

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

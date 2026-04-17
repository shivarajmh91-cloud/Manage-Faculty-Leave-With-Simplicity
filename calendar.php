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

$leaves_query = mysqli_query($conn, "SELECT * FROM leaves WHERE status IN ('Approved', 'Pending')" . ($filter ? " AND $filter" : ""));
$events = array();

if($leaves_query) {
    while($row = mysqli_fetch_assoc($leaves_query)) {
        // Determine color based on status
        $color = (strtolower($row['status']) == 'approved') ? '#10b981' : '#f59e0b'; // Emerald for Approved, Amber for Pending

        $events[] = array(
            'id' => $row['id'],
            'title' => $row['leave_type'] . ' (' . $row['status'] . ')',
            'start' => $row['from_date'],
            'end' => date('Y-m-d', strtotime($row['to_date'] . ' +1 day')), // FullCalendar end date is exclusive
            'color' => $color,
            'allDay' => true
        );
    }
}

$events_json = json_encode($events);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Calendar - Faculty Leave System</title>
    <link rel="stylesheet" href="style.css">
    
    <!-- FullCalendar CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          events: <?= $events_json ?>,
          eventContent: function(arg) {
              return { html: '<div style="padding: 2px 5px; font-size: 12px; font-weight: 500;">' + arg.event.title + '</div>' };
          }
        });
        calendar.render();
      });
    </script>

    <style>
        /* =========================================
           ULTRA-PREMIUM CALENDAR STYLING
           ========================================= */
        #calendar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 24px;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
            font-family: 'Inter', sans-serif;
            border: 1px solid rgba(255,255,255,0.8);
        }
        
        /* Grid Lines */
        .fc-theme-standard td, .fc-theme-standard th {
            border-color: #e2e8f0;
        }
        
        /* Headers (Mon, Tue, Wed...) */
        .fc-col-header-cell {
            background: transparent;
            padding: 15px 0;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 1px;
            color: #64748b;
            border-bottom: 2px solid #e2e8f0 !important;
        }
        
        /* Date Numbers */
        .fc-daygrid-day-number {
            color: #1e293b;
            font-weight: 600;
            padding: 8px 12px !important;
            text-decoration: none;
            transition: 0.3s;
        }
        .fc-daygrid-day-number:hover {
            color: #4f46e5;
            background: #f1f5f9;
            border-radius: 50%;
            text-decoration: none;
        }
        
        /* Current Day Highlight */
        .fc-day-today {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05), rgba(79, 70, 229, 0.1)) !important;
        }
        
        /* Events (The colored blocks) */
        .fc-h-event {
            border: none;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 2px 4px !important;
            transition: transform 0.2s ease;
        }
        .fc-h-event:hover {
            transform: scale(1.02);
            z-index: 5;
        }
        
        /* Toolbar (Buttons & Title) */
        .fc-toolbar-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700 !important;
            color: #0f172a;
            font-size: 26px !important;
        }
        
        /* Primary Buttons */
        .fc .fc-button-primary {
            background-color: white;
            color: #475569;
            border: 1px solid #cbd5e1;
            text-transform: capitalize;
            font-weight: 600;
            border-radius: 8px;
            padding: 8px 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .fc .fc-button-primary:hover {
            background-color: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
            transform: translateY(-1px);
        }
        .fc .fc-button-primary:not(:disabled):active, .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
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

<!-- Main Content -->
<div class="main">
    
    <!-- Message Notification -->
    <div id="messageBox" class="message-box"></div>

    <!-- Topbar -->
    <div class="topbar">
        <h2>Leave Calendar</h2>
        <div class="topbar-right">
            <button class="role-btn" title="Your Role"><?php echo strtoupper($user_role); ?></button>
            <button class="notification-btn" title="Notifications" onclick="showNotifications()">🔔<?php if($pending_count > 0) echo '<span class="badge">'.$pending_count.'</span>'; ?></button>
            <div class="user">👩‍🏫 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div id='calendar'></div>
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

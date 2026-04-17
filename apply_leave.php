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
$user_role = $_SESSION['user_role'];

if(isset($_POST['submit'])){
    $user_email = mysqli_real_escape_string($conn, $_SESSION['user_email']);
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $from = mysqli_real_escape_string($conn, $_POST['from_date']);
    $to = mysqli_real_escape_string($conn, $_POST['to_date']);
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // Handle file upload
    $document_file = NULL;
    if(isset($_FILES['document']) && $_FILES['document']['size'] > 0) {
        $file_name = $_FILES['document']['name'];
        $file_tmp = $_FILES['document']['tmp_name'];
        $file_size = $_FILES['document']['size'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        
        // Allowed file types
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
        
        // Max file size: 5MB
        if($file_size <= 5242880 && in_array(strtolower($file_ext), $allowed_types)) {
            // Create unique filename
            $new_file_name = time() . '_' . md5($file_name) . '.' . $file_ext;
            $upload_dir = 'uploads/';
            
            if(move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
                $document_file = $new_file_name;
            } else {
                $error_message = "Error uploading file. Please try again.";
            }
        } else {
            $error_message = "Invalid file. Allowed: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (Max 5MB)";
        }
    }

    // Later we can link leave to user_id or email
    if(!isset($error_message)) {
        $query = "INSERT INTO leaves (email, branch, leave_type, from_date, to_date, reason, document_file)
                  VALUES ('$user_email','$branch','$type','$from','$to','$reason','$document_file')";

        if(mysqli_query($conn, $query)){
            // Get user name
            $user_query = mysqli_query($conn, "SELECT name FROM users WHERE email='$user_email'");
            $user_data = mysqli_fetch_assoc($user_query);
            $user_name = $user_data['name'] ?? 'Faculty Member';
            
            // Get HOD and Principal emails for this branch
            $hod_query = mysqli_query($conn, "SELECT email FROM users WHERE role='hod' AND branch='$branch'");
            $hod_data = mysqli_fetch_assoc($hod_query);
            $hod_email = $hod_data['email'] ?? null;
            
            $principal_query = mysqli_query($conn, "SELECT email FROM users WHERE role='principal'");
            $principal_data = mysqli_fetch_assoc($principal_query);
            $principal_email = $principal_data['email'] ?? null;
            
            // Prepare email content
            $subject = "Leave Application Confirmation - $type from $from to $to";
            $message = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                    .header { background: linear-gradient(45deg, #00c6ff, #0072ff); color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                    .details { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0; }
                    .details p { margin: 8px 0; }
                    .label { font-weight: bold; color: #333; }
                    .value { color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Leave Application Confirmation</h2>
                    </div>
                    
                    <p>Dear $user_name,</p>
                    
                    <p>Your leave application has been successfully submitted. Here are the details:</p>
                    
                    <div class='details'>
                        <p><span class='label'>Leave Type:</span> <span class='value'>$type</span></p>
                        <p><span class='label'>From Date:</span> <span class='value'>$from</span></p>
                        <p><span class='label'>To Date:</span> <span class='value'>$to</span></p>
                        <p><span class='label'>Branch:</span> <span class='value'>$branch</span></p>
                        <p><span class='label'>Reason:</span> <span class='value'>$reason</span></p>
                        <p><span class='label'>Status:</span> <span class='value'>Pending</span></p>
                    </div>
                    
                    <p>Your leave request is now pending approval from your Head of Department (HOD) and Principal. You will be notified once a decision is made.</p>
                    
                    <p>If you have any questions, please contact your HOD.</p>
                    
                    <p>Best regards,<br>Faculty Leave Management System</p>
                </div>
            </body>
            </html>";
            
            // Send email to faculty
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: noreply@facultyleave.com\r\n";
            
            mail($user_email, $subject, $message, $headers);
            
            // Send notification to HOD
            if($hod_email) {
                $hod_subject = "New Leave Application - $user_name ($type)";
                $hod_message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        .header { background: #f39c12; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                        .details { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0; }
                        .details p { margin: 8px 0; }
                        .label { font-weight: bold; color: #333; }
                        .value { color: #666; }
                        .action-btn { background: #0072ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 15px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>New Leave Application Awaiting Approval</h2>
                        </div>
                        
                        <p>Dear HOD,</p>
                        
                        <p>A new leave application has been submitted and is pending your approval. Here are the details:</p>
                        
                        <div class='details'>
                            <p><span class='label'>Faculty Name:</span> <span class='value'>$user_name</span></p>
                            <p><span class='label'>Email:</span> <span class='value'>$user_email</span></p>
                            <p><span class='label'>Leave Type:</span> <span class='value'>$type</span></p>
                            <p><span class='label'>From Date:</span> <span class='value'>$from</span></p>
                            <p><span class='label'>To Date:</span> <span class='value'>$to</span></p>
                            <p><span class='label'>Branch:</span> <span class='value'>$branch</span></p>
                            <p><span class='label'>Reason:</span> <span class='value'>$reason</span></p>
                        </div>
                        
                        <p><a href='http://localhost/faculty_leave_system/leave_history.php' class='action-btn'>Review Leave Request</a></p>
                        
                        <p>Please review and take appropriate action.</p>
                        
                        <p>Best regards,<br>Faculty Leave Management System</p>
                    </div>
                </body>
                </html>";
                
                mail($hod_email, $hod_subject, $hod_message, $headers);
            }
            
            // Send notification to Principal
            if($principal_email) {
                $principal_subject = "Leave Application Notification - $user_name ($branch)";
                $principal_message = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                        .header { background: #27ae60; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
                        .details { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0; }
                        .details p { margin: 8px 0; }
                        .label { font-weight: bold; color: #333; }
                        .value { color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Leave Application Notification</h2>
                        </div>
                        
                        <p>Dear Principal,</p>
                        
                        <p>For your information, a new leave application has been submitted. Here are the details:</p>
                        
                        <div class='details'>
                            <p><span class='label'>Faculty Name:</span> <span class='value'>$user_name</span></p>
                            <p><span class='label'>Branch:</span> <span class='value'>$branch</span></p>
                            <p><span class='label'>Leave Type:</span> <span class='value'>$type</span></p>
                            <p><span class='label'>From Date:</span> <span class='value'>$from</span></p>
                            <p><span class='label'>To Date:</span> <span class='value'>$to</span></p>
                        </div>
                        
                        <p>This notification is for your records. The corresponding HOD will handle the approval process.</p>
                        
                        <p>Best regards,<br>Faculty Leave Management System</p>
                    </div>
                </body>
                </html>";
                
                mail($principal_email, $principal_subject, $principal_message, $headers);
            }
            
            $success_message = "Leave Applied Successfully! Confirmation email sent. You will be redirected shortly.";
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Leave - Faculty Leave System</title>
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
    <div id="messageBox" class="message-box">
        <?php 
        if (isset($success_message)) {
            echo '<div class="message message-success">' . htmlspecialchars($success_message) . '</div>';
        }
        if (isset($error_message)) {
            echo '<div class="message message-error">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>
    </div>

    <!-- Topbar -->
<div class="topbar">
        <div style="display:flex; align-items:center; gap:10px; cursor:pointer;" onclick="toggleSidebar()">
            <span>☰</span>
            <h2>Apply Leave</h2>
        </div>
        <div class="topbar-right">
            <button class="role-btn" title="Your Role"><?php echo strtoupper($user_role); ?></button>
            <button class="notification-btn" title="Notifications" onclick="showNotifications()">🔔<?php if($pending_count > 0) echo '<span class="badge">'.$pending_count.'</span>'; ?></button>
            <div class="user">👩‍🏫 Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        </div>
    </div>

    <!-- Mobile overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>

    <!-- Apply Form -->
    <div class="settings-grid">
        <div class="settings-card" style="max-width: 600px; margin: 0 auto; width: 100%;">
            <h3 style="text-align: center; margin-bottom: 5px;">📝 New Leave Request</h3>
            <p style="text-align: center; color: #666; margin-bottom: 20px;">Fill the form to submit your request</p>

            <form method="POST" enctype="multipart/form-data">

                <label style="display:block; text-align:left; margin-top:10px; font-weight:500;">Branch/Department</label>
                <select name="branch" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                    <option value="">Select Branch</option>
                    <option>CSE (Computer Science Engineering)</option>
                    <option>ECE (Electronics and Communication Engineering)</option>
                    <option>ME (Mechanical Engineering)</option>
                    <option>EE (Electrical Engineering)</option>
                    <option>CE/CV (Civil Engineering)</option>
                    <option>Others</option>
                </select>

                <label style="display:block; text-align:left; margin-top:10px; font-weight:500;">Leave Type</label>
                <select name="type" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd; outline: none;">
                    <option value="">Select Leave Type</option>
                    <option>Sick Leave</option>
                    <option>Casual Leave</option>
                    <option>Emergency Leave</option>
                </select>

                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <div style="flex: 1;">
                        <label style="display:block; text-align:left; font-weight:500;">From Date</label>
                        <input type="date" name="from_date" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <div style="flex: 1;">
                        <label style="display:block; text-align:left; font-weight:500;">To Date</label>
                        <input type="date" name="to_date" required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                </div>

                <label style="display:block; text-align:left; margin-top:15px; font-weight:500;">Reason</label>
                <textarea name="reason" placeholder="Enter detailed reason..." required style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd; outline: none; height: 100px; resize: none;"></textarea>

                <label style="display:block; text-align:left; margin-top:15px; font-weight:500;">Upload Document (Medical/Proof) - Optional</label>
                <input type="file" name="document" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx" style="width: 100%; padding: 10px; margin-top: 5px; border-radius: 8px; border: 1px solid #ddd;">
                <small style="color: #666; display: block; margin-top: 5px;">Supported: PDF, DOC, DOCX, JPG, PNG, XLS (Max 5MB)</small>

                <button type="submit" name="submit" style="width: 100%; margin-top: 20px; padding: 12px; border-radius: 25px; border: none; background: linear-gradient(45deg, #00c6ff, #0072ff); color: white; font-weight: 600; cursor: pointer; transition: 0.3s;">Submit Leave</button>

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

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.getElementById('mobileOverlay');
        
        sidebar.classList.toggle('open');
        overlay.classList.toggle('active');
    }

    // Redirect after successful application
    <?php if (isset($success_message)) { ?>
        setTimeout(() => {
            window.location = 'leave_history.php';
        }, 2000);
    <?php } ?>
</script>

</body>
</html>
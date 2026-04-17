<?php
session_start();
if(!isset($_SESSION['user_email'])){
    header("Location: login.php");
    exit();
}

include("db.php");

if(isset($_GET['file']) && isset($_GET['leave_id'])) {
    $file_name = basename($_GET['file']);
    $leave_id = mysqli_real_escape_string($conn, $_GET['leave_id']);
    $user_email = $_SESSION['user_email'];
    $user_role = $_SESSION['user_role'];
    
    // Verify user has permission to download this file
    $verify_query = "SELECT email, document_file, branch FROM leaves WHERE id='$leave_id'";
    $result = mysqli_query($conn, $verify_query);
    
    if($result && mysqli_num_rows($result) > 0) {
        $leave = mysqli_fetch_assoc($result);
        
        // Check if user is authorized to download
        $authorized = false;
        if($leave['email'] == $user_email) {
            $authorized = true; // Faculty can download their own files
        } else if($user_role == 'hod') {
            // HOD can download files from their branch
            $user_query = mysqli_query($conn, "SELECT branch FROM users WHERE email='$user_email'");
            $user = mysqli_fetch_assoc($user_query);
            if($user['branch'] == $leave['branch']) {
                $authorized = true;
            }
        } else if($user_role == 'principal') {
            $authorized = true; // Principal can download any file
        }
        
        if($authorized && $leave['document_file']) {
            $file_path = 'uploads/' . $leave['document_file'];
            
            if(file_exists($file_path)) {
                // Get original file extension
                $ext = pathinfo($file_path, PATHINFO_EXTENSION);
                
                // Set appropriate content type
                $content_types = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ];
                
                $content_type = isset($content_types[$ext]) ? $content_types[$ext] : 'application/octet-stream';
                
                header('Content-Type: ' . $content_type);
                header('Content-Disposition: attachment; filename="' . $file_name . '"');
                header('Content-Length: ' . filesize($file_path));
                readfile($file_path);
                exit();
            }
        }
    }
    
    echo "Unauthorized or file not found";
} else {
    echo "Invalid request";
}
?>

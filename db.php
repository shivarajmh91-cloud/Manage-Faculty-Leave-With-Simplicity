<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = mysqli_connect("127.0.0.1", "root", "", "faculty_leave_db");

if(!$conn){
    die("Database Connection Failed: " . mysqli_connect_error());
}
?>
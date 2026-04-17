<?php
error_reporting(0);
include("db.php");

mysqli_query($conn, "ALTER TABLE users ADD COLUMN name VARCHAR(255) DEFAULT 'Faculty'");
mysqli_query($conn, "ALTER TABLE users ADD COLUMN role VARCHAR(50) DEFAULT 'faculty'");
mysqli_query($conn, "UPDATE users SET role='admin' WHERE email='test@gmail.com'");

echo "Done";
?>

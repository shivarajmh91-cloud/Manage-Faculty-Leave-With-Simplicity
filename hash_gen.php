<?php
// Generate password hash
$password = "123456";
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
?>

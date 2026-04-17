<?php
include("db.php");

// Update existing test accounts with hashed passwords
$updates = [
    ['admin@gmail.com', '1234'],
    ['ranjitha@gmail.com', '2004'],
    ['shivarajmh@gmail.com', '2005']
];

foreach($updates as $account) {
    $email = $account[0];
    $password = $account[1];
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    
    $query = "UPDATE users SET password='$hashed' WHERE email='$email'";
    if(mysqli_query($conn, $query)) {
        echo "✓ Updated: $email with password: $password<br>";
    } else {
        echo "✗ Failed to update: $email<br>";
    }
}

echo "<br><strong>Password migration complete!</strong><br>";
echo "You can now delete this file.<br>";
echo "Existing accounts can log in with their original passwords.<br>";
?>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysql_path = 'C:\\wamp64\\bin\\mysql\\mysql8.0.30\\bin\\mysql.exe';
$schema_file = 'schema.sql';

if (file_exists($mysql_path) && file_exists($schema_file)) {
    $command = "\"$mysql_path\" -u root -h127.0.0.1 < \"$schema_file\"";
    $output = shell_exec($command);
    echo "<h2>Schema executed:</h2><pre>$output</pre>";
} else {
    echo "MySQL or schema.sql not found.";
}

// Run setup_db.php
include('setup_db.php');
echo "<br>Setup DB columns added.<br>";

// Test connection
include('db.php');
$result = mysqli_query($conn, "SELECT COUNT(*) FROM users");
echo "Users table OK: " . mysqli_fetch_row($result)[0] . " users.";
?>


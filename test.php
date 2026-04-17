include "db.php";
$query = mysqli_query($conn, "SELECT COUNT(*) as total FROM leaves");
$total = mysqli_fetch_assoc($query)['total'];

$query = mysqli_query($conn, "SELECT COUNT(*) as pending FROM leaves WHERE status='Pending'");
$pending = mysqli_fetch_assoc($query)['pending'];

$query = mysqli_query($conn, "SELECT COUNT(*) as approved FROM leaves WHERE status='Approved'");
$approved = mysqli_fetch_assoc($query)['approved'];

$query = mysqli_query($conn, "SELECT COUNT(*) as rejected FROM leaves WHERE status='Rejected'");
$rejected = mysqli_fetch_assoc($query)['rejected'];

$query = mysqli_query($conn, "SELECT DISTINCT role, COUNT(*) as count FROM users GROUP BY role");
$roles = '';
while($row = mysqli_fetch_assoc($query)) {
    $roles .= $row['role'] . ': ' . $row['count'] . '<br>';
}

echo "<h2>DB Debug</h2>";
echo "<h2>DB Debug</h2>";

if(isset($_POST['create_test_leave'])) {
    $test_sql = "INSERT INTO leaves (email, leave_type, from_date, to_date, branch, reason, status, created_at) VALUES ('test-faculty@example.com', 'Annual Leave', '2024-12-01', '2024-12-05', 'CSE', 'Personal vacation', 'Pending', NOW())";
    if(mysqli_query($conn, $test_sql)) {
        echo "<p style='color:green'>✅ Test Pending leave created!</p>";
    } else {
        echo "<p style='color:red'>❌ Error: " . mysqli_error($conn) . "</p>";
    }
}
echo "Total leaves: $total<br>";
echo "Pending: $pending<br>";
echo "Approved: $approved<br>";
echo "Rejected: $rejected<br>";
echo "Roles:<br>$roles";

$result = mysqli_query($conn, "SELECT id, email, leave_type, status FROM leaves ORDER BY id DESC LIMIT 10");
if($result) {
    echo "<h3>Recent Leaves:</h3><table border='1'><tr><th>ID</th><th>Email</th><th>Type</th><th>Status</th></tr>";
    while($row = mysqli_fetch_assoc($result)) {
        echo "<tr><td>{$row['id']}</td><td>{$row['email']}</td><td>{$row['leave_type']}</td><td>{$row['status']}</td></tr>";
    }
    echo "</table>";
}
?>
?>
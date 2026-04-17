<?php
session_start();
include("db.php");

// Redirect if already logged in
if(isset($_SESSION['user_email'])){
    if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0){
        $user = mysqli_fetch_assoc($result);
        
        // Verify password using password_verify() for hashed passwords
        // Also fallback to plain text for old accounts
        if(password_verify($password, $user['password']) || $user['password'] === $password) {
            // Create session variables
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = isset($user['name']) ? $user['name'] : 'Faculty';
            $_SESSION['user_role'] = isset($user['role']) ? $user['role'] : 'faculty';
            $_SESSION['user_branch'] = isset($user['branch']) ? $user['branch'] : 'Others';
            
            if ($_SESSION['user_role'] == 'principal') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Email or Password";
        }
    } else {
        $error = "Invalid Email or Password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Faculty Leave System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-bg">

    <div class="login-container">
        <div class="login-card">

            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to continue</p>

            <?php if(isset($error)) { ?>
                <div class="error" style="background: rgba(255,60,60,0.8); color: white; border-radius: 8px; padding: 10px; margin-bottom: 15px; border: none; font-weight: 500; backdrop-filter: blur(5px);"><?= $error ?></div>
            <?php } ?>

            <form method="POST">

                <input type="email" name="email" placeholder="Email Address" required>

                <input type="password" name="password" placeholder="Password" required>

                <button type="submit" name="login">Login</button>

            </form>

            <div class="extra">
                <p>New user? <a href="register.php">Create an account</a></p>
            </div>

        </div>
    </div>

</div>

</body>
</html>
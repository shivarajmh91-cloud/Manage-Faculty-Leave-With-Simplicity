<?php
include("db.php");

if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif(strlen($password) < 6) {
        $error = "Password must be at least 6 characters long!";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);

        $query = "INSERT INTO users (name, email, password, role, branch) VALUES ('$name', '$email', '$hashed_password', '$role', '$branch')";
        
        if(mysqli_query($conn, $query)){
            $success = "Registration Successful! Redirecting to login...";
        } else {
            $error = "Registration failed. Email might already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Faculty Leave System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .select-register {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border-radius: 10px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 14px;
        }
        .select-register::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .select-register option {
            color: #333;
            background: white;
        }
    </style>
</head>
<body>

<div class="login-bg">

    <div class="login-container">
        <div class="login-card">

            <h2>Create Account</h2>
            <p class="subtitle">Join the Faculty Leave System</p>

            <?php if(isset($error)) { ?>
                <div class="error" style="background: rgba(255,60,60,0.8); color: white; border-radius: 8px; padding: 10px; margin-bottom: 15px; border: none; font-weight: 500; backdrop-filter: blur(5px);"><?= $error ?></div>
            <?php } ?>

            <?php if(isset($success)) { ?>
                <div class="success" style="background: rgba(60,255,60,0.8); color: white; border-radius: 8px; padding: 10px; margin-bottom: 15px; border: none; font-weight: 500; backdrop-filter: blur(5px);"><?= $success ?></div>
            <?php } ?>

            <form method="POST">

                <input type="text" name="name" placeholder="Full Name" required>

                <input type="email" name="email" placeholder="Email Address" required>

                <input type="password" name="password" placeholder="Password (Min 6 characters)" required>

                <input type="password" name="confirm_password" placeholder="Confirm Password" required>

                <select name="role" class="select-register" required>
                    <option value="">Select Role</option>
                    <option value="faculty">Faculty</option>
                    <option value="hod">HOD (Head of Department)</option>
                    <option value="principal">Principal</option>
                </select>

                <select name="branch" class="select-register" required>
                    <option value="">Select Branch</option>
                    <option value="CSE (Computer Science Engineering)">CSE - Computer Science Engineering</option>
                    <option value="ECE (Electronics and Communication Engineering)">ECE - Electronics and Communication Engineering</option>
                    <option value="ME (Mechanical Engineering)">ME - Mechanical Engineering</option>
                    <option value="EE (Electrical Engineering)">EE - Electrical Engineering</option>
                    <option value="CE/CV (Civil Engineering)">CE/CV - Civil Engineering</option>
                    <option value="Others">Others</option>
                </select>

                <button type="submit" name="register">Sign Up</button>

            </form>

            <div class="extra">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>

        </div>
    </div>

</div>

<?php if(isset($success)) { ?>
    <script>
        setTimeout(() => {
            window.location = 'login.php';
        }, 2000);
    </script>
<?php } ?>

</body>
</html>
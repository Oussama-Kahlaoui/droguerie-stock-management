<?php
// Include database connection
include('db.php');
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Get user from database
    $query = "SELECT * FROM users WHERE email=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: index.php"); // Redirect to dashboard
            exit;
        } else {
            $error_message = "Incorrect email or password.";
        }
    } else {
        $error_message = "Incorrect email or password.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

<div class="flex">
    <div class="container">
        <div class="user-logo"></div>
        <form method="POST">
            <h1>Login</h1>

            <div class="inputs">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="inputs">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="forget-pass">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <div class="box">
                <button type="submit">Login</button>
            </div>

            <hr>

            <div class="signup">
                <p>Don't have an account? <a href="register.php">Sign up</a></p>
            </div>

            <!-- Show error message if login fails -->
            <?php
            if (isset($error_message)) {
                echo "<p style='color: red; text-align:center;'>$error_message</p>";
            }
            ?>
        </form>
    </div>
</div>

</body>
</html>

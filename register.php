<?php
// Include database connection
include('db.php');
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password before storing it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database
    $query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: login.php?registered=success"); // Redirect after successful registration
        exit;
    } else {
        $error_message = "Error: " . mysqli_error($conn);
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
    <title>Register</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

<div class="flex">
    <div class="container">
        <div class="user-logo"></div>
        <form method="POST">
            <h1>Register</h1>

            <div class="inputs">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>

            <div class="inputs">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="inputs">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="box">
                <button type="submit">Register</button>
            </div>

            <hr>

            <div class="signup">
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>

            <!-- Show error message if registration fails -->
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

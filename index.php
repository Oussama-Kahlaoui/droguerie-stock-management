<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Stock Management</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

<div class="sidebar">
    <h2>Gestion de Stock</h2>
    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="products.php">Manage Products</a></li>
        <li><a href="sales.php">Sales</a></li>
        <li><a href="suppliers.php">Suppliers</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="main-content">
    <div class="dashboard-header">
        <h1>Welcome, <?php echo $user['name']; ?>!</h1>
        <a href="logout.php">Logout</a>
    </div>

    <div class="cards">
        <div class="card">
            <h2>Total Products</h2>
            <p>50</p>
        </div>
        <div class="card">
            <h2>Total Sales</h2>
            <p>$5,000</p>
        </div>
        <div class="card">
            <h2>Suppliers</h2>
            <p>15</p>
        </div>
    </div>
</div>

</body>
</html>

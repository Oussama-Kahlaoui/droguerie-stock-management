<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion de Stock</title>
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Gestion de Stock</h2>
        <ul>
            <li><a href="index.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="admin/products.php"><i class="fas fa-box"></i> Produits</a></li>
            <li><a href="admin/sales.php"><i class="fas fa-shopping-cart"></i> Ventes</a></li>
            <li><a href="admin/suppliers.php"><i class="fas fa-truck"></i> Fournisseurs</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header">
            <h1>Bienvenue, <?php echo htmlspecialchars($user['name']); ?> !</h1>
            <a class="logout-btn" href="logout.php">Déconnexion</a>
        </header>

        <!-- Dashboard Cards -->
        <section class="dashboard-cards">
            <div class="card">
                <i class="fas fa-box"></i>
                <h2>Produits</h2>
                <p>50</p>
            </div>
            <div class="card">
                <i class="fas fa-shopping-cart"></i>
                <h2>Ventes</h2>
                <p>5,000 $</p>
            </div>
            <div class="card">
                <i class="fas fa-truck"></i>
                <h2>Fournisseurs</h2>
                <p>15</p>
            </div>
        </section>
    </main>
</div>

</body>
</html>

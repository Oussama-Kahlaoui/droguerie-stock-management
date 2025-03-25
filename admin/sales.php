<?php
include '../db.php'; // Include PDO connection

// Get today's sales statistics
$stmt = $pdo->query("
    SELECT 
        COALESCE(SUM(s.quantity * p.price), 0) as total_sales,
        COUNT(*) as total_transactions,
        COUNT(DISTINCT s.product_id) as unique_products_sold,
        AVG(s.quantity * p.price) as average_sale
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE DATE(s.sale_date) = CURDATE()
");
$todaySales = $stmt->fetch();

// Get monthly sales data for the last 6 months
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(s.sale_date, '%M %Y') as month,
        SUM(s.quantity * p.price) as total_sales,
        COUNT(*) as total_transactions,
        COUNT(DISTINCT s.product_id) as unique_products_sold,
        AVG(s.quantity * p.price) as average_sale
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
    ORDER BY s.sale_date ASC
");
$monthlySales = $stmt->fetchAll();

// Get best-selling products with more details
$stmt = $pdo->query("
    SELECT 
        p.name,
        p.category,
        SUM(s.quantity) as total_sold,
        SUM(s.quantity * p.price) as total_revenue,
        COUNT(DISTINCT s.sale_date) as days_sold,
        AVG(s.quantity * p.price) as average_sale_price
    FROM sales s 
    JOIN products p ON s.product_id = p.id 
    GROUP BY p.id, p.name, p.category
    ORDER BY total_sold DESC 
    LIMIT 5
");
$bestSelling = $stmt->fetchAll();

// Get sales by category
$stmt = $pdo->query("
    SELECT 
        p.category,
        COUNT(DISTINCT s.id) as total_transactions,
        SUM(s.quantity) as total_quantity,
        SUM(s.quantity * p.price) as total_revenue,
        AVG(s.quantity * p.price) as average_sale
    FROM sales s
    JOIN products p ON s.product_id = p.id
    GROUP BY p.category
    ORDER BY total_revenue DESC
");
$categorySales = $stmt->fetchAll();

// Get recent sales with product details
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.sale_date,
        s.quantity * p.price as total_amount,
        s.quantity,
        p.name as product_name,
        p.category
    FROM sales s
    JOIN products p ON s.product_id = p.id
    ORDER BY s.sale_date DESC
    LIMIT 10
");
$recentSales = $stmt->fetchAll();

// Convert to JSON
$monthly_sales_json = json_encode($monthlySales);
$best_selling_json = json_encode($bestSelling);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ventes - Gestion de Stock</title>
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-info h3 {
            margin: 0;
            font-size: 14px;
            color: #666;
        }

        .stat-info p {
            margin: 5px 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .stat-info small {
            color: #666;
            font-size: 12px;
        }

        .chart-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            margin: 0 0 20px;
            color: #333;
            font-size: 18px;
        }

        .sales-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .sales-table th, .sales-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .sales-table th {
            font-weight: 600;
            color: #666;
            background: #f8f9fa;
        }

        .sales-table tr:hover {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .chart-container {
                grid-template-columns: 1fr;
            }

            .sales-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <h2>Gestion de Stock</h2>
        <ul>
            <li><a href="../index.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-box"></i> Produits</a></li>
            <li><a href="sales.php" class="active"><i class="fas fa-shopping-cart"></i> Ventes</a></li>
            <li><a href="suppliers.php"><i class="fas fa-truck"></i> Fournisseurs</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h1><i class="fas fa-shopping-cart"></i> Ventes</h1>
            <div class="header-actions">
                <span class="date"><?php echo date('d M Y'); ?></span>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3>Ventes du Jour</h3>
                    <p><?php echo number_format($todaySales['total_sales'], 2); ?> $</p>
                    <small><?php echo $todaySales['total_transactions']; ?> transactions</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>Produits Vendus</h3>
                    <p><?php echo $todaySales['unique_products_sold']; ?></p>
                    <small>Aujourd'hui</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Panier Moyen</h3>
                    <p><?php echo number_format($todaySales['average_sale'], 2); ?> $</p>
                    <small>Aujourd'hui</small>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-container">
            <div class="chart-card">
                <h3>Évolution des Ventes</h3>
                <canvas id="salesChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Meilleurs Produits</h3>
                <canvas id="productsChart"></canvas>
            </div>
        </div>

        <!-- Recent Sales Table -->
        <div class="chart-card">
            <h3>Ventes Récentes</h3>
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Quantité</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><?php echo date('d M Y H:i', strtotime($sale['sale_date'])); ?></td>
                            <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($sale['category']); ?></td>
                            <td><?php echo $sale['quantity']; ?></td>
                            <td><?php echo number_format($sale['total_amount'], 2); ?> $</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Monthly Sales Chart
    new Chart(document.getElementById("salesChart"), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthlySales, 'month')); ?>,
            datasets: [{
                label: "Ventes Mensuelles ($)",
                data: <?php echo json_encode(array_column($monthlySales, 'total_sales')); ?>,
                borderColor: "#2e7d32",
                backgroundColor: "rgba(46, 125, 50, 0.2)",
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Best-Selling Products Chart
    new Chart(document.getElementById("productsChart"), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($bestSelling, 'name')); ?>,
            datasets: [{
                label: "Quantité Vendue",
                data: <?php echo json_encode(array_column($bestSelling, 'total_sold')); ?>,
                backgroundColor: "#1976d2"
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
</body>
</html>

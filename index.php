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

        .category-table, .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .category-table th, .category-table td,
        .products-table th, .products-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .category-table th, .products-table th {
            font-weight: 600;
            color: #666;
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge.success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge.warning {
            background: #fff3e0;
            color: #f57c00;
        }

        .badge.danger {
            background: #fce4ec;
            color: #c2185b;
        }

        .recent-products {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .recent-products h3 {
            margin: 0 0 20px;
            color: #333;
            font-size: 18px;
        }

        .recent-activities {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .activity-item small {
            color: #666;
            font-size: 12px;
        }

        @media (max-width: 768px) {
            .chart-container {
                grid-template-columns: 1fr;
            }

            .category-table, .products-table {
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
            <li><a href="index.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="admin/products.php"><i class="fas fa-box"></i> Produits</a></li>
            <li><a href="admin/sales.php"><i class="fas fa-shopping-cart"></i> Ventes</a></li>
            <li><a href="admin/suppliers.php"><i class="fas fa-truck"></i> Fournisseurs</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <main class="main-content">
    <header class="dashboard-header">
        <h1>Bienvenue, <?php echo htmlspecialchars($user['name']); ?> !</h1>
        <div class="header-actions">
            <span class="date"><?php echo date('d M Y'); ?></span>
            <a class="logout-btn" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </header>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                <i class="fas fa-box"></i>
            </div>
            <div class="stat-info">
                <h3>Total Produits</h3>
                <p id="totalProducts">0</p>
                <small>Valeur totale: <span id="totalValue">0 $</span></small>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-info">
                <h3>Ventes du Jour</h3>
                <p id="todaySales">0 $</p>
                <small>Transactions: <span id="todayTransactions">0</span></small>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h3>Stock Faible</h3>
                <p id="lowStock">0</p>
                <small>Expirant bientôt: <span id="expiringSoon">0</span></small>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fce4ec; color: #c2185b;">
                <i class="fas fa-calendar-times"></i>
            </div>
            <div class="stat-info">
                <h3>Produits Expirés</h3>
                <p id="expiredProducts">0</p>
                <small>À surveiller</small>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div id="lowStockAlert" class="low-stock-alert" style="display: none;">
        <i class="fas fa-exclamation-circle"></i>
        <span>Certains produits sont en stock faible. Veuillez vérifier l'inventaire.</span>
    </div>

    <!-- Stock by Category -->
    <div class="chart-container">
        <div class="chart-card">
            <h3>Stock par Catégorie</h3>
            <div class="category-stats">
                <table class="category-table">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Total</th>
                            <th>Valeur</th>
                            <th>Stock Faible</th>
                            <th>Expiré</th>
                            <th>Prochaine Expiration</th>
                        </tr>
                    </thead>
                    <tbody id="categoryStats">
                        <!-- Category stats will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="chart-card">
            <h3>Évolution des Ventes</h3>
            <canvas id="salesChart"></canvas>
        </div>
        <div class="chart-card">
            <h3>Distribution des Fournisseurs</h3>
            <canvas id="suppliersChart"></canvas>
        </div>
    </div>

    <!-- Recent Products -->
    <div class="recent-products">
        <h3>Produits Récents</h3>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Quantité</th>
                    <th>Prix</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody id="recentProducts">
                <!-- Recent products will be populated by JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Recent Activities -->
    <div class="recent-activities">
        <h3>Activités Récentes</h3>
        <ul class="activity-list" id="recentActivities">
            <!-- Activities will be populated by JavaScript -->
        </ul>
    </div>
</main>

</div>

<script>
let charts = {
    sales: null,
    suppliers: null
};

function updateDashboard() {
    $.ajax({
        url: 'get_data.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            // Update inventory stats with more details
            $('#totalProducts').text(data.inventoryStats.total_products);
            $('#totalValue').text(data.inventoryStats.total_value.toFixed(2) + ' $');
            $('#lowStock').text(data.inventoryStats.low_stock_count);
            $('#expiringSoon').text(data.inventoryStats.expiring_soon_count);
            $('#expiredProducts').text(data.inventoryStats.expired_count);

            // Update sales stats with more details
            $('#todaySales').text(data.todaySales.total_sales.toFixed(2) + ' $');
            $('#todayTransactions').text(data.todaySales.total_transactions);

            // Update category stats table with more details
            const categoryStatsHtml = data.categoryStats.map(category => `
                <tr>
                    <td>${category.category}</td>
                    <td>${category.total_quantity}</td>
                    <td>${category.total_value.toFixed(2)} $</td>
                    <td>
                        <span class="badge ${category.low_stock_count > 0 ? 'warning' : 'success'}">
                            ${category.low_stock_count}
                        </span>
                    </td>
                    <td>
                        <span class="badge ${category.expired_count > 0 ? 'danger' : 'success'}">
                            ${category.expired_count}
                        </span>
                    </td>
                    <td>${category.next_expiry_date ? new Date(category.next_expiry_date).toLocaleDateString() : '-'}</td>
                </tr>
            `).join('');
            $('#categoryStats').html(categoryStatsHtml);

            // Update recent products table with more details
            const recentProductsHtml = data.recentProducts.map(product => `
                <tr>
                    <td>${product.name}</td>
                    <td>${product.category}</td>
                    <td>
                        <span class="badge ${product.stock_status_color}">
                            ${product.quantity}
                        </span>
                    </td>
                    <td>${product.price.toFixed(2)} $</td>
                    <td>${new Date(product.expiry_date).toLocaleDateString()}</td>
                    <td>
                        <span class="badge ${product.status_color}">
                            ${product.status}
                        </span>
                    </td>
                </tr>
            `).join('');
            $('#recentProducts').html(recentProductsHtml);

            // Add Critical Stock section
            if (data.criticalStock.length > 0) {
                const criticalStockHtml = `
                    <div class="chart-card">
                        <h3>Stock Critique</h3>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Quantité</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.criticalStock.map(item => `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${item.category}</td>
                                        <td>${item.quantity}</td>
                                        <td>
                                            <span class="badge ${item.status_color}">
                                                ${item.status}
                                            </span>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                $('.chart-container').append(criticalStockHtml);
            }

            // Add Expiring Products section
            if (data.expiringProducts.length > 0) {
                const expiringProductsHtml = `
                    <div class="chart-card">
                        <h3>Produits en Expiration</h3>
                        <table class="products-table">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Catégorie</th>
                                    <th>Jours restants</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.expiringProducts.map(item => `
                                    <tr>
                                        <td>${item.name}</td>
                                        <td>${item.category}</td>
                                        <td>${item.days_until_expiry}</td>
                                        <td>
                                            <span class="badge ${item.status_color}">
                                                ${item.status}
                                            </span>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                $('.chart-container').append(expiringProductsHtml);
            }

            // Update sales chart with more details
            if (charts.sales) {
                charts.sales.destroy();
            }
            charts.sales = new Chart(document.getElementById('salesChart'), {
                type: 'line',
                data: {
                    labels: data.sales.map(item => item.month),
                    datasets: [{
                        label: 'Ventes ($)',
                        data: data.sales.map(item => item.total_sales),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
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
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Ventes: ${context.parsed.y.toFixed(2)} $`;
                                }
                            }
                        }
                    }
                }
            });

            // Update suppliers chart with more details
            if (charts.suppliers) {
                charts.suppliers.destroy();
            }
            charts.suppliers = new Chart(document.getElementById('suppliersChart'), {
                type: 'doughnut',
                data: {
                    labels: data.suppliers.map(item => item.name),
                    datasets: [{
                        data: data.suppliers.map(item => item.total_value),
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const supplier = data.suppliers[context.dataIndex];
                                    return [
                                        `Valeur totale: ${supplier.total_value.toFixed(2)} $`,
                                        `Produits: ${supplier.total_products}`,
                                        `Stock faible: ${supplier.low_stock_products}`
                                    ];
                                }
                            }
                        }
                    }
                }
            });

            // Update recent activities with more details
            const activitiesHtml = data.recentActivities.map(activity => `
                <li class="activity-item">
                    <div class="activity-icon" style="background: ${getActivityColor(activity.type)}; color: white;">
                        <i class="${getActivityIcon(activity.type)}"></i>
                    </div>
                    <div>
                        <div>${activity.description}</div>
                        <small>${activity.time}</small>
                    </div>
                </li>
            `).join('');
            $('#recentActivities').html(activitiesHtml);

            // Show/hide low stock alert
            if (data.inventoryStats.low_stock_count > 0) {
                $('#lowStockAlert').show();
            } else {
                $('#lowStockAlert').hide();
            }
        }
    });
}

// Initial update
$(document).ready(function() {
    updateDashboard();
    // Update every 30 seconds
    setInterval(updateDashboard, 30000);
});

function getActivityIcon(type) {
    switch(type) {
        case 'sale': return 'fas fa-shopping-cart';
        case 'stock': return 'fas fa-box';
        case 'supplier': return 'fas fa-truck';
        case 'alert': return 'fas fa-exclamation-triangle';
        default: return 'fas fa-info-circle';
    }
}

function getActivityColor(type) {
    switch(type) {
        case 'sale': return '#4caf50';
        case 'stock': return '#2196f3';
        case 'supplier': return '#ff9800';
        case 'alert': return '#f44336';
        default: return '#9e9e9e';
    }
}
</script>

</body>
</html>

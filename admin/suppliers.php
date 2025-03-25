<?php
include '../db.php';

// Get all suppliers
$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY name");
$suppliers = $stmt->fetchAll();

// Get supplier statistics
$stmt = $pdo->query("
    SELECT 
        s.id,
        s.name,
        COUNT(DISTINCT p.id) as total_products,
        COALESCE(SUM(p.quantity * p.price), 0) as total_value,
        COUNT(CASE WHEN p.quantity < 10 THEN 1 END) as low_stock_products,
        COUNT(CASE WHEN p.expiry_date <= CURDATE() THEN 1 END) as expired_products,
        COALESCE(AVG(p.price), 0) as average_price
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id, s.name
    ORDER BY total_value DESC
");
$supplierStats = $stmt->fetchAll();

// Get recent deliveries
$stmt = $pdo->query("
    SELECT 
        s.name as supplier_name,
        COUNT(DISTINCT p.id) as total_products,
        SUM(p.quantity) as total_quantity,
        COALESCE(SUM(p.quantity * p.price), 0) as total_value,
        MAX(p.created_at) as last_delivery_date
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id, s.name
    ORDER BY last_delivery_date DESC
    LIMIT 10
");
$recentDeliveries = $stmt->fetchAll();

// Add new supplier
if (isset($_POST['add_supplier'])) {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO suppliers (name) VALUES (?)");
        $stmt->execute([$name]);
    }
    header("Location: suppliers.php");
    exit;
}

// Edit supplier
if (isset($_POST['edit_supplier'])) {
    $id = (int)$_POST['supplier_id'];
    $name = trim($_POST['name']);

    $stmt = $pdo->prepare("UPDATE suppliers SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    header("Location: suppliers.php");
    exit;
}

// Delete supplier
if (isset($_POST['delete_supplier'])) {
    $id = (int)$_POST['supplier_id'];
    $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: suppliers.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fournisseurs - Gestion de Stock</title>
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

        .suppliers-table, .deliveries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .suppliers-table th, .suppliers-table td,
        .deliveries-table th, .deliveries-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .suppliers-table th, .deliveries-table th {
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

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            z-index: 1000;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .btn {
            padding: 8px 12px;
            cursor: pointer;
            border: none;
            border-radius: 5px;
            margin: 2px;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .chart-container {
                grid-template-columns: 1fr;
            }

            .suppliers-table, .deliveries-table {
                display: block;
                overflow-x: auto;
            }

            .modal {
                width: 90%;
                max-width: 400px;
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
            <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Ventes</a></li>
            <li><a href="suppliers.php" class="active"><i class="fas fa-truck"></i> Fournisseurs</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h1><i class="fas fa-truck"></i> Fournisseurs</h1>
            <button class="btn btn-primary" id="openAddModal"><i class="fas fa-plus"></i> Ajouter un fournisseur</button>
        </header>

        <!-- Stats Cards -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon" style="background: #e3f2fd; color: #1976d2;">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-info">
                    <h3>Total Fournisseurs</h3>
                    <p><?php echo count($suppliers); ?></p>
                    <small>Actifs</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9; color: #2e7d32;">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-info">
                    <h3>Produits Fournis</h3>
                    <p><?php echo array_sum(array_column($supplierStats, 'total_products')); ?></p>
                    <small>Total</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0; color: #f57c00;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3>Stock Faible</h3>
                    <p><?php echo array_sum(array_column($supplierStats, 'low_stock_products')); ?></p>
                    <small>À surveiller</small>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fce4ec; color: #c2185b;">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <div class="stat-info">
                    <h3>Produits Expirés</h3>
                    <p><?php echo array_sum(array_column($supplierStats, 'expired_products')); ?></p>
                    <small>À surveiller</small>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="chart-container">
            <div class="chart-card">
                <h3>Valeur par Fournisseur</h3>
                <canvas id="suppliersChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Distribution des Produits</h3>
                <canvas id="productsChart"></canvas>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="chart-card">
            <h3>Liste des Fournisseurs</h3>
            <table class="suppliers-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Produits</th>
                        <th>Valeur</th>
                        <th>Stock Faible</th>
                        <th>Expirés</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($supplierStats as $stat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($stat['name']); ?></td>
                            <td>
                                <span class="badge <?php echo $stat['total_products'] > 0 ? 'success' : 'warning'; ?>">
                                    <?php echo $stat['total_products']; ?>
                                </span>
                            </td>
                            <td><?php echo number_format($stat['total_value'], 2); ?> $</td>
                            <td>
                                <span class="badge <?php echo $stat['low_stock_products'] > 0 ? 'warning' : 'success'; ?>">
                                    <?php echo $stat['low_stock_products']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $stat['expired_products'] > 0 ? 'danger' : 'success'; ?>">
                                    <?php echo $stat['expired_products']; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary edit-btn" 
                                    data-id="<?php echo $stat['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($stat['name']); ?>">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="btn btn-danger delete-btn" data-id="<?php echo $stat['id']; ?>">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Recent Deliveries -->
        <div class="chart-card">
            <h3>Livraisons Récentes</h3>
            <table class="deliveries-table">
                <thead>
                    <tr>
                        <th>Fournisseur</th>
                        <th>Produits</th>
                        <th>Quantité</th>
                        <th>Valeur</th>
                        <th>Dernière Livraison</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentDeliveries as $delivery): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($delivery['supplier_name']); ?></td>
                            <td><?php echo $delivery['total_products']; ?></td>
                            <td><?php echo $delivery['total_quantity']; ?></td>
                            <td><?php echo number_format($delivery['total_value'], 2); ?> $</td>
                            <td><?php echo $delivery['last_delivery_date'] ? date('d M Y', strtotime($delivery['last_delivery_date'])) : 'Aucune'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modals -->
<div class="overlay"></div>
<div class="modal" id="addSupplierModal">
    <form method="POST">
        <h3>Ajouter un Fournisseur</h3>
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="name" required>
        </div>
        <button type="submit" class="btn btn-primary" name="add_supplier">Ajouter</button>
        <button type="button" class="btn btn-secondary close-modal">Annuler</button>
    </form>
</div>

<div class="modal" id="editSupplierModal">
    <form method="POST">
        <h3>Modifier un Fournisseur</h3>
        <input type="hidden" name="supplier_id" id="edit_supplier_id">
        <div class="form-group">
            <label>Nom</label>
            <input type="text" name="name" id="edit_name" required>
        </div>
        <button type="submit" class="btn btn-primary" name="edit_supplier">Modifier</button>
        <button type="button" class="btn btn-secondary close-modal">Annuler</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Suppliers Chart
    new Chart(document.getElementById("suppliersChart"), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($supplierStats, 'name')); ?>,
            datasets: [{
                label: "Valeur Totale ($)",
                data: <?php echo json_encode(array_column($supplierStats, 'total_value')); ?>,
                backgroundColor: "#1976d2"
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

    // Products Distribution Chart
    new Chart(document.getElementById("productsChart"), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($supplierStats, 'name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($supplierStats, 'total_products')); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});

// Modal functionality
document.getElementById("openAddModal").addEventListener("click", function() {
    document.querySelector(".overlay").style.display = "block";
    document.getElementById("addSupplierModal").style.display = "block";
});

document.querySelectorAll(".delete-btn").forEach(button => {
    button.addEventListener("click", function() {
        if (confirm("Voulez-vous supprimer ce fournisseur ?")) {
            let id = this.getAttribute("data-id");
            fetch("suppliers.php", {
                method: "POST",
                body: new URLSearchParams({delete_supplier: 1, supplier_id: id}),
                headers: {"Content-Type": "application/x-www-form-urlencoded"}
            }).then(() => location.reload());
        }
    });
});

document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        const name = this.getAttribute("data-name");

        document.getElementById("edit_supplier_id").value = id;
        document.getElementById("edit_name").value = name;

        document.querySelector(".overlay").style.display = "block";
        document.getElementById("editSupplierModal").style.display = "block";
    });
});

document.querySelectorAll(".close-modal").forEach(button => {
    button.addEventListener("click", function() {
        document.querySelector(".overlay").style.display = "none";
        document.getElementById("addSupplierModal").style.display = "none";
        document.getElementById("editSupplierModal").style.display = "none";
    });
});

document.querySelector(".overlay").addEventListener("click", function() {
    document.querySelector(".overlay").style.display = "none";
    document.getElementById("addSupplierModal").style.display = "none";
    document.getElementById("editSupplierModal").style.display = "none";
});

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        document.querySelector(".overlay").style.display = "none";
        document.getElementById("addSupplierModal").style.display = "none";
        document.getElementById("editSupplierModal").style.display = "none";
    }
});
</script>
</body>
</html>
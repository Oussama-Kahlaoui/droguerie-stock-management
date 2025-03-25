<?php
require '../config.php'; // Connexion à la base de données

// Récupérer les produits et leur stock
$productsQuery = $pdo->query("
    SELECT name, stock_quantity FROM products ORDER BY stock_quantity DESC
");
$products = $productsQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les ventes mensuelles
$salesQuery = $pdo->query("
    SELECT DATE_FORMAT(sale_date, '%Y-%m') AS month, SUM(total_price) AS total_sales
    FROM sales GROUP BY month ORDER BY month
");
$sales = $salesQuery->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les livraisons par fournisseur
$suppliersQuery = $pdo->query("
    SELECT supplier_name, COUNT(*) AS deliveries FROM suppliers GROUP BY supplier_name
");
$suppliers = $suppliersQuery->fetchAll(PDO::FETCH_ASSOC);

// Retourner les données au format JSON
header('Content-Type: application/json');
echo json_encode([
    'products' => $products,
    'sales' => $sales,
    'suppliers' => $suppliers
]);
?>

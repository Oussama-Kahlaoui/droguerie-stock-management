<?php
include 'db.php';

// Get inventory statistics with more details
$stmt = $pdo->query("
    SELECT 
        COUNT(*) as total_products,
        SUM(quantity) as total_stock,
        SUM(quantity * price) as total_value,
        SUM(CASE WHEN quantity < 10 THEN 1 ELSE 0 END) as low_stock_count,
        SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date > CURDATE() THEN 1 ELSE 0 END) as expiring_soon_count,
        SUM(CASE WHEN expiry_date <= CURDATE() THEN 1 ELSE 0 END) as expired_count,
        AVG(price) as average_price,
        COUNT(DISTINCT category) as total_categories
    FROM products
");
$inventoryStats = $stmt->fetch();

// Get today's sales statistics with more details
$stmt = $pdo->query("
    SELECT 
        COALESCE(SUM(s.quantity * p.price), 0) as total_sales,
        COUNT(*) as total_transactions,
        COUNT(DISTINCT s.product_id) as unique_products_sold,
        AVG(s.quantity * p.price) as average_sale,
        MAX(s.quantity * p.price) as highest_sale,
        MIN(s.quantity * p.price) as lowest_sale
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE DATE(s.sale_date) = CURDATE()
");
$todaySales = $stmt->fetch();

// Get category statistics with more details
$stmt = $pdo->query("
    SELECT 
        category,
        COUNT(*) as total_items,
        SUM(quantity) as total_quantity,
        SUM(quantity * price) as total_value,
        SUM(CASE WHEN quantity < 10 THEN 1 ELSE 0 END) as low_stock_count,
        SUM(CASE WHEN expiry_date <= CURDATE() THEN 1 ELSE 0 END) as expired_count,
        SUM(CASE WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND expiry_date > CURDATE() THEN 1 ELSE 0 END) as expiring_soon_count,
        MIN(CASE WHEN expiry_date > CURDATE() THEN expiry_date END) as next_expiry_date,
        AVG(price) as average_price,
        COUNT(DISTINCT CASE WHEN quantity < 10 THEN id END) as critical_items
    FROM products
    GROUP BY category
    ORDER BY total_value DESC
");
$categoryStats = $stmt->fetchAll();

// Get recent products with more details
$stmt = $pdo->query("
    SELECT 
        name,
        category,
        quantity,
        price,
        expiry_date,
        CASE 
            WHEN expiry_date <= CURDATE() THEN 'Expiré'
            WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expire bientôt'
            WHEN quantity < 10 THEN 'Stock faible'
            ELSE 'Normal'
        END as status,
        CASE 
            WHEN expiry_date <= CURDATE() THEN 'danger'
            WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'warning'
            WHEN quantity < 10 THEN 'warning'
            ELSE 'success'
        END as status_color,
        CASE 
            WHEN quantity = 0 THEN 'Rupture de stock'
            WHEN quantity < 5 THEN 'Stock très faible'
            WHEN quantity < 10 THEN 'Stock faible'
            ELSE 'Stock normal'
        END as stock_status,
        CASE 
            WHEN quantity = 0 THEN 'danger'
            WHEN quantity < 5 THEN 'warning'
            WHEN quantity < 10 THEN 'warning'
            ELSE 'success'
        END as stock_status_color
    FROM products
    ORDER BY id DESC
    LIMIT 5
");
$recentProducts = $stmt->fetchAll();

// Get critical stock items
$stmt = $pdo->query("
    SELECT 
        name,
        category,
        quantity,
        price,
        expiry_date,
        CASE 
            WHEN quantity = 0 THEN 'Rupture de stock'
            WHEN quantity < 5 THEN 'Stock très faible'
            ELSE 'Stock faible'
        END as status,
        CASE 
            WHEN quantity = 0 THEN 'danger'
            WHEN quantity < 5 THEN 'warning'
            ELSE 'warning'
        END as status_color
    FROM products
    WHERE quantity < 10
    ORDER BY quantity ASC
    LIMIT 5
");
$criticalStock = $stmt->fetchAll();

// Get expiring products
$stmt = $pdo->query("
    SELECT 
        name,
        category,
        quantity,
        price,
        expiry_date,
        DATEDIFF(expiry_date, CURDATE()) as days_until_expiry,
        CASE 
            WHEN expiry_date <= CURDATE() THEN 'Expiré'
            WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Expire très bientôt'
            ELSE 'Expire bientôt'
        END as status,
        CASE 
            WHEN expiry_date <= CURDATE() THEN 'danger'
            WHEN expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'warning'
            ELSE 'warning'
        END as status_color
    FROM products
    WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY expiry_date ASC
    LIMIT 5
");
$expiringProducts = $stmt->fetchAll();

// Get sales data for the last 6 months with more details
$stmt = $pdo->query("
    SELECT 
        DATE_FORMAT(s.sale_date, '%M %Y') as month,
        SUM(s.quantity * p.price) as total_sales,
        COUNT(*) as total_transactions,
        COUNT(DISTINCT s.product_id) as unique_products_sold,
        AVG(s.quantity * p.price) as average_sale,
        MAX(s.quantity * p.price) as highest_sale,
        MIN(s.quantity * p.price) as lowest_sale
    FROM sales s
    JOIN products p ON s.product_id = p.id
    WHERE s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(s.sale_date, '%Y-%m')
    ORDER BY s.sale_date ASC
");
$sales = $stmt->fetchAll();

// Get supplier data with more details
$stmt = $pdo->query("
    SELECT 
        s.name,
        COUNT(DISTINCT p.id) as total_products,
        SUM(p.quantity * p.price) as total_value,
        AVG(p.price) as average_price,
        COUNT(DISTINCT CASE WHEN p.quantity < 10 THEN p.id END) as low_stock_products,
        COUNT(DISTINCT CASE WHEN p.expiry_date <= CURDATE() THEN p.id END) as expired_products
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id, s.name
    ORDER BY total_value DESC
    LIMIT 5
");
$suppliers = $stmt->fetchAll();

// Get recent activities with more context
$activities = [];

// Add recent sales with more details
$stmt = $pdo->query("
    SELECT 
        s.sale_date as time,
        CONCAT('Vente de ', s.quantity, ' unités de ', p.name, ' (', (s.quantity * p.price), ' $)') as description,
        'sale' as type,
        s.quantity * p.price as amount
    FROM sales s
    JOIN products p ON s.product_id = p.id
    ORDER BY s.sale_date DESC
    LIMIT 5
");
$activities = array_merge($activities, $stmt->fetchAll());

// Add low stock alerts with more details
$stmt = $pdo->query("
    SELECT 
        NOW() as time,
        CONCAT('Stock faible pour ', name, ' (', quantity, ' restants)') as description,
        'alert' as type,
        quantity
    FROM products
    WHERE quantity < 10
    ORDER BY quantity ASC
    LIMIT 5
");
$activities = array_merge($activities, $stmt->fetchAll());

// Add expired products alerts with more details
$stmt = $pdo->query("
    SELECT 
        NOW() as time,
        CONCAT('Produit expiré: ', name, ' (', DATEDIFF(CURDATE(), expiry_date), ' jours)') as description,
        'alert' as type,
        DATEDIFF(CURDATE(), expiry_date) as days_expired
    FROM products
    WHERE expiry_date <= CURDATE()
    ORDER BY expiry_date ASC
    LIMIT 5
");
$activities = array_merge($activities, $stmt->fetchAll());

// Sort activities by time and limit to 10 most recent
usort($activities, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});
$activities = array_slice($activities, 0, 10);

// Format time for activities
foreach ($activities as &$activity) {
    $time = strtotime($activity['time']);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        $activity['time'] = 'À l\'instant';
    } elseif ($diff < 3600) {
        $activity['time'] = floor($diff / 60) . ' minutes';
    } elseif ($diff < 86400) {
        $activity['time'] = floor($diff / 3600) . ' heures';
    } else {
        $activity['time'] = date('d M Y H:i', $time);
    }
}

// Prepare response with all enhanced data
$response = [
    'inventoryStats' => $inventoryStats,
    'todaySales' => $todaySales,
    'categoryStats' => $categoryStats,
    'recentProducts' => $recentProducts,
    'criticalStock' => $criticalStock,
    'expiringProducts' => $expiringProducts,
    'sales' => $sales,
    'suppliers' => $suppliers,
    'recentActivities' => $activities
];

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 
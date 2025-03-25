<?php
// Check if stock is low
$low_stock_threshold = 10; // Define your low stock threshold
$stmt = $conn->prepare("SELECT name, quantity FROM products WHERE quantity < ?");
$stmt->bind_param("i", $low_stock_threshold);
$stmt->execute();
$result = $stmt->get_result();
$low_stock_products = [];
while ($row = $result->fetch_assoc()) {
    $low_stock_products[] = $row;
}

// Return low stock products along with other data
echo json_encode([
    'products' => $products, 
    'sales' => $sales, 
    'suppliers' => $suppliers,
    'low_stock' => $low_stock_products // Include low stock products
]);
?>
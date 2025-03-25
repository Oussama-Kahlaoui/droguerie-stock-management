<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $supplier_id = $_POST['supplier_id'];

    $stmt = $pdo->prepare("INSERT INTO products (name, price, quantity, supplier_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $quantity, $supplier_id]);

    echo "Product added successfully";
}
?>

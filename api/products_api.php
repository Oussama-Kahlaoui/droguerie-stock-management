<?php
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "delete") {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$_POST['id']])) {
        echo "Product deleted successfully!";
    } else {
        echo "Error deleting product.";
    }
}
?>

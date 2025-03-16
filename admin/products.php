<?php
include '../db.php';
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();
?>

<table id="productsTable">
    <tr>
        <th>Name</th><th>Category</th><th>Quantity</th><th>Price</th><th>Expiry</th><th>Actions</th>
    </tr>
    <?php foreach ($products as $product): ?>
        <tr>
            <td><?= $product['name'] ?></td>
            <td><?= $product['category'] ?></td>
            <td><?= $product['quantity'] ?></td>
            <td><?= $product['price'] ?></td>
            <td><?= $product['expiry_date'] ?></td>
            <td>
                <button class="delete-btn" data-id="<?= $product['id'] ?>">Delete</button>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

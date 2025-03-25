<?php
include '../db.php';

// Récupérer les produits depuis la base de données
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();

// **Ajouter un produit**
if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $expiry_date = $_POST['expiry_date'];

    if (!empty($name) && !empty($category) && $quantity >= 0 && $price >= 0 && !empty($expiry_date)) {
        $stmt = $pdo->prepare("INSERT INTO products (name, category, quantity, price, expiry_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $quantity, $price, $expiry_date]);
    }
    header("Location: products.php");
    exit;
}

// **Modifier un produit**
if (isset($_POST['edit_product'])) {
    $id = (int)$_POST['product_id'];
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];
    $expiry_date = $_POST['expiry_date'];

    $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, quantity=?, price=?, expiry_date=? WHERE id=?");
    $stmt->execute([$name, $category, $quantity, $price, $expiry_date, $id]);

    header("Location: products.php");
    exit;
}

// **Supprimer un produit**
if (isset($_POST['delete_product'])) {
    $id = (int)$_POST['product_id'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produits - Gestion de Stock</title>
    <link rel="stylesheet" href="../assets/index.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
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
            width: 300px;
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
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
<div class="app-container">
    <aside class="sidebar">
        <h2>Gestion de Stock</h2>
        <ul>
            <li><a href="../index.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="products.php" class="active"><i class="fas fa-box"></i> Produits</a></li>
            <li><a href="sales.php"><i class="fas fa-shopping-cart"></i> Ventes</a></li>
            <li><a href="suppliers.php"><i class="fas fa-truck"></i> Fournisseurs</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="dashboard-header">
            <h1><i class="fas fa-box"></i> Produits</h1>
            <button class="btn btn-primary" id="openAddModal"><i class="fas fa-plus"></i> Ajouter un produit</button>
        </header>

        <section class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Quantité</th>
                        <th>Prix</th>
                        <th>Expiration</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category']) ?></td>
                            <td><?= htmlspecialchars($product['quantity']) ?></td>
                            <td><?= htmlspecialchars($product['price']) ?> $</td>
                            <td><?= htmlspecialchars($product['expiry_date']) ?></td>
                            <td>
    <button class="btn btn-primary edit-btn" data-id="<?= $product['id'] ?>"
        data-name="<?= $product['name'] ?>" data-category="<?= $product['category'] ?>"
        data-quantity="<?= $product['quantity'] ?>" data-price="<?= $product['price'] ?>"
        data-expiry="<?= $product['expiry_date'] ?>">
        <i class="fas fa-pen"></i> Modifier
    </button>
    <button class="btn btn-danger delete-btn" data-id="<?= $product['id'] ?>">
        <i class="fas fa-times"></i> Supprimer
    </button>
</td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>

<!-- Modals -->
<div class="overlay"></div>
<div class="modal" id="addProductModal">
    <form method="POST">
        <h3>Ajouter un Produit</h3>
        <input type="text" name="name" placeholder="Nom" required>
        <input type="text" name="category" placeholder="Catégorie" required>
        <input type="number" name="quantity" placeholder="Quantité" required>
        <input type="number" name="price" placeholder="Prix" required>
        <input type="date" name="expiry_date" required>
        <button type="submit" class="btn btn-primary" name="add_product">Ajouter</button>
        <button type="button" class="btn btn-secondary close-modal">Annuler</button>
    </form>
</div>

<div class="modal" id="editProductModal">
    <form method="POST">
        <h3>Modifier un Produit</h3>
        <input type="hidden" name="product_id" id="edit_product_id">
        <input type="text" name="name" id="edit_name" placeholder="Nom" required>
        <input type="text" name="category" id="edit_category" placeholder="Catégorie" required>
        <input type="number" name="quantity" id="edit_quantity" placeholder="Quantité" required>
        <input type="number" name="price" id="edit_price" placeholder="Prix" required>
        <input type="date" name="expiry_date" id="edit_expiry_date" required>
        <button type="submit" class="btn btn-primary" name="edit_product">Modifier</button>
        <button type="button" class="btn btn-secondary close-modal">Annuler</button>
    </form>
</div>

<script>
document.getElementById("openAddModal").addEventListener("click", function() {
    document.querySelector(".overlay").style.display = "block";
    document.getElementById("addProductModal").style.display = "block";
});

document.querySelectorAll(".delete-btn").forEach(button => {
    button.addEventListener("click", function() {
        if (confirm("Voulez-vous supprimer ce produit ?")) {
            let id = this.getAttribute("data-id");
            fetch("products.php", {
                method: "POST",
                body: new URLSearchParams({delete_product: 1, product_id: id}),
                headers: {"Content-Type": "application/x-www-form-urlencoded"}
            }).then(() => location.reload());
        }
    });
});

// Edit button functionality
document.querySelectorAll(".edit-btn").forEach(button => {
    button.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        const name = this.getAttribute("data-name");
        const category = this.getAttribute("data-category");
        const quantity = this.getAttribute("data-quantity");
        const price = this.getAttribute("data-price");
        const expiry = this.getAttribute("data-expiry");

        // Populate the edit form
        document.getElementById("edit_product_id").value = id;
        document.getElementById("edit_name").value = name;
        document.getElementById("edit_category").value = category;
        document.getElementById("edit_quantity").value = quantity;
        document.getElementById("edit_price").value = price;
        document.getElementById("edit_expiry_date").value = expiry;

        // Show the edit modal
        document.querySelector(".overlay").style.display = "block";
        document.getElementById("editProductModal").style.display = "block";
    });
});

// Close modal functionality for both modals
document.querySelectorAll(".close-modal").forEach(button => {
    button.addEventListener("click", function() {
        document.querySelector(".overlay").style.display = "none";
        document.getElementById("addProductModal").style.display = "none";
        document.getElementById("editProductModal").style.display = "none";
    });
});

// Close modal when clicking overlay
document.querySelector(".overlay").addEventListener("click", function() {
    document.querySelector(".overlay").style.display = "none";
    document.getElementById("addProductModal").style.display = "none";
    document.getElementById("editProductModal").style.display = "none";
});

// Add keyboard support for closing modals
document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
        document.querySelector(".overlay").style.display = "none";
        document.getElementById("addProductModal").style.display = "none";
        document.getElementById("editProductModal").style.display = "none";
    }
});

// Add form validation
document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", function(e) {
        const quantity = this.querySelector('input[name="quantity"]').value;
        const price = this.querySelector('input[name="price"]').value;
        
        if (quantity < 0) {
            e.preventDefault();
            alert("La quantité ne peut pas être négative");
            return;
        }
        
        if (price < 0) {
            e.preventDefault();
            alert("Le prix ne peut pas être négatif");
            return;
        }
    });
});
</script>
</body>
</html>
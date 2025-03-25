<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("INSERT INTO suppliers (name, contact_info) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $contact);

    if ($stmt->execute()) {
        echo "Supplier added!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

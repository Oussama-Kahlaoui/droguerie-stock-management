document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", function () {
            let productId = this.getAttribute("data-id");

            if (confirm("Are you sure you want to delete this product?")) {
                fetch("../api/products_api.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: "action=delete&id=" + productId
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
            }
        });
    });
});
$(document).ready(function() {
    $.getJSON("api/products_api.php", function(data) {
        // Products Chart (Bar)
        new Chart(document.getElementById('productsChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(data.products),
                datasets: [{
                    label: 'Quantité en Stock',
                    data: Object.values(data.products),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
            options: { responsive: true }
        });

        // Sales Chart (Line)
        new Chart(document.getElementById('salesChart'), {
            type: 'line',
            data: {
                labels: Object.keys(data.sales),
                datasets: [{
                    label: 'Ventes ($)',
                    data: Object.values(data.sales),
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true
                }]
            },
            options: { responsive: true }
        });

        // Suppliers Chart (Pie)
        new Chart(document.getElementById('suppliersChart'), {
            type: 'pie',
            data: {
                labels: Object.keys(data.suppliers),
                datasets: [{
                    data: Object.values(data.suppliers),
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56']
                }]
            },
            options: { responsive: true }
        });
    });
});

<?php
// Inclure la connexion à la base de données
include('db.php');
session_start();

// Vérifier si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vérifier que les champs ne sont pas vides
    if (!empty($email) && !empty($password)) {
        try {
            // Récupérer l'utilisateur depuis la base de données
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Vérifier le mot de passe hashé
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user'] = $user;
                    header("Location: index.php"); // Redirection vers l'accueil
                    exit;
                } else {
                    $error_message = "Email ou mot de passe incorrect.";
                }
            } else {
                $error_message = "Email ou mot de passe incorrect.";
            }
        } catch (PDOException $e) {
            $error_message = "Erreur SQL : " . $e->getMessage();
        }
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>

<div class="flex">
    <div class="container">
        <div class="user-logo"></div>
        <form method="POST">
            <h1>Connexion</h1>

            <div class="inputs">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="inputs">
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <div class="forget-pass">
                <a href="forgot_password.php">Mot de passe oublié ?</a>
            </div>

            <div class="box">
                <button type="submit">Se connecter</button>
            </div>

            <hr>

            <div class="signup">
                <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
            </div>

            <!-- Afficher le message d'erreur si la connexion échoue -->
            <?php
            if (isset($error_message)) {
                echo "<p style='color: red; text-align:center;'>$error_message</p>";
            }
            ?>
        </form>
    </div>
</div>

</body>
</html>

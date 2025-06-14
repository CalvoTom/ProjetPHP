<?php
session_start();
require_once 'includes/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = htmlspecialchars($_POST["email"]);
    $mot_de_passe = $_POST["mot_de_passe"];

    if (!empty($email) && !empty($mot_de_passe)) {
        $sql = "SELECT * FROM utilisateurs WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($utilisateur && password_verify($mot_de_passe, $utilisateur['mot_de_passe'])) {
            $_SESSION["user_id"] = $utilisateur["id"];
            $_SESSION["email"] = $utilisateur["email"];
            $_SESSION["nom"] = $utilisateur["nom"];
            $_SESSION["prenom"] = $utilisateur["prenom"];
            $_SESSION["creation"] = $utilisateur["date_creation"];
            $_SESSION["role"] = $utilisateur["role"];

            header("Location: index.php");
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketPlace</title>
    <link rel="icon" type="image/png" href="/img/favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
         <!-- Navigation -->
        <nav class="navbar">
            <a href="index.php" class="logo">MarketPlace</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="articles.php" class="nav-link">ARTICLES</a>
                <a href="panier.php" class="nav-link">PANIER</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
                    <a href="admin.php" class="nav-link">DASHBOARD</a>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="articleLike.php" class="nav-link nav-heart">❤️</a>
                <?php endif; ?>
            </div>

            <div class="nav-buttons">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn-secondary">Mon Profil</a>
                    <a href="vente.php" class="btn-primary">Vends tes articles !</a>
                <?php else: ?>
                    <a href="register.php" class="btn-secondary">S'inscrire</a>
                    <a href="login.php" class="btn-primary">Se connecter</a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Login Section -->
        <section class="login-section fade-in">
            <div class="login-illustration">
                <img  class="img-cta" src="/img/cta.png" alt="image cta">
            </div>
            
            <div class="login-form-container">
                <a href="index.php" class="back-link">back to website</a>
                
                <h1 class="welcome-title">Welcome</h1>
                
                <?php if ($error): ?>
                    <div class="message error-message shake">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form id="loginForm" class="login-form" method="post" action="">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="Entrez votre email"
                            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="mot_de_passe">Password</label>
                        <input 
                            type="password" 
                            id="mot_de_passe" 
                            name="mot_de_passe" 
                            class="form-input" 
                            placeholder="Entrez votre mot de passe"
                            required
                        >
                    </div>
                    
                    <a href="#" class="forgot-password-link" onclick="forgotPassword()">Forgot password ?</a>
                    
                    <button type="submit" class="login-button">Login</button>
                </form>

                <a href="register.php" class="create-account-link">Create a free account</a>
            </div>
        </section>
    </div>
    <!-- Footer -->
    <footer class="footer">
        <h2 class="footer-title">MARKETPLACE</h2>
    </footer>
</body>
<script>
    // Form validation
    function validateForm(form) {
        const email = form.querySelector('input[name="email"]').value.trim();
        const motDePasse = form.querySelector('input[name="mot_de_passe"]').value;

        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showClientError('Veuillez entrer une adresse email valide');
            return false;
        }

        // Password validation
        if (motDePasse.length === 0) {
            showClientError('Le mot de passe est obligatoire');
            return false;
        }

        return true;
    }

    // Client-side error display
    function showClientError(message) {
        // Remove existing error messages
        const existingError = document.querySelector('.client-error-message');
        if (existingError) {
            existingError.remove();
        }

        // Create error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'message error-message client-error-message shake';
        errorDiv.textContent = message;

        // Insert error message
        const form = document.querySelector('.login-form');
        form.insertBefore(errorDiv, form.firstChild);

        // Remove error after 5 seconds
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }

    // Form submission handler
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        if (!validateForm(this)) {
            e.preventDefault();
            return false;
        }

        // Show loading state
        const button = this.querySelector('.login-button');
        const originalText = button.textContent;
        button.textContent = 'Connexion...';
        button.disabled = true;
    });

    // Input focus effects
    document.querySelectorAll('.form-input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentNode.style.transform = 'translateY(-2px)';
            this.parentNode.style.transition = 'transform 0.2s ease';
        });

        input.addEventListener('blur', function() {
            this.parentNode.style.transform = 'translateY(0)';
        });

        // Real-time validation feedback
        input.addEventListener('input', function() {
            this.style.borderColor = '#F8582E';
            
            // Remove client error messages when user starts typing
            const clientError = document.querySelector('.client-error-message');
            if (clientError) {
                clientError.remove();
            }
        });
    });

    // Auto-hide server messages after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const messages = document.querySelectorAll('.message:not(.client-error-message)');
        messages.forEach(message => {
            setTimeout(() => {
                if (message.parentNode) {
                    message.style.opacity = '0';
                    message.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        message.remove();
                    }, 300);
                }
            }, 5000);
        });
    });

    // Auto-focus on email field
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('email').focus();
    });
</script>
</html>

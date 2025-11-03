<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        echo isset($title) && $title != null ? $title : "Titre inconnu";
        ?>
    </title>
    <?php
        use Sae\Models\Repository\UserRepository;
        if(isset($styles))
            foreach($styles as $style)
                echo $style;

        $repository = new UserRepository();
        $user = ($repository)->selectCurrent();
        $photoSrc = $repository->profilePictureUrl($user);
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<!-- Navigation -->
<nav class="navbar">
    <!-- Bouton menu hamburger -->
    <div class="menu-toggle" aria-label="Menu de navigation" role="button" tabindex="0">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Logo -->
    <div class="logo-container">
        <a href="/index/welcome" aria-label="Accueil">
            <img src="/public/assets/images/logo.png" alt="Logo - Aether">
        </a>
    </div>

    <!-- Navigation principale -->
    <ul class="nav-links" role="navigation">
        <li><a href="/index/welcome" <?php echo ($_SERVER['REQUEST_URI'] === '/index/welcome') ? 'class="active"' : ''; ?>>Accueil</a></li>
        <li><a href="/index/map" <?php echo ($_SERVER['REQUEST_URI'] === '/index/map') ? 'class="active"' : ''; ?>>Carte</a></li>
        <li><a href="/graph/select" <?php echo ($_SERVER['REQUEST_URI'] === '/graph/select') ? 'class="active"' : ''; ?>>Comparer</a></li>
        <li><a href="/library/list" <?php echo ($_SERVER['REQUEST_URI'] === '/library/list') ? 'class="active"' : ''; ?>>Météothèques</a></li>
        <li><a href="/game" <?php echo ($_SERVER['REQUEST_URI'] === '/game') ? 'class="active"' : ''; ?>>Higher or Lower</a></li>
        <li><a href="/index/credits" <?php echo ($_SERVER['REQUEST_URI'] === '/index/credits') ? 'class="active"' : ''; ?>>À propos de nous</a></li>

        <!-- Déplacer user-links ici -->
        <div class="user-links">
            <a href="<?= $user != null ? '/profile/' . $user->getId() : '/auth/login' ?>" aria-label="Mon compte" title="Mon compte">
                <img src="<?= $photoSrc ?>" class="profile-picture" alt="Photo de profil">
            </a>
            <a href="/auth/settings" aria-label="Paramètres" title="Paramètres">
                <i class="fa fa-cog" aria-hidden="true"></i>
            </a>
        </div>
    </ul>

</nav>

<!-- Messages Flash -->
<div class="flash-messages" role="alert">
    <?php
    if(isset($autoFlashMessages) && $autoFlashMessages && isset($flashMessages)) {
        foreach($flashMessages as $flashMessage) {
            $flashMessage->draw();
        }
    }
    ?>
</div>

<!-- Contenu principal -->
<main class="content" role="main">
    <?php
    if(isset($body)) {
        if(file_exists($body)) {
            require $body;
        } else {
            echo '<div class="alert alert-error">Vue introuvable</div>';
        }
    } else {
        echo '<div class="alert alert-error">Page introuvable</div>';
    }
    ?>
</main>

<!-- Footer -->
<footer class="footer" role="contentinfo">
    <h2>Suivez-nous pour plus de mises à jour météo !</h2>
    <div class="social-icons">
        <a href="#" aria-label="Suivez-nous sur LinkedIn" class="social-icon">
            <i class="fab fa-linkedin" aria-hidden="true"></i>
        </a>
        <a href="#" aria-label="Suivez-nous sur Instagram" class="social-icon">
            <i class="fab fa-instagram" aria-hidden="true"></i>
        </a>
        <a href="#" aria-label="Rejoignez-nous sur Discord" class="social-icon">
            <i class="fab fa-discord" aria-hidden="true"></i>
        </a>
        <a href="#" aria-label="Contactez-nous par email" class="social-icon">
            <i class="fas fa-envelope" aria-hidden="true"></i>
        </a>
    </div>
    <p>©<?php echo date('Y'); ?> Aether - Tous droits réservés</p>
</footer>

<!-- Scripts -->
<script>
    // Gestion du menu responsive
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');
        const navbar = document.querySelector('.navbar');

        // Toggle menu mobile
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            menuToggle.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });

        // Fermer le menu au clic sur un lien
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                menuToggle.classList.remove('active');
                document.body.classList.remove('menu-open');
            });
        });

        // Support clavier pour le menu hamburger
        menuToggle.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
</script>
</body>
</html>
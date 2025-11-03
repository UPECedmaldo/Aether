<div class="login-container">

    <h2>Connexion</h2>

    <?php
        if(isset($flashMessages))
            foreach($flashMessages as $flashMessage)
                $flashMessage->draw();
    ?>

    <form action="/auth/logged" method="POST">
        <label>
            <input type="text" name="input" placeholder="Adresse mail ou Nom d'utilisateur" required>
        </label>
        <label>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </label>
        <button type="submit">Se connecter</button>
    </form>

    <a href="/auth/register">Pas encore de compte ? Inscrivez-vous</a>

</div>
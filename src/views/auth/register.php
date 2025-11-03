<div class="login-container">

    <h2>Inscription</h2>

    <?php
        if(isset($flashMessages))
            foreach($flashMessages as $flashMessage)
                $flashMessage->draw();
    ?>

    <form action="/auth/registered" method="POST">
        <label>
            <input type="text" name="pseudo" placeholder="Pseudo" required>
        </label>
        <label>
            <input type="text" name="nom" placeholder="Nom" required>
        </label>
        <label>
            <input type="text" name="prenom" placeholder="Prénom" required>
        </label>
        <label>
            <input type="email" name="email" placeholder="Email" required>
        </label>
        <label>
            <input type="password" name="password" placeholder="Mot de passe" required>
        </label>
        <button type="submit">S'inscrire</button>
    </form>

    <a href="/auth/login">Déjà inscrit ? Connectez-vous</a>

</div>
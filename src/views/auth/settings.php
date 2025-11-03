<div class="flash-messages">
    <?php
    if(isset($flashMessages))
        foreach($flashMessages as $flashMessage)
            $flashMessage->draw();
    ?>
</div>

<div class="settings-container">

    <div class="settings-sidebar">
        <h2>Paramètres</h2>
        <ul>
            <li><a href="#account"><i class="fas fa-user"></i> Compte</a></li>
            <li><a href="#security"><i class="fas fa-lock"></i> Sécurité</a></li>
        </ul>
    </div>

    <div class="settings-columns">
        <div class="settings-content">
            <section id="account">
                <h2>Compte</h2>
                <form method="POST" action="/auth/update">
                    <div class="setting">
                        <label for="pseudo">Modifier le pseudonyme</label>
                        <input type="text" id="pseudo" name="pseudo" placeholder="Nouveau pseudonyme">
                        <button type="submit">Confirmer</button>
                    </div>
                    <input type="hidden" name="type" value="pseudo">
                </form>
                <form method="POST" action="/auth/update">
                    <div class="setting">
                        <label for="email">Modifier l'adresse e-mail</label>
                        <input type="email" id="email" name="email" placeholder="Nouvelle adresse e-mail">
                        <button type="submit">Confirmer</button>
                    </div>
                    <input type="hidden" name="type" value="email">
                </form>
                <form method="POST" action="/auth/update">
                    <div class="setting">
                        <label for="password">Modifier le mot de passe</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Mot de passe actuel">
                        <input type="password" id="password" name="password" placeholder="Nouveau mot de passe">
                        <input type="hidden" name="type" value="password">
                        <button type="submit">Changer</button>
                    </div>
                    <input type="hidden" name="field" value="password">
                </form>
                <form method="POST" action="/auth/update" enctype="multipart/form-data">
                    <div class="setting">
                        <label for="photo">Modifier la photo de profil</label>
                        <input type="file" id="photo" name="photo" accept="image/*">
                        <input type="hidden" name="type" value="photo">
                        <button type="submit">Mettre à jour</button>
                    </div>
                </form>
            </section>
        </div>
        <div class="settings-content">
            <section id="security">
                <h2>Sécurité</h2>
                <form method="POST" action="/auth/delete">
                    <div class="setting">
                        <label>Supprimer le compte</label>
                        <button type="submit" class="delete-btn">Supprimer le compte</button>
                    </div>
                </form>
                <form method="POST" action="/auth/logout">
                    <div class="setting">
                        <label>Se déconnecter</label>
                        <button type="submit" class="logout-btn">Se déconnecter</button>
                    </div>
                </form>

                <!-- Nouvelle section Historique -->
                <div class="setting">
                    <label for="history">Historique</label>
                    <div id="history" class="history-scrollable">
                        <?php
                            if (!empty($historics)) {
                                foreach ($historics as $log) {
                                    echo '<p>' . htmlspecialchars($log->getReferenceName()) . " le " . $log->getFormattedDate() . '</p>';
                                }
                            } else {
                                echo '<p>Aucun historique disponible.</p>';
                            }
                        ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
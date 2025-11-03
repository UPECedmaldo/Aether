<div class="meteotheque-page">
    <div class="meteotheque-create-container">
        <h2>Modifier</h2>

        <?php
            if(isset($flashMessages))
                foreach($flashMessages as $flashMessage)
                    $flashMessage->draw();
        ?>

        <form method="POST" action="/library/updated" class="meteotheque-form">
            <div class="form-group">
                <input
                    type="text"
                    name="name"
                    value="<?= $library->getName() ?>"
                    placeholder="Nom de la météothèque"
                    required
                >
            </div>

            <div class="form-group radio-group">
                <label class="radio-label">
                    <input
                        type="radio"
                        name="state"
                        value="public"
                        <?= ($library->isPublic() ? "checked" : "") ?>
                    >
                    Publique
                </label>
                <label class="radio-label">
                    <input
                        type="radio"
                        name="state"
                        value="private"
                        <?= ($library->isPrivate() ? "checked" : "") ?>
                    >
                    Privée
                </label>
                <label class="radio-label">
                    <input
                        type="radio"
                        name="state"
                        value="friends"
                        <?= ($library->isShared() ? "checked" : "") ?>
                    >
                    Amis
                </label>
            </div>

            <div class="form-group color-picker-group">
                <label>Choisir une couleur</label>
                <div class="color-input-wrapper">
                    <input
                        type="color"
                        name="color"
                        value="#<?= $library->getColor() ?>"
                        class="color-picker"
                    >
                </div>
            </div>

            <input type="hidden" name="library" value="<?= $library->getId() ?>">

            <button type="submit">Mettre à jour</button>
            <button formaction="/library/delete" type="submit">Supprimer</button>
        </form>
    </div>
</div>
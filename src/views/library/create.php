<div class="meteotheque-page">
    <div class="meteotheque-create-container">
        <h2>Créer une météothèque</h2>

        <?php
            if(isset($flashMessages))
                foreach($flashMessages as $flashMessage)
                    $flashMessage->draw();
        ?>

        <form method="POST" action="/library/created" class="meteotheque-form">
            <div class="form-group">
                <input
                    type="text"
                    name="name"
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
                        checked
                    >
                    Publique
                </label>
                <label class="radio-label">
                    <input
                        type="radio"
                        name="state"
                        value="private"
                    >
                    Privée
                </label>
                <label class="radio-label">
                    <input
                        type="radio"
                        name="state"
                        value="friends"
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
                        value="#26ABE1"
                        class="color-picker"
                    >
                </div>

                <div class="preset-colors">
                    <label>Couleurs suggérées :</label>
                    <div class="color-buttons">
                        <button type="button" class="preset-color" data-color="#26ABE1" style="background-color: #26ABE1"></button>
                        <button type="button" class="preset-color" data-color="#0033aa" style="background-color: #0033aa"></button>
                        <button type="button" class="preset-color" data-color="#00aaff" style="background-color: #00aaff"></button>
                    </div>
                </div>
            </div>

            <button type="submit">Créer la météothèque</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorPicker = document.querySelector('.color-picker');
        const presetButtons = document.querySelectorAll('.preset-color');

        presetButtons.forEach(button => {
            button.addEventListener('click', function() {
                colorPicker.value = this.dataset.color;
            });
        });
    });
</script>
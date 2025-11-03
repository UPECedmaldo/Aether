<!-- Contenu principal -->
<div class="climate-app">
    <!-- Section de la carte -->
    <main class="climate-map-container">
        <h2>Carte Climatique</h2>
        <div id="carte" class="climate-map">
            <div>
                <?php
                    $franceMap->draw(true);
                ?>
            </div>
        </div>
    </main>

    <!-- Filtre latéral -->
    <aside class="climate-filter">
        <h2>Filtres</h2>
        <form method="GET" action="">
            <div class="filter-group">
                <label for="season">Saison</label>
                <select id="season" name="season">
                    <option value="">Choisir une saison</option>
                    <option value="spring">Printemps (21 mars - 20 juin)</option>
                    <option value="summer">Été (21 juin - 22 septembre)</option>
                    <option value="autumn">Automne (23 septembre - 20 décembre)</option>
                    <option value="winter">Hiver (21 décembre - 20 mars)</option>
                </select>
            </div>

            <div class="filter-group">
                <label for="year">Année</label>
                <select id="year" name="year">
                    <?php
                        $currentYear = (int)date('Y');
                        for ($year = $currentYear; $year >= 2010; $year--) {
                            echo "<option value=\"$year\">$year</option>";
                        }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="from">Période du</label>
                <input type="date" id="from" name="from" value="<?= $from ?>">
            </div>

            <div class="filter-group">
                <label for="to">au</label>
                <input type="date" id="to" name="to" value="<?= $to ?>">
            </div>

            <div class="filter-group switch-container">
                <label for="compare2010" class="switch-label">
                    Comparer avec 2010
                    <div class="switch">
                        <input type="checkbox" id="compare2010" name="compare2010" value="1">
                        <span class="slider round"></span>
                    </div>
                </label>
            </div>

            <div class="button-group">
                <button type="submit" class="apply-filter">Appliquer</button>
                <button type="reset" class="reset-filter">Réinitialiser</button>
            </div>
        </form>
    </aside>
</div>

<style>
    /* Style général pour l'application */
    .climate-app {
        display: flex;
        width: 100%;
        height: calc(100vh - 85px);
        margin-top: 85px;
        overflow: hidden;
        font-family: "Montserrat", sans-serif;
        background-color: #eef2f7;
    }

    /* Section de la carte climatique */
    .climate-map-container {
        width: 75%;
        padding: 2rem;
        background-color: #ffffff;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
    }

    .climate-map-container h2 {
        margin-bottom: 1.5rem;
        color: #0033aa;
        border-bottom: 2px solid #0033aa;
        padding-bottom: 0.5rem;
        font-size: 1.8rem;
    }

    /* Carte climatique */
    .climate-map {
        flex: 1;
        background-color: #f5f5f5;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        min-height: 500px;
    }

    /* Section des filtres */
    .climate-filter {
        width: 25%;
        padding: 2rem;
        background: linear-gradient(0deg, #0033aa, #00aaff);
        color: white;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .climate-filter h2 {
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #ffffff;
        padding-bottom: 0.5rem;
        text-align: center;
        font-size: 1.8rem;
    }

    /* Groupes de filtres */
    .filter-group {
        margin-bottom: 1.5rem;
    }

    .filter-group label {
        display: block;
        font-size: 1rem;
        color: white;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }

    .filter-group select,
    .filter-group input {
        width: 100%;
        padding: 0.8rem;
        font-size: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s ease;
    }

    .filter-group select:focus,
    .filter-group input:focus {
        outline: none;
        border-color: white;
        background: rgba(255, 255, 255, 0.2);
        box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
    }

    /* Boutons de filtre */
    .button-group {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    .button-group button {
        flex: 1;
        background-image: linear-gradient(to right, transparent 50%, white 50%);
        background-size: 200% 100%;
        background-position: left bottom;
        color: white;
        border: 2px solid white;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 25px;
        cursor: pointer;
        transition: background 0.5s, color 0.5s;
        background-color: transparent;
    }

    .button-group button:hover {
        background-position: right bottom;
        color: #0033aa;
    }

    /* Responsive design */
    @media screen and (max-width: 1024px) {
        .climate-app {
            flex-direction: column;
            height: auto;
        }

        .climate-map-container,
        .climate-filter {
            width: 100%;
        }

        .climate-map {
            min-height: 400px;
        }
    }

    @media screen and (max-width: 768px) {
        .climate-map-container,
        .climate-filter {
            padding: 1rem;
        }

        .climate-map {
            min-height: 300px;
        }

        .button-group {
            flex-direction: column;
        }
    }

    /* Style pour l'interrupteur (switch) */
    .switch-container {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .switch-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        cursor: pointer;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.2);
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: white;
    }

    input:checked + .slider:before {
        background-color: #0033aa;
        transform: translateX(26px);
    }

    .slider.round {
        border-radius: 24px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Références aux éléments du formulaire
        const seasonSelect = document.getElementById('season');
        const yearSelect = document.getElementById('year');
        const fromInput = document.getElementById('from');
        const toInput = document.getElementById('to');

        // Fonction pour mettre à jour les dates en fonction de la saison et l'année
        function updateDateRanges() {
            const selectedYear = yearSelect.value;
            const season = seasonSelect.value;

            if (!selectedYear || !season) return;

            let fromDate, toDate;

            switch(season) {
                case 'spring':
                    fromDate = `${selectedYear}-03-21`;
                    toDate = `${selectedYear}-06-20`;
                    break;
                case 'summer':
                    fromDate = `${selectedYear}-06-21`;
                    toDate = `${selectedYear}-09-22`;
                    break;
                case 'autumn':
                    fromDate = `${selectedYear}-09-23`;
                    toDate = `${selectedYear}-12-20`;
                    break;
                case 'winter':
                    // L'hiver chevauche deux années
                    if (new Date().getFullYear() == selectedYear && new Date().getMonth() < 2) {
                        // Si nous sommes dans l'année en cours et avant mars
                        fromDate = `${parseInt(selectedYear)-1}-12-21`;
                        toDate = `${selectedYear}-03-20`;
                    } else {
                        fromDate = `${selectedYear}-12-21`;
                        toDate = `${parseInt(selectedYear)+1}-03-20`;
                    }
                    break;
            }

            fromInput.value = fromDate;
            toInput.value = toDate;
        }

        // Événements pour mettre à jour les dates quand saison ou année change
        seasonSelect.addEventListener('change', updateDateRanges);
        yearSelect.addEventListener('change', updateDateRanges);

        // Pour la réinitialisation du formulaire
        const compareSwitch = document.getElementById('compare2010');

        // Dans la fonction de réinitialisation, ajoutez :
        document.querySelector('.reset-filter').addEventListener('click', function() {
            setTimeout(() => {
                seasonSelect.selectedIndex = 0;
                yearSelect.selectedIndex = 0;
                fromInput.value = new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0];
                toInput.value = new Date().toISOString().split('T')[0];
                compareSwitch.checked = false; // Réinitialise l'interrupteur
            }, 10);
        });
    });
</script>
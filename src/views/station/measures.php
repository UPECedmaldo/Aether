<div class="station-app">
    <div class="station-info">
        <h2>Informations de la station</h2>
        <p><strong>ID:</strong> <?= htmlspecialchars($station->getId()) ?></p>
        <p><strong>Nom:</strong> <?= htmlspecialchars($station->getName()) ?></p>
        <p><strong>Code:</strong> <?= htmlspecialchars($station->getCode()) ?></p>
        <p><strong>Commune:</strong> <?= htmlspecialchars($station->getCityName()) ?></p>
        <p><strong>Département:</strong> <?= htmlspecialchars($station->getDepartmentName()) ?></p>
        <p><strong>Région:</strong> <?= htmlspecialchars($station->getRegionName()) ?></p>
        <p><strong>EPCI:</strong> <?= htmlspecialchars($station->getEpciName()) ?></p>
        <p><strong>Activité(s):</strong></p>
        <p><strong>Première: </strong><?= $first ?></p>
        <p><strong>Dernière: </strong><?= $last ?></p>
        <button class="like-btn"
                onclick='addData("station", <?= json_encode($station) ?>)'
            <?php echo (!$logged ? 'disabled style="cursor: not-allowed;"' : '') ?>
        >
            Ajouter
        </button>
    </div>
    <div class="station-measures">
        <h2>Filtrer les mesures</h2>
        <form method="GET" action="">
            <div>
                <label for="sort">Tri</label>
                <select id="sort" name="sort">
                    <option value="date_asc" <?php echo ($sort == "date_asc" ? "selected" : "") ?>>Date croissante</option>
                    <option value="date_desc" <?php echo ($sort == "date_desc" ? "selected" : "") ?>>Date décroissante</option>
                    <option value="value_asc"> <?php echo ($sort == "value_asc" ? "selected" : "") ?>Valeur croissante</option>
                    <option value="value_desc" <?php echo ($sort == "value_desc" ? "selected" : "") ?>>Valeur décroissante</option>
                </select>
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="">Toutes</option>
                    <?php
                        foreach ($measureTypes as $type)
                            echo '<option value="' . $type->getCode() . '"' . ($selectedType == $type ? ' selected' : '') . '>' . htmlspecialchars($type->getName()) . '</option>';
                    ?>
                </select>
            </div>
            <div>
                <label for="from">Depuis</label>
                <input type="date" id="from" name="from" value="<?= $from ?>" required
                    <?php if(isset($min) && $min != null) echo 'min="' . $min . '"' ?>
                    <?php if(isset($max) && $max != null) echo 'max="' . $max . '"' ?>
                >
            </div>
            <div>
                <label for="to">Jusqu'à</label>
                <input type="date" id="to" name="to" value="<?= $to ?>" required
                    <?= (isset($min) && $min != null ? 'min="' . $min . '"' : '') ?>
                    <?= (isset($max) && $max != null ? 'max="' . $max . '"' : '') ?>
                >
            </div>
            <input type="hidden" name="station" value="<?= $station->getId() ?>">
            <div>
                <button type="submit">Filtrer</button>
                <?php if($logged && !empty($libraries)): ?>

                    <label for="library">Météothèque</label>
                    <select id="library" name="library">
                        <?php
                            foreach ($libraries as $library)
                                echo '<option value="' . $library->getId() . '">' . htmlspecialchars($library->getName()) . '</option>';
                        ?>
                    </select>
                    <button formaction="export" type="submit">Exporter vers</button>
                <?php endif; ?>
            </div>
        </form>

        <h2>Mesures <?= count($measures) ?></h2>
        <table class="measures-table">
            <thead>
            <tr>
                <th>Type</th>
                <th>Valeur</th>
                <th>Unité</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($measures as $measure): ?>
                <tr id="measure-<?= $measure->getId() ?>">
                    <td><?= htmlspecialchars($measure->getType()->getName()) ?></td>
                    <td><?= htmlspecialchars($measure->getRoundedValue()) ?></td>
                    <td><?= htmlspecialchars($measure->getType()->getUnit()) ?></td>
                    <td><?= htmlspecialchars($measure->getFormattedDate()) ?></td>
                    <td>
                        <button class="like-btn fa-regular fa-heart" data-id="<?= htmlspecialchars($measure->getId()) ?>"
                            <?php echo (!$logged ? 'disabled style="cursor: not-allowed;"' : '') ?>
                            onclick='addData("measure", <?= json_encode($measure) ?>)'
                        >
                            Ajouter
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="meteotheque-popup" style="display: none" class="popup">
    <div class="popup-content">
        <span class="close-btn">&times;</span>
        <h2>Liste des Météothèques</h2>
        <ul id="meteotheque-list">
            <?php
            foreach ($libraries as $library)
                echo '<li>'
                    . '<h3>' . htmlspecialchars($library->getName()) . '</h3>'
                    . '<button type="submit"
                        onclick="sendDataRequest(' . $library->getId() . ')"
                        >
                        Ajouter
                        </button>'
                    . '</li>';
            ?>
            <a href="/library/create"><button>Créer une météothèque</button></a>
        </ul>
    </div>
</div>

<script>

    const likedStations = new Map(Object.entries(<?= json_encode($likedStations) ?>));
    const likedMeasures = new Map(Object.entries(<?= json_encode($likedMeasures) ?>));
    <?php
        $librariesJs = [];
        foreach ($libraries as $library)
            $librariesJs[] = [
                    "id" => $library->getId(),
                    "name" => $library->getName()
            ];
        echo "const libraries = new Map(Object.entries(" . json_encode($librariesJs) . "));";
    ?>

    let selectedMeasure = null;
    let selectedStation = null;

    function sendDataRequest(library) {
        if(selectedMeasure != null)
            sendMeasureRequest(library);
        else if(selectedStation != null)
            sendStationRequest(library);
    }

    function sendMeasureRequest(libraryId) {

        if(selectedMeasure == null)
            return;

        // Send request

        const data = new FormData();
        data.append('library', libraryId);
        data.append('add_type', "measure");
        data.append('measure_id', selectedMeasure.id);
        data.append('station_code', selectedMeasure.station);
        data.append('type', selectedMeasure.type);
        data.append('value', selectedMeasure.value);
        data.append('date', selectedMeasure.date);

        performRequest(data);

    }

    function sendStationRequest(libraryId) {

        if(selectedStation == null)
            return;

        // Send request

        const data = new FormData();
        data.append('library', libraryId);
        data.append('add_type', "station");
        data.append('station_code', String(selectedStation.code));

        performRequest(data);
    }

    function performRequest(formData) {

        fetch('/library/add', {
            method: 'POST',
            body: formData
        }).then(response => {
            console.log(response.text());
            window.location.reload();
        }).catch(error => {
            console.error(error);
        });

    }

    document.addEventListener('DOMContentLoaded', function() {
        const popup = document.getElementById('meteotheque-popup');
        const closeBtn = document.querySelector('.close-btn');

        // Gérer la fermeture de la popup
        closeBtn.addEventListener('click', () => {
            popup.style.display = 'none';
            popup.style.opacity = '0';
            popup.style.visibility = 'hidden';
            document.body.style.overflow = 'auto';
        });

        // Fermer la popup en cliquant en dehors
        window.addEventListener('click', (e) => {
            if (e.target === popup) {
                popup.style.display = 'none';
                popup.style.opacity = '0';
                popup.style.visibility = 'hidden';
                document.body.style.overflow = 'auto';
            }
        });
    });

    function addData(type, data) {
        switch (type) {
            case 'measure':
                selectedMeasure = data;
                selectedStation = null;
                break;
            case 'station':
                selectedStation = data;
                selectedMeasure = null;
                break;
        }

        // Modification ici
        const popup = document.getElementById('meteotheque-popup');
        popup.style.display = 'flex';
        popup.style.opacity = '1';
        popup.style.visibility = 'visible';

        // Prevent scrolling of background
        document.body.style.overflow = 'hidden';

        const meteothequeList = document.getElementById('meteotheque-list');
        meteothequeList.innerHTML = '';

        libraries.forEach(library => {
            const libraryId = String(library.id);
            const checkId = selectedMeasure != null ? selectedMeasure.id : selectedStation != null ? selectedStation.id : null;
            const likes = selectedMeasure != null ? likedMeasures : selectedStation != null ? likedStations : null;

            const liked = likes != null && likes.has(libraryId) && likes.get(libraryId).includes(checkId);

            const li = document.createElement('li');
            li.innerHTML = '<h3>' + library.name + '</h3>'
                + '<button id="add-button" type="submit" onclick="sendDataRequest(' + library.id + ')">' +
                (liked ? 'Ajoutée' : 'Ajouter') +
                '</button>';

            const addButton = li.querySelector('#add-button');
            if(liked)
                addButton.disabled = true;

            meteothequeList.appendChild(li);
        });
    }
</script>

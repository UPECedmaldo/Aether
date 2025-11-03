<div class="station-app">
    <!-- Liste des stations aimées -->
    <aside class="station-info">
        <h2>Stations Aimées (<?= count($stations)?>)</h2>
        <ul id="meteotheque-list" class="scrollable-list">
            <?php foreach ($stations as $station): ?>
                <li>
                    <h3><?= htmlspecialchars($station->getName()) ?></h3>
                    <?php
                        /*<button onclick='sendDataRequest(<?= $library->getId() ?>, "station", "<?= $station->getId() ?>")'>Supprimer</button>*/
                        if($edit)
                            echo '<button onclick=\'sendDataRequest(' . $library->getId() . ', "station", "' . $station->getId() . '")\'>Supprimer</button>';
                    ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Section principale -->
    <main class="station-measures">
        <!-- Formulaire pour filtrer les mesures -->
        <h2>Filtrer les mesures</h2>
        <form method="GET" action="">
            <div>
                <label for="sort">Tri</label>
                <select id="sort" name="sort">
                    <option value="date_asc" <?= ($sort == "date_asc" ? "selected" : "") ?>>Date croissante</option>
                    <option value="date_desc" <?= ($sort == "date_desc" ? "selected" : "") ?>>Date décroissante</option>
                    <option value="value_asc" <?= ($sort == "value_asc" ? "selected" : "") ?>>Valeur croissante</option>
                    <option value="value_desc" <?= ($sort == "value_desc" ? "selected" : "") ?>>Valeur décroissante</option>
                </select>
                <label for="type">Type</label>
                <select id="type" name="type">
                    <option value="">Toutes</option>
                    <?php foreach ($measureTypes as $type): ?>
                        <option value="<?= $type->getCode() ?>" <?= ($selectedType == $type ? 'selected' : '') ?>>
                            <?= htmlspecialchars($type->getName()) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="from">Depuis</label>
                <input type="date" id="from" name="from" value="<?= $from ?>" required
                    <?= (isset($min) && $min != null ? 'min="' . $min . '"' : '') ?>
                    <?= (isset($max) && $max != null ? 'max="' . $max . '"' : '') ?>
                >
            </div>
            <div>
                <label for="to">Jusqu'à</label>
                <input type="date" id="to" name="to" value="<?= $to ?>" required
                    <?= (isset($min) && $min != null ? 'min="' . $min . '"' : '') ?>
                    <?= (isset($max) && $max != null ? 'max="' . $max . '"' : '') ?>
                >
            </div>
            <div>
                <input type="hidden" name="library" value="<?= $library->getId() ?>">

                <?php if(!empty($measures)): ?>
                    <button type="submit">Filtrer</button>
                    <button type="submit" formaction="/library/graph">Graphique</button>
                    <select name="station">
                        <?php foreach ($measureStations as $station): ?>
                            <option value="<?= $station->getId() ?>">
                                <?= htmlspecialchars($station->getName()) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <p>Aucune mesure disponible</p>
                <?php endif ?>
            </div>
        </form>

        <!-- Tableau des données aimées -->
        <h2>Mesures Aimées (<?= count($measures) ?>)</h2>
        <table class="measures-table">
            <thead>
            <tr>
                <th>Station</th>
                <th>Type</th>
                <th>Valeur</th>
                <th>Unité</th>
                <th>Date</th>
                <?php
                    if($edit)
                        echo '<th>Actions</th>';
                ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($measures as $measure): ?>
                <tr id="<?= $measure->getId() ?>">
                    <td><?= htmlspecialchars($measure->getStationName()) ?></td>
                    <td><?= htmlspecialchars($measure->getType()->getName()) ?></td>
                    <td><?= htmlspecialchars($measure->getRoundedValue()) ?></td>
                    <td><?= htmlspecialchars($measure->getType()->getUnit()) ?></td>
                    <td><?= htmlspecialchars($measure->getFormattedDate()) ?></td>
                    <?php
                        if($edit)
                            echo '<td><button onclick=\'sendDataRequest(' . $library->getId() . ', "measure", "' . $measure->getId() . '")\'>Supprimer</button></td>';
                    ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php
        function movingAverage(array $data, int $window = 3): array {
            $result = [];
            for ($i = 0; $i < count($data); $i++) {
                $start = max(0, $i - $window + 1);
                $slice = array_slice($data, $start, $i - $start + 1);
                $result[] = array_sum($slice) / count($slice);
            }
            return $result;
        }
        function standardDeviation(array $data): float {
            $mean = array_sum($data) / count($data);
            $sumSquares = 0.0;
            foreach ($data as $value) {
                $sumSquares += pow($value - $mean, 2);
            }
            return sqrt($sumSquares / count($data));
        }
        function cumulativeValues(array $data): array {
            $cumulative = [];
            $sum = 0;
            foreach ($data as $value) {
                $sum += $value;
                $cumulative[] = $sum;
            }
            return $cumulative;
        }
        function variations(array $data): array {
            $var = [0];
            for ($i = 1; $i < count($data); $i++) {
                $var[] = $data[$i] - $data[$i - 1];
            }
            return $var;
        }
        
        // Extraction des données des mesures pour les statistiques
        $rawTemps = [];
        if (!empty($measures)) {
            // On récupère les valeurs des mesures
            // Filtrer pour n'avoir que les valeurs numériques si nécessaire
            foreach ($measures as $measure) {
                $value = $measure->getRoundedValue();
                if (is_numeric($value)) {
                    $rawTemps[] = (float)$value;
                }
            }
        }
        
        // Si on a des données, on calcule les statistiques
        if (!empty($rawTemps)) {
            $meanTemps = movingAverage($rawTemps);
            $stdTemp = standardDeviation($rawTemps);
            $cumulTemps = cumulativeValues($rawTemps);
            $variationTemp = variations($rawTemps);
        ?>
        
        <h3>Statistiques météo</h3>
        <table border="1" cellpadding="5">
            <tr>
                <th>Index</th>
                <th>Valeur brute</th>
                <th>Moyenne mobile</th>
                <th>Cumulée</th>
                <th>Variation</th>
            </tr>
            <?php foreach ($rawTemps as $i => $val): ?>
            <tr>
                <td><?= $i ?></td>
                <td><?= round($val, 2) ?></td>
                <td><?= round($meanTemps[$i], 2) ?></td>
                <td><?= round($cumulTemps[$i], 2) ?></td>
                <td><?= round($variationTemp[$i], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <p><strong>Écart-type :</strong> <?= round($stdTemp, 2) ?><?= !empty($measures) && !empty($measures[0]->getType()) ? htmlspecialchars($measures[0]->getType()->getUnit()) : '' ?></p>
        <?php } ?>
    </main>
</div>

<script>
    function sendDataRequest(libraryId, type, reference) {
        // Send request
        const data = new FormData();
        data.append('library', libraryId);
        data.append('del_type', type);
        data.append('reference_id', reference);

        fetch('/library/del', {
            method: 'POST',
            body: data
        }).then(response => {
            console.log(response.text());

            // refresh page
            location.reload();

        }).catch(error => {
            console.error(error);
        });
    }
</script>
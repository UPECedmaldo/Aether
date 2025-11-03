<?php
// Définitions des fonctions statistiques
function standardDeviation(array $data): float {
    if (count($data) < 2) return 0;
    $mean = array_sum($data) / count($data);
    $sumSquares = 0.0;
    foreach ($data as $value) {
        $sumSquares += pow($value - $mean, 2);
    }
    return sqrt($sumSquares / count($data));
}

function movingAverage(array $data, int $window = 3): array {
    $result = [];
    for ($i = 0; $i < count($data); $i++) {
        $start = max(0, $i - $window + 1);
        $slice = array_slice($data, $start, $i - $start + 1);
        $result[] = array_sum($slice) / count($slice);
    }
    return $result;
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
    $var = [];
    if (count($data) > 0) {
        $var[] = 0;
        for ($i = 1; $i < count($data); $i++) {
            $var[] = $data[$i] - $data[$i - 1];
        }
    }
    return $var;
}
?>

<section class="container">
    <div class="stations-container">
        <div class="station-details">
            <h1 class="station-info-title"><?= htmlspecialchars($selected1->getName()) ?></h1>
            <p>Id: <?= htmlspecialchars($selected1->getId()) ?></p>
            <p>Nom: <?= htmlspecialchars($selected1->getName()) ?></p>
            <p>Code: <?= htmlspecialchars($selected1->getCode()) ?></p>
            <p>Commune: <?= htmlspecialchars($selected1->getCityName()) ?></p>
            <p>Département: <?= htmlspecialchars($selected1->getDepartmentName()) ?></p>
            <p>Région: <?= htmlspecialchars($selected1->getRegionName()) ?></p>
            <p>EPCI: <?= htmlspecialchars($selected1->getEpciName()) ?></p>
        </div>

        <div class="station-details">
            <h1 class="station-info-title"><?= htmlspecialchars($selected2->getName()) ?></h1>
            <p>Id: <?= htmlspecialchars($selected2->getId()) ?></p>
            <p>Nom: <?= htmlspecialchars($selected2->getName()) ?></p>
            <p>Code: <?= htmlspecialchars($selected2->getCode()) ?></p>
            <p>Commune: <?= htmlspecialchars($selected2->getCityName()) ?></p>
            <p>Département: <?= htmlspecialchars($selected2->getDepartmentName()) ?></p>
            <p>Région: <?= htmlspecialchars($selected2->getRegionName()) ?></p>
            <p>EPCI: <?= htmlspecialchars($selected2->getEpciName()) ?></p>
        </div>
    </div>

    <form id="form" class="station-form" method="post" action="/graph/compare#form">
        <div class="date-inputs">
            <label>
                Date de début:
                <input type="date" name="from" value="<?= htmlspecialchars($from) ?>"
                    <?php if(isset($min) && $min != null) echo 'min="' . htmlspecialchars($min) . '"'; ?>
                    <?php if(isset($max) && $max != null) echo 'max="' . htmlspecialchars($max) . '"'; ?>
                >
            </label>
            <label>
                Date de fin:
                <input type="date" name="to" value="<?= htmlspecialchars($to) ?>"
                    <?php if(isset($min) && $min != null) echo 'min="' . htmlspecialchars($min) . '"'; ?>
                    <?php if(isset($max) && $max != null) echo 'max="' . htmlspecialchars($max) . '"'; ?>
                >
            </label>
        </div>
        <label>
            Type de mesure:
            <select name="measure">
                <?php foreach ($measures as $measure): ?>
                    <option value="<?= htmlspecialchars($measure->getCode()) ?>"
                        <?= $measure->getCode() === $selectedMeasure ? 'selected' : '' ?>>
                        <?= htmlspecialchars($measure->getName()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <input type="hidden" name="station1" value="<?= $selected1->getId() ?>">
        <input type="hidden" name="station2" value="<?= $selected2->getId() ?>">
        <button type="submit">Comparer</button>
    </form>

    <form id="export-form" class="export-form" method="post" action="/graph/export.php">
        <label for="export-type">Exporter en :</label>
        <select name="format" id="export-type">
            <option value="json">JSON</option>
            <option value="csv">CSV</option>
        </select>
        <input type="hidden" name="data" id="export-data">
        <input type="hidden" name="station1" value="<?= htmlspecialchars($selected1->getId()) ?>">
        <input type="hidden" name="station2" value="<?= htmlspecialchars($selected2->getId()) ?>">
        <input type="hidden" name="from" value="<?= htmlspecialchars($from) ?>">
        <input type="hidden" name="to" value="<?= htmlspecialchars($to) ?>">
        <input type="hidden" name="measure" value="<?= htmlspecialchars($selectedMeasure) ?>">
        <button type="submit">Exporter</button>
    </form>

    <script>
        document.getElementById('export-form').addEventListener('submit', function (event) {
            event.preventDefault();

            let dataToExport = [];

            document.querySelectorAll('.station-details').forEach(station => {
                let stationData = {};
                station.querySelectorAll('p').forEach(p => {
                    let keyValue = p.innerText.split(':');
                    if (keyValue.length === 2) {
                        stationData[keyValue[0].trim()] = keyValue[1].trim();
                    }
                });
                dataToExport.push(stationData);
            });

            const exportDataField = document.getElementById('export-data');
            exportDataField.value = JSON.stringify(dataToExport);

            console.log('Export Data:', exportDataField.value);
            this.submit();
        });
    </script>

    <div class="analysis-container">
        <!-- Graphique -->
        <div class="graph-container">
            <?php
                $script = true;
                foreach ($graphs as $graph) {
                    $graph->draw(inject: $script, class: "measure-graph");
                    if ($script) {
                        $script = false;
                    }
                }
            ?>
        </div>
        
        <?php
        // Extraction des données depuis measures1 et measures2
        $station1Data = [];
        $station2Data = [];
        $unit = "";
        $dates = [];
        
        // Obtenir le code de mesure sélectionné
        $measureCode = $selectedMeasure;
        
        // Utiliser les mesures déjà filtrées
        foreach ($measures1 as $measure) {
            $station1Data[] = $measure->getValue();
            $dates[] = $measure->getDate()->format('Y-m-d');
        }
        
        foreach ($measures2 as $measure) {
            $station2Data[] = $measure->getValue();
        }
        
        // Récupérer l'unité depuis la mesure sélectionnée
        $currentMeasure = null;
        foreach ($measures as $measure) {
            if ($measure->getCode() === $measureCode) {
                $currentMeasure = $measure;
                $unit = $measure->getUnit();
                break;
            }
        }
        
        // Si l'unité n'est pas trouvée, utiliser une valeur par défaut
        if (empty($unit)) {
            $unit = '°C';
        }
        
        // Calcul des statistiques
        $statsStation1 = [
            'mean' => count($station1Data) ? array_sum($station1Data) / count($station1Data) : 0,
            'max' => count($station1Data) ? max($station1Data) : 0,
            'min' => count($station1Data) ? min($station1Data) : 0,
            'std' => standardDeviation($station1Data),
            'movingAvg' => movingAverage($station1Data),
            'cumulative' => cumulativeValues($station1Data),
            'variations' => variations($station1Data)
        ];
        
        $statsStation2 = [
            'mean' => count($station2Data) ? array_sum($station2Data) / count($station2Data) : 0,
            'max' => count($station2Data) ? max($station2Data) : 0,
            'min' => count($station2Data) ? min($station2Data) : 0,
            'std' => standardDeviation($station2Data),
            'movingAvg' => movingAverage($station2Data),
            'cumulative' => cumulativeValues($station2Data),
            'variations' => variations($station2Data)
        ];
        ?>
        
        <!-- Résumé statistique -->
        <div class="stats-summary">
            <h3>Résumé des données</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label">Moyenne <?= htmlspecialchars($selected1->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation1['mean'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Moyenne <?= htmlspecialchars($selected2->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation2['mean'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Maximum <?= htmlspecialchars($selected1->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation1['max'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Maximum <?= htmlspecialchars($selected2->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation2['max'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Minimum <?= htmlspecialchars($selected1->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation1['min'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Minimum <?= htmlspecialchars($selected2->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation2['min'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Écart-type <?= htmlspecialchars($selected1->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation1['std'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Écart-type <?= htmlspecialchars($selected2->getName()) ?>:</span>
                    <span class="stat-value"><?= round($statsStation2['std'], 2) ?> <?= htmlspecialchars($unit) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Tableau des valeurs -->
        <div class="simplified-table">
            <h3>Valeurs mesurées</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th><?= htmlspecialchars($selected1->getName()) ?></th>
                        <th><?= htmlspecialchars($selected2->getName()) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < max(count($station1Data), count($station2Data)); $i++): ?>
                    <tr>
                        <td><?= isset($dates[$i]) ? htmlspecialchars($dates[$i]) : 'N/A' ?></td>
                        <td><?= isset($station1Data[$i]) ? round($station1Data[$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                        <td><?= isset($station2Data[$i]) ? round($station2Data[$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Détails statistiques -->
        <div class="more-details">
            <button id="toggle-details" class="btn-details">Afficher plus de détails</button>
            <div id="detailed-stats" class="hidden">
                <h3>Analyse statistique détaillée</h3>
                <div class="stats-explanation">
                    <p>Analyse complète des données météorologiques :</p>
                    <ul>
                        <li><strong>Moyenne mobile</strong> : Tendance sur 3 mesures</li>
                        <li><strong>Cumul</strong> : Valeurs cumulées</li>
                        <li><strong>Variation</strong> : Différence entre mesures</li>
                    </ul>
                </div>
                
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Moyenne mobile<br><?= htmlspecialchars($selected1->getName()) ?></th>
                            <th>Moyenne mobile<br><?= htmlspecialchars($selected2->getName()) ?></th>
                            <th>Cumul<br><?= htmlspecialchars($selected1->getName()) ?></th>
                            <th>Cumul<br><?= htmlspecialchars($selected2->getName()) ?></th>
                            <th>Variation<br><?= htmlspecialchars($selected1->getName()) ?></th>
                            <th>Variation<br><?= htmlspecialchars($selected2->getName()) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < max(count($station1Data), count($station2Data)); $i++): ?>
                        <tr>
                            <td><?= isset($dates[$i]) ? htmlspecialchars($dates[$i]) : 'N/A' ?></td>
                            <td><?= isset($statsStation1['movingAvg'][$i]) ? round($statsStation1['movingAvg'][$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                            <td><?= isset($statsStation2['movingAvg'][$i]) ? round($statsStation2['movingAvg'][$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                            <td><?= isset($statsStation1['cumulative'][$i]) ? round($statsStation1['cumulative'][$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                            <td><?= isset($statsStation2['cumulative'][$i]) ? round($statsStation2['cumulative'][$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                            <td><?= isset($statsStation1['variations'][$i]) ? round($statsStation1['variations'][$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                            <td><?= isset($statsStation2['variations'][$i]) ? round($statsStation2['variations'][$i], 2) . ' ' . htmlspecialchars($unit) : '-' ?></td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('toggle-details').addEventListener('click', function() {
        const detailsSection = document.getElementById('detailed-stats');
        detailsSection.classList.toggle('hidden');
        this.textContent = detailsSection.classList.contains('hidden') 
            ? 'Afficher plus de détails' 
            : 'Masquer les détails';
    });
    </script>
</section>
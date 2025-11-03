
<div class="stations-container">
    <div class="station-details">
        <h1 class="station-info-title">Statistiques sur <?= count($measures) ?> mesure(s)</h1>
        <p>Météothèque: <?= htmlspecialchars($library->getName()) ?></p>
        <p>Station: <?= htmlspecialchars($station->getName()) ?></p>
        <p>De: <?= htmlspecialchars($from->format("d/m/Y H:i")) ?></p>
        <p>A: <?= htmlspecialchars($to->format("d/m/Y H:i")) ?></p>
        <p>Types: <?= htmlspecialchars(implode(", ", $measureTypes)) ?></p>

    </div>
</div>

<?php

    $script = true;
    foreach ($graphs as $graph) {

        echo '<div class="graph-container">';
        $graph->draw(inject: $script);
        echo "</div>";

        if($script)
            $script = false;
    }
?>

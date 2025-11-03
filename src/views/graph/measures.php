<section class="container">
    <div class="station-info">
        <div class="station-details">
            <h1 class="station-info-title"><?= htmlspecialchars($selected->getName()) ?></h1>
            <p>Id: <?= htmlspecialchars($selected->getId()) ?></p>
            <p>Nom: <?= htmlspecialchars($selected->getName()) ?></p>
            <p>Code: <?= htmlspecialchars($selected->getCode()) ?></p>
            <p>Commune: <?= htmlspecialchars($selected->getCityName()) ?></p>
            <p>Département: <?= htmlspecialchars($selected->getDepartmentName()) ?></p>
            <p>Région: <?= htmlspecialchars($selected->getRegionName()) ?></p>
            <p>EPCI: <?= htmlspecialchars($selected->getEpciName()) ?></p>
        </div>

        <form class="station-form">
            <label>
                Type de graphique:
                <select name="graphType">
                    <?php foreach (Sae\Models\DataObject\GraphType::getTypes() as $code => $name): ?>
                        <option value="<?= $code ?>" <?= isset($selectedGraphType) && $selectedGraphType === $code ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Type de statistique:
                <select name="statType">
                    <?php foreach (Sae\Models\DataObject\StatisticType::getTypes() as $code => $name): ?>
                        <option value="<?= $code ?>" <?= isset($selectedStatType) && $selectedStatType === $code ? 'selected' : '' ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Date de début:
                <input type="date" name="from" value="<?= $from ?>"
                    <?php if(isset($min) && $min != null) echo 'min="' . $min . '"' ?>
                    <?php if(isset($max) && $max != null) echo 'max="' . $max . '"' ?>
                >
            </label>
            <label>
                Date de fin:
                <input type="date" name="to" value="<?= $to ?>"
                    <?php if(isset($min) && $min != null) echo 'min="' . $min . '"' ?>
                    <?php if(isset($max) && $max != null) echo 'max="' . $max . '"' ?>
                >
            </label>
            <input type="hidden" name="station" value="<?= $selected->getId() ?>">
            <button type="submit">Rafraichir</button>
        </form>
    </div>

    <div class="graph-container">
        <?php
            $script = true;
            foreach ($graphs as $graph) {
                $graph->draw(inject: $script, class: "measure-graph");
                if($script)
                    $script = false;
            }
        ?>
    </div>
</section>

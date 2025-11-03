<div class="container-banner">
    <div class="text-container">
        <h2>Comparer les stations</h2>
        <p>
            Comparez facilement les données entre deux stations météo grâce à notre interface interactive.
            Sélectionnez vos stations, la mesure et la période souhaitée pour visualiser les résultats.
        </p>
        <a href="#compare-form" class="explore-button">Commencer la comparaison</a>
    </div>
    <div class="image-container">
        <img src="/public/assets/images/antenne.png" alt="Logo - Antenne">
    </div>
</div>

<!-- Formulaire -->
<div class="container" id="compare-form">
    <h1>Comparer les Stations</h1>

    <?php
        if(isset($flashMessages))
            foreach($flashMessages as $flashMessage)
                $flashMessage->draw();
    ?>

    <form id="form" action="/graph/compare" method="post">
        <div class="form-group">
            <label for="station1">Station 1</label>
            <select name="station1" id="station1">
                <?php foreach ($stations as $station) : ?>
                    <option value="<?= $station->getId(); ?>" <?php echo ($selected != null && $selected->getId() == $station->getId() ? "selected" : "") ?>><?= $station->getName(); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="station2">Station 2</label>
            <select name="station2" id="station2">
                <?php foreach ($stations as $station) : ?>
                    <option value="<?= $station->getId(); ?>"><?= $station->getName(); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="measure">Mesure</label>
            <select name="measure" id="measure">
                <?php foreach ($measures as $measure) : ?>
                    <option value="<?= $measure->getCode(); ?>"><?= $measure->getName(); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="fromDate">Depuis</label>
            <input type="date" name="from-date" id="fromDate" value="<?php echo $from ?>" required
                <?php if(isset($min) && $min != null) echo 'min="' . $min . '"' ?>
                <?php if(isset($max) && $max != null) echo 'max="' . $max . '"' ?>>
        </div>
        <div class="form-group">
            <label for="toDate">Jusqu'à</label>
            <input type="date" name="to-date" id="toDate" value="<?php echo $to ?>" required
                <?php if(isset($min) && $min != null) echo 'min="' . $min . '"' ?>
                <?php if(isset($max) && $max != null) echo 'max="' . $max . '"' ?>>
        </div>
        <button type="submit">Comparer</button>
    </form>
</div>
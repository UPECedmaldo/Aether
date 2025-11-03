<div class="mtq-container">
    <h2 class="mtq-title">Météothèques publiques</h2>

    <?php
    if(isset($flashMessages))
        foreach($flashMessages as $flashMessage)
            $flashMessage->draw();
    ?>

    <div class="mtq-scroll-container">
        <?php if (!empty($publicLibraries)): ?>
            <?php foreach ($publicLibraries as $library): ?>
                <div class="mtq-card" style="background-color: #<?php echo $library->getColor(); ?>">
                    <div class="mtq-card-name"><?php echo htmlspecialchars($library->getName()); ?></div>
                    <div class="mtq-card-date"><?php echo date('d/m/Y', strtotime($library->getCreation())) . " - " . $library->getUsername(); ?></div>
                    <div class="mtq-card-stats">
                        Station(s) : <?php echo $library->getStationCount(); ?><br>
                        Mesure(s) : <?php echo $library->getMeasureCount(); ?>
                    </div>
                    <a href="/library/consult?library=<?php echo $library->getId(); ?>"><button class="mtq-btn-consulter" style="color: #<?php echo $library->getColor(); ?>">Consulter</button></a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="mtq-empty">Aucune météothèque publique disponible pour le moment.</div>
        <?php endif; ?>
    </div>

    <?php if($logged): ?>
        <h2 class="mtq-title">Mes météothèques
            <form action="/library/create" method="get" style="display:inline;">
                <button type="submit" class="mtq-btn-creer">Créer</button>
            </form>
        </h2>
        <div class="mtq-scroll-container">
            <?php if (!empty($myRepositories)): ?>
                <?php foreach ($myRepositories as $library): ?>
                    <div class="mtq-card" style="background-color: #<?php echo $library->getColor(); ?>">
                        <div class="mtq-card-name"><?php echo htmlspecialchars($library->getName()); ?></div>
                        <div class="mtq-card-date"><?php echo date('d/m/Y', strtotime($library->getCreation())); ?></div>
                        <div class="mtq-card-stats">
                            Station(s) : <?php echo $library->getStationCount(); ?><br>
                            Mesure(s) : <?php echo $library->getMeasureCount(); ?>
                        </div>
                        <a href="/library/edit?library=<?php echo $library->getId(); ?>"><button class="mtq-btn-consulter" style="color: #<?php echo $library->getColor(); ?>">Mettre à jour</button></a>
                        <a href="/library/update?library=<?php echo $library->getId(); ?>"><button class="mtq-btn-consulter" style="color: #<?php echo $library->getColor(); ?>">Éditer</button></a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="mtq-empty">Vous n'avez pas encore créé de météothèque.</div>
            <?php endif; ?>
        </div>

        <!-- Section des recommandations -->
        <?php if(isset($recommandations)): ?>
            <h2 class="mtq-title">Recommandations pour vous</h2>
            <div class="mtq-scroll-container">
                <?php foreach ($recommandations as $library): ?>
                    <div class="mtq-card recommandation-card" style="background-color: #<?php echo $library->getColor(); ?>">
                        <span class="recommended-badge">★ Recommandé</span>
                        <div class="mtq-card-name"><?php echo htmlspecialchars($library->getName()); ?></div>
                        <div class="mtq-card-date"><?php echo date('d/m/Y', strtotime($library->getCreation())); ?></div>
                        <div class="mtq-card-stats">
                            Station(s) : <?php echo $library->getStationCount(); ?><br>
                            Mesure(s) : <?php echo $library->getMeasureCount(); ?>
                        </div>
                        <a href="/library/consult?library=<?php echo $library->getId(); ?>"><button class="mtq-btn-consulter" style="color: #<?php echo $library->getColor(); ?>">Consulter</button></a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Script pour la visualisation améliorée -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animation subtile sur les cartes recommandées au survol
        const recommendedCards = document.querySelectorAll('.recommandation-card');

        recommendedCards.forEach(card => {
            card.addEventListener('mouseover', function() {
                this.style.transform = 'scale(1.03)';
                this.style.transition = 'transform 0.3s ease';
            });

            card.addEventListener('mouseout', function() {
                this.style.transform = 'scale(1)';
            });
        });
    });
</script>
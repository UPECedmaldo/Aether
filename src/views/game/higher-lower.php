<div class="game-container">
    <div class="scoreboard">
        <div class="score">Score: <span id="score">0</span></div>
        <div class="high-score">Meilleur score: <span id="high-score">0</span></div>
    </div>

    <div class="stations-comparison">
        <div class="station current" id="station-current">
            <div class="station-info">
                <h3 class="station-name">Chargement...</h3>
                <p class="station-city"></p>
                <p class="station-region"></p>
                <div class="temperature"><span class="temp-value">--</span>°C</div>
            </div>
        </div>

        <div class="versus">VS</div>

        <div class="station next" id="station-next">
            <div class="station-info">
                <h3 class="station-name">Chargement...</h3>
                <p class="station-city"></p>
                <p class="station-region"></p>
                <div class="temperature hidden"><span class="temp-value">--</span>°C</div>
            </div>
            <div class="question">
                <p>La température est-elle plus haute ou plus basse?</p>
                <div class="buttons">
                    <button id="btn-higher" class="btn-guess">Plus haute</button>
                    <button id="btn-lower" class="btn-guess">Plus basse</button>
                </div>
            </div>
        </div>
    </div>

    <div id="game-result" class="game-result hidden">
        <div class="result-content">
            <h2 class="result-title">Partie terminée!</h2>
            <p>Votre score: <span id="final-score">0</span></p>
            <button id="btn-restart" class="btn-restart">Rejouer</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Variables du jeu
        let currentScore = 0;
        let highScore = localStorage.getItem('higherLowerHighScore') || 0;
        let currentStation = null;
        let nextStation = null;

        // Éléments DOM
        const scoreEl = document.getElementById('score');
        const highScoreEl = document.getElementById('high-score');
        const currentStationEl = document.getElementById('station-current');
        const nextStationEl = document.getElementById('station-next');
        const btnHigher = document.getElementById('btn-higher');
        const btnLower = document.getElementById('btn-lower');
        const gameResultEl = document.getElementById('game-result');
        const finalScoreEl = document.getElementById('final-score');
        const btnRestart = document.getElementById('btn-restart');

        // Affichage du meilleur score
        highScoreEl.textContent = highScore;

        // Initialisation du jeu
        initGame();

        // Fonctions du jeu
        async function initGame() {
            // Réinitialiser le score
            currentScore = 0;
            scoreEl.textContent = currentScore;

            // Réinitialiser l'affichage
            gameResultEl.classList.add('hidden');

            // Charger la première station
            currentStation = await fetchStationData();
            updateStationDisplay(currentStationEl, currentStation);

            // Charger la station suivante
            nextStation = await fetchStationData();
            updateStationDisplay(nextStationEl, nextStation, true);

            // Activer les boutons
            enableButtons();
        }

        async function fetchStationData() {
            try {
                const response = await fetch('/game/getStationData');
                const data = await response.json();

                if (data.error) {
                    console.error('Erreur:', data.error);
                    return null;
                }

                return data;
            } catch (error) {
                console.error('Erreur lors de la récupération des données:', error);
                return null;
            }
        }

        function updateStationDisplay(element, station, hideTemperature = false) {
            if (!station) return;

            const nameEl = element.querySelector('.station-name');
            const cityEl = element.querySelector('.station-city');
            const regionEl = element.querySelector('.station-region');
            const temperatureEl = element.querySelector('.temperature');
            const tempValueEl = element.querySelector('.temp-value');

            nameEl.textContent = station.station.name;
            cityEl.textContent = station.station.city || '';
            regionEl.textContent = station.station.region || '';
            tempValueEl.textContent = station.temperature;

            if (hideTemperature) {
                temperatureEl.classList.add('hidden');
            } else {
                temperatureEl.classList.remove('hidden');
            }
        }

        function enableButtons() {
            btnHigher.disabled = false;
            btnLower.disabled = false;
        }

        function disableButtons() {
            btnHigher.disabled = true;
            btnLower.disabled = true;
        }

        async function nextRound() {
            // La station actuelle devient l'ancienne "suivante"
            currentStation = nextStation;

            // Mettre à jour l'affichage
            updateStationDisplay(currentStationEl, currentStation);

            // Charger une nouvelle station suivante
            nextStation = await fetchStationData();
            updateStationDisplay(nextStationEl, nextStation, true);

            // Réactiver les boutons
            enableButtons();
        }

        function checkAnswer(isHigher) {
            // Récupérer les températures
            const currentTemp = currentStation.temperature;
            const nextTemp = nextStation.temperature;

            // Vérifier si la réponse est correcte
            let correct = false;
            if (isHigher && nextTemp > currentTemp) {
                correct = true;
            } else if (!isHigher && nextTemp < currentTemp) {
                correct = true;
            }

            // Révéler la température
            const nextTempEl = nextStationEl.querySelector('.temperature');
            nextTempEl.classList.remove('hidden');

            // Mettre à jour le score ou terminer le jeu
            if (correct) {
                currentScore++;
                scoreEl.textContent = currentScore;

                // Mettre à jour le meilleur score si nécessaire
                if (currentScore > highScore) {
                    highScore = currentScore;
                    highScoreEl.textContent = highScore;
                    localStorage.setItem('higherLowerHighScore', highScore);
                }

                // Passer au tour suivant après un délai
                setTimeout(nextRound, 1500);
            } else {
                // Fin de partie
                finalScoreEl.textContent = currentScore;
                setTimeout(() => {
                    gameResultEl.classList.remove('hidden');
                }, 1500);
            }
        }

        // Événements
        btnHigher.addEventListener('click', () => {
            disableButtons();
            checkAnswer(true);
        });

        btnLower.addEventListener('click', () => {
            disableButtons();
            checkAnswer(false);
        });

        btnRestart.addEventListener('click', initGame);
    });
</script>
<!-- Bannière -->
<div class="image-banner">
    <div class="weather-overlay">
        <div class="text-container">
            <h2>Prévisions météo sur la carte</h2>
            <p>
                Aether collabore avec des services météorologiques nationaux et des météorologues qualifiés pour vous offrir des prévisions précises pour n'importe quel lieu.
                Nous aidons les gens à comprendre l'impact de la météo, à prendre les meilleures décisions et à planifier leur journée à l'avance.
                Sur notre site, vous pouvez vous attendre à des prévisions météorologiques d'une grande précision, offrant des informations exploitables sur le climat pour renforcer votre sécurité.
            </p>
        </div>
        <div class="button-container">
            <a href="#map" class="explore-button">DÉCOUVERTE DE LA METEO SUR LA CARTE</a>
        </div>
        <div class="climate-button-container">
            <a href="/index/climate-map" target="_blank" class="climate-button">EXPLORER LA CARTE CLIMATIQUE</a>
        </div>
        <div class="image-container">
            <img src="/public/assets/images/Maprefait.png" alt="Image illustrative" class="banner-image" />
        </div>
    </div>
</div>

<!-- Barre de recherche -->
<div class="search-bar">
    <input
            type="text"
            id="station-search"
            placeholder="Recherchez une station..."
            oninput="filterStations()"
    />
    <ul id="station-list"></ul>
</div>
<div id="map">
    <?php
        $map->draw(inject: true);
    ?>
</div>

<script>

    const stations = <?= json_encode($convertedStations) ?>;

    function filterStations() {
        const query = document.getElementById("station-search").value.toLowerCase();
        const stationList = document.getElementById("station-list");
        stationList.innerHTML = "";

        const filtered = stations.filter(station =>
            station.name.toLowerCase().includes(query)
        );

        filtered.forEach(station => {
            const listItem = document.createElement("li");
            listItem.textContent = station.name;
            listItem.onclick = () => zoomToStation(station);
            stationList.appendChild(listItem);
        });
    }

    function zoomToStation(station) {

        const map = mapBiding.get('<?= $map->getId() ?>');
        console.log(station);
        map.setView(L.latLng(station.lat, station.long), 12);

    }

    // Ajouter un gestionnaire d'événement pour fermer la liste si on clique à l'extérieur
    document.addEventListener('click', function(event) {
        const searchBar = document.querySelector('.search-bar');
        const stationList = document.getElementById("station-list");

        // Vérifie si le clic est en dehors de la barre de recherche et de la liste
        if (!searchBar.contains(event.target)) {
            stationList.innerHTML = ""; // Masquer la liste
        }
    });

</script>

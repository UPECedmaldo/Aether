<div class="fond-accueil" id="fond-accueil">
    <div class="accueil">
        <main>
            <div class="container">
                <div class="main">
                    <h2>Prévisions<span class="bleu"> météorologiques </span></h2>
                    <div class="button-container">
                        <a href="#partie-img"><button class="custom-button">Lire Plus</button></a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<div class="sections">
    <div class="partie-carte">
        <div class="textes">
            <h2>Prévision météorologique
                de la semaine en France</h2>
            <p>Rester informé sur la météo de la Semaine en France avec Aether.
                Les données météorologiques sont mis à jour toutes les semaines. </p>
            <div class="button-container">
                <a href="map"><button class="carte-button">Voir</button></a>
            </div>
        </div>
        <div class="image-presentation">
            <a href="/index/map"><img src="../public/assets/images/carte_france.png" alt=""></a>
        </div>


    </div>

    <div class="partie-img" id="partie-img">
        <h2 class="animated-text">Guyane, St-Laurent-du-maroni</h2>
        <p class="animated-text"><?= $today?> </p>
        <p class="degre"><?= $current ?>°</p>
        <div class="mesures-accueil">
            <div class="mesure">
                <!--<div class="mesure-image"><img src="../public/assets/images/water.png" alt="Image"></div>-->
                <div class="mesure-textes">
                    <p> </p>
                </div>
            </div>
            <div class="mesure">
                <div class="mesure-image"><img src="../public/assets/images/temp.png" alt="Image"></div>-->
                <div class="mesure-textes">
                    <p>Température</p>
                    <p><?= $max ?>°max/<?= $min ?>°min</p>
                </div>
            </div>
            <div class="mesure">
                <!--<div class="mesure-image"><img src="../public/assets/images/weather.png" alt="Image"></div>-->
                <div class="mesure-textes">
                    <p> </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ajouter ici le reste des sections de contenu -->

    <div class="partie-carte">
        <div class="textes">
            <h2>Comparaison des stations en France</h2>
            <p>Aether collabore avec des services météorologiques nationaux et des météorologues qualifiés pour vous offrir des prévisions précises pour n'importe quel lieu. Nous aidons les gens à comprendre l'impact de la météo, à prendre les meilleures décisions et à planifier leur journée à l'avance.
                Sur notre site, vous pouvez vous attendre à des prévisions météorologiques d'une grande précision, offrant des informations exploitables sur le climat pour renforcer votre sécurité.</p>
            <div class="button-container">
                <a href="../graph/select"><button class="carte-button">Voir</button></a>
            </div>
        </div>

        <div class="container-images">
            <button id="left-arrow" class="arrow">◀</button>
            <div class="image-welcome">
                <img src="../public/assets/images/Ville-Images/Martinique.png" alt="">
                <div class="city-name">Martinique</div>
            </div>
            <div class="image-welcome">
                <a href="/graph/select"><img src="../public/assets/images/Ville-Images/Nice.png" alt=""></a>
                <div class="city-name">Nice</div>
            </div>
            <div class="image-welcome">
                <img src="../public/assets/images/Ville-Images/Saint-Georges.png" alt="">
                <div class="city-name">Saint-Georges</div>
            </div>
            <button id="right-arrow" class="arrow">▶</button>
        </div>
    </div>
</div>


<script>
    // Dossier contenant les images
    const imagesPath = "../public/assets/images/Ville-Images/";

    // Liste des fichiers (les noms de fichiers = noms des villes)
    const villes = [
        "Ajaccio",
        "Bangor",
        "Blothzheim",
        "LesAbymes",
        "Maripasoula-Guyane",
        "Matoury-Guyane",
        "Mayotte",
        "Nice",
        "Perros-Guirec",
        "Saint denis (la Reunion)",
        "Saint-Georges",
        "Sainte-Marie",
        "Trinité-Martinique"
    ];

    let currentIndex = 0; // Index de la première image affichée

    // Récupération des éléments HTML des trois images
    const imageElements = document.querySelectorAll(".image-welcome img");
    const cityNameElements = document.querySelectorAll(".image-welcome .city-name");
    const leftArrow = document.getElementById("left-arrow");
    const rightArrow = document.getElementById("right-arrow");

    // Fonction pour mettre à jour les images et les noms de villes visibles
    function updateContent() {
        for (let i = 0; i < 3; i++) {
            const villeIndex = (currentIndex + i) % villes.length; // Calculer l'index circulaire
            const ville = villes[villeIndex];

            imageElements[i].src = `${imagesPath}${ville}.png`; // Mettre à jour l'image
            imageElements[i].alt = ville; // Mettre à jour l'attribut alt
            cityNameElements[i].textContent = ville; // Mettre à jour le nom de la ville
        }
    }

    // Écouteur pour la flèche gauche
    leftArrow.addEventListener("click", () => {
        currentIndex = (currentIndex - 1 + villes.length) % villes.length; // Décaler à gauche (circulaire)
        updateContent();
    });

    // Écouteur pour la flèche droite
    rightArrow.addEventListener("click", () => {
        currentIndex = (currentIndex + 1) % villes.length; // Décaler à droite (circulaire)
        updateContent();
    });

    // Initialisation : afficher les premières images
    updateContent();
</script>
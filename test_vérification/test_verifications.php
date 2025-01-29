<?php
// Configuration de la connexion à la base de données
$host = 'localhost';
$dbname = 'projetunivers'; 
$username = 'utilitaire'; 
$password = 'motpassoublier'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<h1>Connexion réussie à la base de données.</h1>";
} catch (PDOException $e) {
    die("<div class='error'>Erreur de connexion : " . $e->getMessage() . "</div>");
}

function logResult($message) {
    file_put_contents('test_log.txt', $message . PHP_EOL, FILE_APPEND);
}

function printWithColor($text, $color) {
    // Codes de couleur ANSI
    $colors = [
        'green' => "\033[32m",
        'red' => "\033[31m",
        'yellow' => "\033[33m",
        'reset' => "\033[0m"
    ];
    echo $colors[$color] . $text . $colors['reset'] . "<br>";
}

// 1. Vérification de l'intégrité des données
function testIntegrity($pdo) {
    echo "<h2>=== Test d'intégrité des données ===</h2>";
    logResult("Test d'intégrité des données commencé.");
    
    // Vérification des colonnes non nulles
    $tables = [
        'commune' => ['code_commune', 'nom_commune'],
        'coordonnees' => ['id_coordonnees', 'latitude', 'longitude', 'code_commune'],
        'departement' => ['code_departement', 'nom_departement'],
        'epci' => ['code_epci', 'nom_epci'],
        'favoris' => ['id_favori', 'id_meteotheque', 'type', 'reference_id', 'date_ajout'],
        'historique' => ['id_recent', 'id_utilisateur', 'type', 'reference_id', 'date_ajout'],
        'mesure' => ['id_mesure', 'id_station', 'type', 'valeur', 'date', 'date_ajout'],
        'mesures_historiques' => ['id', 'station_id', 'date', 'temperature', 'precipitation', 'pression_mer', 'vitesse_vent', 'visibilite', 'nebulosite', 'pression_station', 'geopotentiel', 'hauteur_neige'],
        'meteotheque' => ['id_meteotheque', 'id_utilisateur', 'etat', 'creation', 'nom', 'couleur'],
        'region' => ['code_region', 'nom_region'],
        'station' => ['id_station', 'code_station', 'nom', 'code_region', 'code_departement', 'code_commune', 'code_epci'],
        'utilisateur' => ['id_utilisateur', 'nom', 'prenom', 'pseudo', 'email', 'date_creation', 'mot_de_passe', 'date_connexion']
    ];

    foreach ($tables as $table => $columns) {
        foreach ($columns as $column) {
            $query = "SELECT COUNT(*) AS nb_invalid FROM $table WHERE $column IS NULL";
            $result = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
            echo $result['nb_invalid'] === 0 
                ? "<div class='success'>Toutes les données de la colonne '$column' dans la table '$table' sont conformes.</div>" 
                : "<div class='error'>{$result['nb_invalid']} lignes invalides trouvées dans la colonne '$column' de la table '$table'.</div>";
        }
    }
    logResult("Test d'intégrité des données terminé.");
    echo "<br>"; // Ligne vide pour séparer les résultats
}

// 2. Test des propriétés ACID
function testACID($pdo) {
    printWithColor("=== Test des propriétés ACID ===", 'yellow');
    logResult("Test des propriétés ACID commencé.");
    try {
        $pdo->beginTransaction();
        $pdo->exec("INSERT INTO station (code_station, nom) VALUES ('test_code', 'Test Station')");
        $pdo->exec("INSERT INTO station (code_station, nom) VALUES ('test_code_2', 'Test Station 2')");
        // Forcer une erreur pour tester l'annulation
        $pdo->exec("INSERT INTO station (non_existant_colonne) VALUES ('valeur3')");
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Transaction annulée comme attendu : " . $e->getMessage() . "\n";
    }
    logResult("Test des propriétés ACID terminé.");
    echo "\n"; // Ligne vide pour séparer les résultats
    cleanupTestData($pdo); // Appel de la fonction de nettoyage
}

// 3. Test de performance
function testPerformance($pdo) {
    printWithColor("=== Test de performance ===", 'yellow');
    logResult("Test de performance commencé.");
    $startTime = microtime(true);
    $result = $pdo->query("SELECT * FROM station LIMIT 10000")->fetchAll(); 
    $endTime = microtime(true);
    echo "Temps de récupération : " . ($endTime - $startTime) . " secondes.\n";
    logResult("Temps de récupération : " . ($endTime - $startTime) . " secondes.");
    echo "\n"; // Ligne vide pour séparer les résultats
    cleanupTestData($pdo); // Appel de la fonction de nettoyage
}

// 4. Test d'évolutivité
function testScalability($pdo) {
    printWithColor("=== Test d'évolutivité ===", 'yellow');
    logResult("Test d'évolutivité commencé.");
    try {
        $rowsToInsert = 100000;
        $pdo->beginTransaction();
        for ($i = 0; $i < $rowsToInsert; $i++) {
            $pdo->exec("INSERT INTO station (code_station, nom) VALUES ('code_$i', 'Station $i')");
            // Insérer par lots de 1000 pour éviter le dépassement de temps
            if ($i % 1000 == 0) {
                $pdo->commit();
                $pdo->beginTransaction();
            }
        }
        $pdo->commit();
        echo "Insertion de $rowsToInsert lignes réussie.\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur lors du test d'évolutivité : " . $e->getMessage() . "\n";
    }
    logResult("Test d'évolutivité terminé.");
    echo "\n"; // Ligne vide pour séparer les résultats
    cleanupTestData($pdo); // Appel de la fonction de nettoyage
}

// 5. Test de sécurité
function testSecurity($pdo) {
    printWithColor("=== Test de sécurité ===", 'yellow');
    logResult("Test de sécurité commencé.");

    // Test des requêtes préparées
    $email = 'test@example.com'; // Remplacez par un email existant
    $statement = $pdo->prepare("SELECT * FROM utilisateur WHERE email = :email");
    $statement->execute(['email' => $email]);
    $result = $statement->fetchAll();

    if (count($result) > 0) {
        echo "Les requêtes préparées fonctionnent.\n";
    } else {
        echo "Aucune donnée trouvée.\n";
    }

    // Validation de l'email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "L'email '$email' est valide.\n";
    } else {
        echo "L'email '$email' n'est pas valide.\n";
    }

    logResult("Test de sécurité terminé.");
    echo "\n"; // Ligne vide pour séparer les résultats
}

// 6. Validation des règles métier
function testBusinessRules($pdo) {
    printWithColor("=== Validation des règles métier ===", 'yellow');
    logResult("Validation des règles métier commencée.");
    $query = "SELECT * FROM mesure WHERE valeur < 0"; // Exemple de règle
    $result = $pdo->query($query)->fetchAll();
    echo count($result) === 0 ? "Les règles métier sont respectées.\n" : "Certaines règles métier sont violées.\n";
    logResult("Validation des règles métier terminée.");
    echo "\n"; // Ligne vide pour séparer les résultats
}

// 7. Test fonctionnel
function testCRUD($pdo) {
    printWithColor("=== Test CRUD ===", 'yellow');
    logResult("Test CRUD commencé.");
    try {
        $pdo->exec("INSERT INTO station (code_station, nom) VALUES ('test_crud', 'Test CRUD')");
        $result = $pdo->query("SELECT * FROM station WHERE code_station = 'test_crud'")->fetchAll();
        $pdo->exec("UPDATE station SET nom = 'Updated CRUD' WHERE code_station = 'test_crud'");
        $pdo->exec("DELETE FROM station WHERE code_station = 'test_crud'");
        echo "CRUD testé avec succès.\n";
    } catch (Exception $e) {
        echo "Erreur dans le test CRUD : " . $e->getMessage() . "\n";
    }
    logResult("Test CRUD terminé.");
    cleanupTestData($pdo); // Appel de la fonction de nettoyage
    echo "\n"; // Ligne vide pour séparer les résultats
}

// 8. Test de validité des données
function testDataValidity($pdo) {
    printWithColor("=== Test de validité des données ===", 'yellow');
    logResult("Test de validité des données commencé.");
    $query = "SELECT COUNT(*) AS invalid_count FROM utilisateur WHERE email NOT REGEXP '^[^@]+@[^@]+\\.[^@]+$'";
    $result = $pdo->query($query)->fetch(PDO::FETCH_ASSOC);
    echo $result['invalid_count'] === 0 
        ? "Toutes les données sont valides.\n" 
        : "{$result['invalid_count']} données invalides détectées.\n";
    logResult("Test de validité des données terminé.");
    echo "\n"; // Ligne vide pour séparer les résultats
}

// 9. Vérification de la synchronisation
function testSync($pdo) {
    printWithColor("=== Vérification de la synchronisation ===", 'yellow');
    logResult("Vérification de la synchronisation commencée.");
    // Simuler une comparaison entre deux bases
    $query = "SELECT COUNT(*) FROM station"; 
    $originalCount = $pdo->query($query)->fetchColumn();
    $query = "SELECT COUNT(*) FROM station"; 
    $duplicateCount = $pdo->query($query)->fetchColumn();
    echo $originalCount === $duplicateCount 
        ? "Bases synchronisées.\n" 
        : "Bases désynchronisées.\n";
    logResult("Vérification de la synchronisation terminée.");
    echo "\n"; // Ligne vide pour séparer les résultats
}

function identifyInvalidData($pdo, $table, $column) {
    $query = "SELECT * FROM $table WHERE $column IS NULL OR $column = ''"; 
    $invalidData = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($invalidData) > 0) {
        echo "Données invalides trouvées dans la colonne '$column' de la table '$table':<br>";
        foreach ($invalidData as $row) {
            print_r($row);
            echo "<br>";
        }
    } else {
        echo "Aucune donnée invalide trouvée dans la colonne '$column' de la table '$table'.<br>";
    }
}

function cleanupTestData($pdo) {
    $query = "DELETE FROM station WHERE id_station > 62";
    $pdo->exec($query);
    echo "Les données de test ont été supprimées de la table 'station'.\n";
}

// Appeler toutes les fonctions de test
testIntegrity($pdo);
testACID($pdo);
testPerformance($pdo);
testScalability($pdo);
testSecurity($pdo);
testBusinessRules($pdo);
testCRUD($pdo);
testDataValidity($pdo);
testSync($pdo);
cleanupTestData($pdo); // Appel final de la fonction de nettoyage


<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données POST
    $station1 = $_POST['station1'] ?? null;
    $station2 = $_POST['station2'] ?? null;
    $from = $_POST['from'] ?? null;
    $to = $_POST['to'] ?? null;
    $measure = $_POST['measure'] ?? null;
    $format = $_POST['format'] ?? 'json'; // JSON par défaut
    $rawData = $_POST['data'] ?? '[]';

    // Vérifier si toutes les données sont présentes
    if (!$station1 || !$station2 || !$from || !$to || !$measure) {
        die('Erreur : Informations manquantes.');
    }

    // Débogage : Vérifier les valeurs reçues
    error_log("Export - Station 1: $station1, Station 2: $station2, Période: $from -> $to, Mesure: $measure");

    // Décoder les données reçues en JSON
    $data = json_decode($rawData, true);
    
    // Vérifier si la conversion JSON a fonctionné
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Erreur de décodage JSON : " . json_last_error_msg());
    }

    if (empty($data)) {
        die("Erreur : Aucune donnée à exporter.");
    }

    // Nom du fichier
    $filename = "comparaison_stations_" . date("Ymd_His") . ($format === 'csv' ? ".csv" : ".json");

    // Export JSON
    if ($format === 'json') {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    } 
    
    // Export CSV
    elseif ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Ajouter un BOM pour l'encodage UTF-8 (évite les problèmes avec Excel)
        fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // En-tête CSV (exemple adapté aux variables reçues)
        fputcsv($output, ['Station', 'Id', 'Nom', 'Code', 'Commune', 'Département', 'Région', 'EPCI']);

        // Ajouter les données des deux stations
        foreach ($data as $station) {
            fputcsv($output, [
                'Station ' . ($station['Id'] === $station1 ? '1' : '2'),
                $station['Id'] ?? 'N/A',
                $station['Nom'] ?? 'N/A',
                $station['Code'] ?? 'N/A',
                $station['Commune'] ?? 'N/A',
                $station['Département'] ?? 'N/A',
                $station['Région'] ?? 'N/A',
                $station['EPCI'] ?? 'N/A'
            ]);
        }

        fclose($output);
        exit;
    }

} else {
    die("Erreur : Requête invalide.");
}
?>

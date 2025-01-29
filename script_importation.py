import requests
import mysql.connector
import time

# URL de base de l'API sans filtres spécifiques
base_url = "https://data.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm@public/records"
limit = 100

# Liste des identifiants de station et leurs noms
stations_data = [
    ("07005", "ABBEVILLE"), ("07015", "LILLE-LESQUIN"), ("07020", "PTE DE LA HAGUE"),
    ("07027", "CAEN-CARPIQUET"), ("07037", "ROUEN-BOOS"), ("07072", "REIMS-PRUNAY"),
    ("07110", "BREST-GUIPAVAS"), ("07117", "PLOUMANAC'H"), ("07130", "RENNES-ST JACQUES"),
    ("07139", "ALENCON"), ("07149", "ORLY"), ("07168", "TROYES-BARBEREY"), 
    ("07181", "NANCY-OCHEY"), ("07190", "STRASBOURG-ENTZHEIM"), ("07207", "BELLE ILE-LE TALUT"),
    ("07222", "NANTES-BOUGUENAIS"), ("07240", "TOURS"), ("07255", "BOURGES"),
    ("07280", "DIJON-LONGVIC"), ("07299", "BALE-MULHOUSE"), ("07314", "PTE DE CHASSIRON"),
    ("07335", "POITIERS-BIARD"), ("07434", "LIMOGES-BELLEGARDE"), ("07460", "CLERMONT-FD"),
    ("07471", "LE PUY-LOUDES"), ("07481", "LYON-ST EXUPERY"), ("07510", "BORDEAUX-MERIGNAC"),
    ("07535", "GOURDON"), ("07558", "MILLAU"), ("07577", "MONTELIMAR"), ("07591", "EMBRUN"),
    ("07607", "MONT-DE-MARSAN"), ("07621", "TARBES-OSSUN"), ("07627", "ST GIRONS"),
    ("07630", "TOULOUSE-BLAGNAC"), ("07643", "MONTPELLIER"), ("07650", "MARIGNANE"),
    ("07661", "CAP CEPET"), ("07690", "NICE"), ("07747", "PERPIGNAN"), ("07761", "AJACCIO"),
    ("07790", "BASTIA"), ("61968", "GLORIEUSES"), ("61970", "JUAN DE NOVA"), ("61972", "EUROPA"),
    ("61976", "TROMELIN"), ("61980", "GILLOT-AEROPORT"), ("61996", "NOUVELLE AMSTERDAM"),
    ("61997", "CROZET"), ("61998", "KERGUELEN"), ("67005", "PAMANDZI"), ("71805", "ST-PIERRE"),
    ("78890", "LA DESIRADE METEO"), ("78894", "ST-BARTHELEMY METEO"), ("78897", "LE RAIZET AERO"),
    ("78922", "TRINITE-CARAVEL"), ("78925", "LAMENTIN-AERO"), ("81401", "SAINT LAURENT"),
    ("81405", "CAYENNE-MATOURY"), ("81408", "SAINT GEORGES"), ("81415", "MARIPASOULA"),
    ("89642", "DUMONT D'URVILLE")
]

# Connexion à la base de données MySQL
conn = mysql.connector.connect(
    host="localhost",
    user="utilitaire",
    password="my_base_bd",
    database="projetunivers"
)
cursor = conn.cursor()

# Suppression des anciennes données dans les tables
def delete_old_data():
    # Désactiver les contraintes de clé étrangère
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0;")

    # Supprimer les données des tables dépendantes d'abord
    cursor.execute("DELETE FROM favoris")  # Supprimer les favoris qui dépendent de meteotheque
    cursor.execute("DELETE FROM mesure")    # Supprimer les mesures qui dépendent de station
    cursor.execute("DELETE FROM historique") # Supprimer l'historique qui dépend de utilisateur
    cursor.execute("DELETE FROM meteotheque") # Supprimer les données de meteotheque
    cursor.execute("DELETE FROM utilisateur")  # Supprimer les utilisateurs

    # Maintenant, nous pouvons tronquer les tables sans problème
    tables = ["station", "commune", "departement", "epci", "region", "coordonnees"]
    for table in tables:
        cursor.execute(f"TRUNCATE TABLE {table}")

    # Réactiver les contraintes de clé étrangère
    cursor.execute("SET FOREIGN_KEY_CHECKS = 1;")
    
    conn.commit()

# Exécution de la suppression
delete_old_data()

# Fonction pour construire l'URL pour chaque station en utilisant `numer_sta`
def build_url(station_id):
    return f"{base_url}?limit={limit}&refine=numer_sta%3A%22{station_id}%22"

# Insertion des données de station et de commune dans la structure ajustée
def insert_station_data(numer_sta, station_name, codegeo, libgeo, code_dep, nom_dept, code_reg, nom_reg, code_epci, nom_epci):
    cursor.execute("SELECT COUNT(*) FROM station WHERE code_station = %s", (numer_sta,))
    (station_exists,) = cursor.fetchone()
    if station_exists == 0:
        cursor.execute("""
        INSERT INTO station (code_station, nom, code_commune, code_departement, code_region, code_epci)
        VALUES (%s, %s, %s, %s, %s, %s)
        """, (numer_sta, station_name, codegeo, code_dep, code_reg, code_epci))

# Boucle pour chaque station
for station_id, station_name in stations_data:
    url = build_url(station_id)
    response = requests.get(url)

    if response.status_code == 200:
        data = response.json()
        results = data.get('results', [])

        if not results:
            print(f"Aucun résultat pour la station {station_id}")
            continue

        for result in results:
            numer_sta = result.get('numer_sta', 'Inconnu')
            coordonnees = result.get('coordonnees', {})
            lat = coordonnees.get('lat', 'Inconnu')
            lon = coordonnees.get('lon', 'Inconnu')
            codegeo = result.get('codegeo', 'Inconnu')
            libgeo = result.get('libgeo', 'Inconnu')
            code_dep = result.get('code_dep', 'Inconnu')
            nom_dept = result.get('nom_dept', 'Inconnu')
            code_reg = result.get('code_reg', 'Inconnu')
            nom_reg = result.get('nom_reg', 'Inconnu')
            code_epci = result.get('code_epci', 'Inconnu')
            nom_epci = result.get('nom_epci', 'Inconnu')

            # Insertion des données dans `commune`
            if codegeo and libgeo:
                cursor.execute("SELECT COUNT(*) FROM commune WHERE code_commune = %s", (codegeo,))
                (commune_exists,) = cursor.fetchone()
                if commune_exists == 0:
                    cursor.execute("INSERT INTO commune (code_commune, nom_commune) VALUES (%s, %s)", (codegeo, libgeo))

            # Insertion des coordonnées
            if codegeo and lat != 'Inconnu' and lon != 'Inconnu':
                cursor.execute("SELECT id_coordonnees FROM coordonnees WHERE latitude = %s AND longitude = %s", (lat, lon))
                result_coord = cursor.fetchone()
                if not result_coord:
                    cursor.execute("INSERT INTO coordonnees (latitude, longitude, code_commune) VALUES (%s, %s, %s)", (lat, lon, codegeo))

            # Insertion dans `departement`
            if code_dep and nom_dept:
                cursor.execute("SELECT COUNT(*) FROM departement WHERE code_departement = %s", (code_dep,))
                (dept_exists,) = cursor.fetchone()
                if dept_exists == 0:
                    cursor.execute("INSERT INTO departement (code_departement, nom_departement) VALUES (%s, %s)", (code_dep, nom_dept))

            # Insertion dans `epci`
            if code_epci and nom_epci:
                cursor.execute("SELECT COUNT(*) FROM epci WHERE code_epci = %s", (code_epci,))
                (epci_exists,) = cursor.fetchone()
                if epci_exists == 0:
                    cursor.execute("INSERT INTO epci (code_epci, nom_epci) VALUES (%s, %s)", (code_epci, nom_epci))

            # Insertion dans `region`
            if code_reg and nom_reg:
                cursor.execute("SELECT COUNT(*) FROM region WHERE code_region = %s", (code_reg,))
                (region_exists,) = cursor.fetchone()
                if region_exists == 0:
                    cursor.execute("INSERT INTO region (code_region, nom_region) VALUES (%s, %s)", (code_reg, nom_reg))

            # Insertion ou mise à jour de la station
            insert_station_data(numer_sta, station_name, codegeo, libgeo, code_dep, nom_dept, code_reg, nom_reg, code_epci, nom_epci)

            # Commit des changements
            try:
                conn.commit()
                print(f"Commit effectué pour la station {numer_sta} - {station_name}")
            except Exception as e:
                print(f"Erreur de commit pour la station {numer_sta} : {e}")

        # Pause pour éviter le blocage de l'API
        time.sleep(1)

    else:
        print(f"Erreur {response.status_code} pour la station {station_id}.")
        time.sleep(30)  # Attendre avant de réessayer

# Fermer la connexion
cursor.close()
conn.close()
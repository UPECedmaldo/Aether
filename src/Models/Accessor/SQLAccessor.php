<?php

namespace Sae\Models\Accessor;

use PDO;
use Sae\Config\Conf;

/**
 * Classe d'accès à la base de données
 */
class SQLAccessor {

    private static SQLAccessor $instance;

    private string $host;
    private int $port;
    private string $user;
    private string $password;
    private string $database;
    private ?PDO $pdo;

    private function __construct(string $host, int $port, string $user, string $password, string $database) {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        $this->pdo = null;
    }

    /**
     * Récupère une connexion à la base de données
     * @return PDO
     */
    public static function getConnection(): PDO {
        if (!isset(self::$instance))
            self::$instance = new SQLAccessor(Conf::$DB_HOST, Conf::$DB_PORT, Conf::$DB_USER, Conf::$DB_PASS, Conf::$DB_NAME);
        return self::$instance->getPdo();
    }

    public function getPdo(): PDO {
        if ($this->pdo == null)
            $this->pdo = new PDO("mysql:host={$this->host};port={$this->port};dbname={$this->database}", $this->user, $this->password);
        return $this->pdo;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getUser(): string {
        return $this->user;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getDatabase(): string {
        return $this->database;
    }

}
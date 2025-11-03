<?php

namespace Sae\Config;

class Conf {

    public static string $DB_HOST = "mysql_sae"; // Docker subnet
    public static string $DB_NAME = "sae"; // Database name
    public static string $DB_USER = "root"; // Database user
    public static string $DB_PASS = "butinfo"; // Database password
    public static int $DB_PORT = 3306;

    public static int $SESSION_TIMEOUT = 60 * 10; // 10 minutes

}
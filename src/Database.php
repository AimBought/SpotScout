<?php

declare(strict_types=1);

namespace App;

use PDO;

final class Database
{
    private string $username;
    private string $password;
    private string $host;
    private int $port;
    private string $name;

    public function __construct()
    {
        require_once dirname(__DIR__) . '/config.php';

        $this->username = DB_USERNAME;
        $this->password = DB_PASSWORD;
        $this->host = DB_HOST;
        $this->port = DB_PORT;
        $this->name = DB_NAME;
    }

    public function connect(): PDO
    {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s', $this->host, $this->port, $this->name);

        return new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}

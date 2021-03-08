<?php

namespace db;

class Connection 
{
    static function connect($config) 
    {
        $dsn = $config['DB_DRIVER'] . ":host=" . $config['DB_HOST'] . ";dbname=" . $config['DB_NAME'] . ";charset=" . $config['DB_CHARSET'];

        $opt = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD'], $opt);

        return $pdo;
    }
}
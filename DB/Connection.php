<?php

namespace DB;

class Connection 
{
    static function connect($config) 
    {
        if ( $config['DB_DRIVER'] == 'sqlite' ) {

            $dsn = "sqlite:" . $config['DB_PATH'];

            if ( !filesize($config['DB_PATH']) ) {
                throw new \Exception('There are no tables in the database!');
            }

            $config['DB_USER'] = null;
            $config['DB_PASSWORD'] = null;

        } else {
            $dsn = $config['DB_DRIVER'] . ":host=" . $config['DB_HOST'] . ";dbname=" . $config['DB_NAME'];
        }

        $opt = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $pdo = new \PDO($dsn, $config['DB_USER'], $config['DB_PASSWORD'], $opt);

        return $pdo;
    }
}
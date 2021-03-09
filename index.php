<?php


require_once 'functions.php';
$config = require_once 'config.php';
use Builders\MyQueryBuilder;

spl_autoload_register(function($classname){

    if (DIRECTORY_SEPARATOR == '/') {
        $classname = str_replace('\\', '/', $classname);
    }

    // dd($classname);

    $file = $classname . '.php';

    require_once $file;
});

dd(PDO::getAvailableDrivers());

$db = new MyQueryBuilder($config);

$db
    ->select(['*'])
    ->from('cards')
    


// $db->select(['user_id', 'title'])->from('cards')->orderBy('user_id');
// $db->insertInto('users', ['username' => 'Sam', 'email' => 'sam@mail.ru']);
// $res = $db->execute();
// print_r($res);

// $db->update('cards')->where('id', '=', 2)

// $db->select()->from('cards');
// $result = $db->execute();

// dd($result);

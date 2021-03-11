<?php

require_once 'functions.php';
$config = require_once 'config.php';
use DB\MyQueryBuilder;

spl_autoload_register(function($classname){

    if (DIRECTORY_SEPARATOR == '/') {
        $classname = str_replace('\\', '/', $classname);
    }

    $file = $classname . '.php';

    require_once $file;
});

// dd(PDO::getAvailableDrivers());

$db = new MyQueryBuilder($config);

$db 
    ->insertInto('cards', ['user_id' => 1, 'title' => 'test', 'd' => 'd']);
// $db
//     ->select(['*'])
//     ->from('admins')
//     ->where(
//         ['name', 'like', 'd%'],
//         ['name', 'like', 'h%', 'or']
//     )
//     ->orWhere('name', 'like', 'h%')
//     ->orderBy('name')
    // ->limit(3)
;

// $db
//     ->select(['permission_name' => 'permissions.name'])
//     ->from('roles')
//     ->innerJoin('roles_permissions')
//     ->on('roles.id', '=', 'roles_permissions.role_id')
//     ->innerJoin('permissions')
//     ->on('permissions.id', '=', 'roles_permissions.permission_id')
//     ->where('roles.slug', '=', 'super-admin')
// ;

// $db
//     ->insertInto('admins', [
//         'name' => 'Test admin 1',
//         'email' => 'Testadmin@gmail.com',
//         'email_verified_at' => '2021-02-28 19:22:06',
//         'password' => '$2y$10$fQyzP3sdw0NR3rA0rOO4yuUGQZgYPOmdS75hQiKM2MjwgdD/VlWJm',
//         'remember_token' => 'BOx2vxy8IB',
//         'created_at' => '2021-02-28 19:22:08',
//         'updated_at' => '2021-02-28 19:22:08',
//     ])
// ;

// $db->delete()->from('admins')->where('id', '=', 22);

// $db->update('admins', ['name' => 'Updated admin'])->where('id', '=', '17');

$result = $db->execute();

dd($result);
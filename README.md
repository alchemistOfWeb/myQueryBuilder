# myQueryBuilder

## begining

Чтобы начать работу с QueryBuilder вам нужно передать ему массив со следуюющим содержанием:
`
[
    'DB_DRIVER'       => 'mysql'|'psql'|'oci'|'sqlite',

    'DB_HOST'       => '127.0.0.1', 

    'DB_PORT'       => порт бд, 

    'DB_NAME'   => имя базы данных,
    
    'DB_USER'   => имя пользователя,

    'DB_PASSWORD'   => пароль пользователя,
    
    'DB_CHARSET'    => 'utf8',
]


`

Вот код создания нового объекта Запросопостроителя
`
    $db = new MyQueryBuilder($config);
`
For executing query and getting results:
`
    $result = $db->execute();
`

## SELECT

`
    $db->select()
`

`
    $db->select(['*'])
`

`
    $db->select(['field1', 'field2'])
`

`
    $db->select(['user_name' => 'users.name', 'users.id'])
`
### from & where

`
    $db->select()->from('users')->where('id', '=', 4);
`

`
    $db
        ->select()
        ->from('cars')
        ->where('speed', '>', 90)
        ->orWhere('mass', '<', '80');
`

`
    $db
        ->select()
        ->from('cars')
        ->where(['speed', '>', 90], ['mass', '<', '80', 'or']);
`
### orderBy

## UPDATE
## INSERT
## DELETE
## LIMIT
## JOINS

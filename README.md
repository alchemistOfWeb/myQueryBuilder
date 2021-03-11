# myQueryBuilder

- [beginning][id]: beginning
- [select][id]: select
    - [orderBe][id]: orderBy
- [limit][id]:limit
[where][id]: where

[update][update]

- [end](#end)

[id]: beginning
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
Для того чтобы выполнить подготовленный запрос и получить результаты используйте следующую конструкцию:
`
    $result = $db->execute();
`

[id]: select
## SELECT

Чтобы получить все поля можно использовать эту конструкцию
`
    $db->select()->from('table')
`
Или эту
`
    $db->select(['*'])->from('table')
`
Чтобы получить определённые поля:
`
    $db->select(['field1', 'field2'])
`
Поля с псевдонимами ('user.name' as 'user_name')
`
    $db->select(['user_name' => 'users.name', 'users.id'])
`

[id]: orderBy
### orderBy
Сортировка выборки:
`
    $db
        ->select()
        ->from('cars')
        ->orderBy('name', 'DESC') // 'ASC' по умолчанию

`

[id]: limit
## LIMIT
Вы также можете ограничить выборку или другой тип запроса следующим образом:
`
    $db
        ->select()
        ->from('table')
        ->limit(3) // получить 3 записи начиная с 0
`
или 
`
    $db
        ->select()
        ->from('table')
        ->limit(3, 5) // Получить 3 записи начиная с 5
`

[id]: where
## WHERE
Пример:
`
    $db->select()->from('users')->where('id', '=', 4);
`
Каждой следующей условной конструкии созданной с помощью метода where будет добавлен оператор AND
Чтобы добавить OR используйте метод orWhere
`
    $db
        ->select()
        ->from('cars')
        ->where('speed', '>', 90)
        ->where('mass', '<', '80');
`
Составлять запросы можно и так:
`
    $db
        ->select()
        ->from('cars')
        ->where(['speed', '>', 90], ['mass', '<', '80', 'or']);
`
Если вы хотите разместить ваше условие в скобках используйте callback
`
    $db
        ->select()
        ->from('posts')
        ->where('likes', '>', 10)
        ->orWhere(function($query){
            return $query
            ->where('subscribers', '>', 3)
            ->where('title', 'like', 'M%');
        });
`

[id]: update
## UPDATE
`
    $db
        ->update('posts', ['title' =>'new title', 'description' => 'new description])
        ->where

`

## INSERT
## DELETE
## JOINS
<a name="end"></a> 
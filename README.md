# myQueryBuilder

- [формулировка ТЗ](#tt)
- [beginning](#beginning)
- [select](#select)
    - [orderBy](#orderBy)
- [limit](#limit)
- [where](#where)
- [update](#update)
- [insert](#insert)
- [delete](#delete)
- [joins](#joins)
    - [inner join](#innerjoin)
    - [l/r join](#lrjoin)
    - [many to many](#mtm)


## формулировка ТЗ
<a name="tt"></a> 

Задание

QueryBuilder (ООП без использования framework)

Разработать обертку для работы с разными СУБД в едином формате.

Принцип такой: есть некий массив, в котором задается конфигурация соединения: тип бд, логин, пароль, хост, имя базы.

Этот массив передается в конструктор класса, создается новый объект.

Дальше с полученным объектом можно выполнять последовательность действий для формирования запроса.

Например:

`
$db = new MyQueryBuilder($config);

$db->select($columns)->from($table)->where($a,'>',1)->limit(1);

$db->execute();
`

При создании экземпляра класса создается подключение, при вызове цепочки методов внутри объекта ведется генерация запроса, при вызове execute запрос отправляется на сервер и непосредственно выполняется.

Необходимо реализовать базовые методы CRUD: INSERT, UPDATE,DELETE,SELECT, условия, сортировки, лимиты. Дополнительно можно реализовать Join и прочие конструкции SQL.

Кроме того, надо понимать, что запрос может содержать sql-инъекции, нужно обязательно его фильтровать, при генерации запроса обязательно реализовать проверку передаваемых аргументов, чтобы они соответствовали типу и не содержали мусора.


## begining
<a name="beginning"></a> 

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

## SELECT
<a name="select"></a> 

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

### orderBy
<a name="orderBy"></a> 

Сортировка выборки:
`

    $db
        ->select()
        ->from('cars')
        ->orderBy('name', 'DESC') // 'ASC' по умолчанию

`

## LIMIT
<a name="limit"></a> 

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

## WHERE
<a name="where"></a> 

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

## UPDATE
<a name="update"></a> 

`
    $db
        ->update('posts', ['title' =>'new title', 'description' => 'new description])
        ->where
`

## INSERT
<a name="insert"></a> 
insertInto и limit - единственные методы пригодные для вставки

`

    $db
        ->insertInto('posts', ['title' =>'title', 'description' => 'description])

`

## DELETE
<a name="delete"></a> 

`

    $db
        ->delete()
        ->from('posts')
        ->where('id', '=', 16)


`

## JOINS
<a name="joins"></a> 

### inner joins
<a name="innerjoin"></a> 

`
    $db
        ->select()
        ->from(users)
        ->innerJoin('contacts')
        ->on('users.id', '=', 'contacts.user_id')
        ->innerJoin('orders')
        ->on('users.id', '=', 'orders.user_id')
        
`

### left/right joins
<a name="lrjoin"></a> 

`
    $db
        ->select()
        ->from(users)
        ->leftJoin('posts')
        ->on('users.id', '=', 'posts.user_id')
`

`
    $db
        ->select()
        ->from(users)
        ->rightJoin('posts')
        ->on('users.id', '=', 'posts.user_id')
        
`



### many to many
<a name="mtm"></a> 

`

    $db
        ->select(['post_name' => 'posts.name'])
        ->from('posts')
        ->innerJoin('posts_categories')
        ->on('posts.id', '=', 'posts_categories.post_id')
        ->innerJoin('categories')
        ->on('categories.id', '=', 'posts_categories.category_id')
        ->where('posts.slug', '=', 'super-post')

`
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

```php

    $db = new MyQueryBuilder($config);

    $db->select($columns)->from($table)->where($a,'>',1)->limit(1);

    $db->execute();

```

При создании экземпляра класса создается подключение, при вызове цепочки методов внутри объекта ведется генерация запроса, при вызове execute запрос отправляется на сервер и непосредственно выполняется.

Необходимо реализовать базовые методы CRUD: INSERT, UPDATE,DELETE,SELECT, условия, сортировки, лимиты. Дополнительно можно реализовать Join и прочие конструкции SQL.

Кроме того, надо понимать, что запрос может содержать sql-инъекции, нужно обязательно его фильтровать, при генерации запроса обязательно реализовать проверку передаваемых аргументов, чтобы они соответствовали типу и не содержали мусора.


## begining
<a name="beginning"></a> 

Чтобы начать работу с QueryBuilder вам нужно передать ему массив со следующим содержанием:


```php
[

    'DB_DRIVER'       => 'mysql',  //|'psql'|'oci'|'sqlite'|'sybase'|'mssql'|'firebird'|anotherdriver

    'DB_HOST'       => '127.0.0.1', 

    'DB_PORT'       => порт бд, 

    'DB_NAME'   => имя базы данных,
    
    'DB_USER'   => имя пользователя,

    'DB_PASSWORD'   => пароль пользователя,
];
```


или если вы используете sqlite

```php
[

    'DB_DRIVER' => 'sqlite',

    'DB_PATH' => '',
];
```

Вот код создания нового объекта Запросопостроителя

```php
$db = new MyQueryBuilder($config);
```


Для того чтобы выполнить подготовленный запрос и получить результаты используйте следующую конструкцию:

```php
$result = $db->execute();
```

#### Поддерживаемые базы данных:
Для подключения используется PDO, так что в теории должны работать все движки поддерживаемые данным расширением

**mysq** (проверено)

**pgsql** (проверено)

**mssql** (MS SQL Server) (не точно)

**sybase** (не точно)

**sqlite** (не точно)

**oci** (oracle) (не точно)

**firebird** (не точно)





## SELECT
<a name="select"></a> 

Чтобы получить все поля можно использовать эту конструкцию
```php
$db->select()->from('table')
```


Или эту
```php
$db->select(['*'])->from('table')
```


Чтобы получить определённые поля:


```php
$db->select(['field1', 'field2'])
```


Поля с псевдонимами ('user.name' as 'user_name')


```php
$db->select(['user_name' => 'users.name', 'users.id'])
```


### orderBy
<a name="orderBy"></a> 

Сортировка выборки:

```php
$db
    ->select()
    ->from('cars')
    ->orderBy('name', 'DESC'); // 'ASC' по умолчанию
```


## LIMIT
<a name="limit"></a> 

Вы также можете ограничить выборку или другой тип запроса следующим образом:

```php
$db
    ->select()
    ->from('table')
    ->limit(3); // получить 3 записи начиная с 0
```

или 

```php
$db
    ->select()
    ->from('table')
    ->limit(3, 5); // Получить 3 записи начиная с 5
```

## WHERE
<a name="where"></a> 

Пример:

```php
$db->select()->from('users')->where('id', '=', 4);
```


Каждой следующей условной конструкии созданной с помощью метода where будет добавлен оператор AND
Чтобы добавить OR используйте метод orWhere

```php
$db
    ->select()
    ->from('cars')
    ->where('speed', '>', 90)
    ->where('mass', '<', '80');
```

Составлять запросы можно и так:

```php
$db
    ->select()
    ->from('cars')
    ->where(['speed', '>', 90], ['mass', '<', '80', 'or']);
```

Если вы хотите разместить ваше условие в скобках используйте callback

```php
$db
    ->select()
    ->from('posts')
    ->where('likes', '>', 10)
    ->orWhere(function($query){
        return $query
        ->where('subscribers', '>', 3)
        ->where('title', 'like', 'M%');
    });
```

## UPDATE
<a name="update"></a> 

```php
$db
    ->update('posts', ['title' =>'new title', 'description' => 'new description])
    ->where('posts.id', '=', 89);
```

## INSERT
<a name="insert"></a> 
insertInto - единственный метод пригодный для вставки. т.е. после него можно только совершить запрос методом 'execute()'


```php
$db
    ->insertInto('posts', ['title' =>'title', 'description' => 'description]);
```


## DELETE
<a name="delete"></a> 


```php
$db
    ->delete()
    ->from('posts')
    ->where('id', '=', 16);
```



## JOINS
<a name="joins"></a> 

### inner joins
<a name="innerjoin"></a> 

```php
$db
    ->select()
    ->from(users)
    ->innerJoin('contacts')
    ->on('users.id', '=', 'contacts.user_id')
    ->innerJoin('orders')
    ->on('users.id', '=', 'orders.user_id');
```


### left/right joins
<a name="lrjoin"></a> 

```php
$db
    ->select()
    ->from(users)
    ->leftJoin('posts')
    ->on('users.id', '=', 'posts.user_id');
```

```php
$db
    ->select()
    ->from(users)
    ->rightJoin('posts')
    ->on('users.id', '=', 'posts.user_id');
```      


### many to many
<a name="mtm"></a> 


```php
$db
    ->select(['post_name' => 'posts.name'])
    ->from('posts')
    ->innerJoin('posts_categories')
    ->on('posts.id', '=', 'posts_categories.post_id')
    ->innerJoin('categories')
    ->on('categories.id', '=', 'posts_categories.category_id')
    ->where('posts.slug', '=', 'super-post');
```

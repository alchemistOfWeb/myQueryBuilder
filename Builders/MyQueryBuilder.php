<?php

namespace Builders;
use DB\Connection;

class MyQueryBuilder 
{
    const ASC_ORDER = 'ASC';
    const DESC_ORDER = 'DESC';

    const QUERY_SELECT = 1;
    const QUERY_INSERT = 2;
    const QUERY_UPDATE = 4;
    const QUERY_WHERE = 8;
    const QUERY_WHEN = 16;

    /**
     * @param string $query
     */
    private string $query = '';

    /**
     * @param array $params
     */
    private array $params = [];

    /**
     * @param mixed $result
     */
    // private $result;

    /**
     * @param string $query_type
     */
    private $query_type = 0;
    
    /**
     * @param string $builder
     */
    private string $call_type;
    
    /**
     * @param $pdo
     */
    private $pdo;


    public function __construct(array $config)
    {
        $this->pdo = Connection::connect($config);
    }

    public function __call($name, $arguments)
    {
        if ( ( ($this->call_type & static::QUERY_WHEN) == true ) && $name != 'when') {
            $this->call_type ^=  static::QUERY_WHEN;
            $this->query .= ' END';
        } elseif ($name == 'when') {
            $this->call_type ^= static::QUERY_WHEN;
        }
    }

    public function execute()
    {
        $stmt = $this->pdo->prepare($this->query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute($this->params);

        $this->query = '';
        $this->params = [];

        return $stmt->fetchAll();

        // if ($this->query_type === static::QUERY_INSERT) {
        // }

        if ($this->query_type === static::QUERY_SELECT) {
            return $stmt->fetchAll();
        }
    }

    public function select(array $fields = ['*'])
    {
        $this-> query_type = static::QUERY_SELECT;

        $this->query .= ' SELECT ' . implode(', ', $fields);
        return $this;
    }

    public function insertInto(string $table, array $params)
    {
        $this->query_type = static::QUERY_INSERT;
        $tmp = [];

        foreach ($params as $key => $val) {
            $tmp[':' . $key] = $val;
        }

        $this->params = $params;

        $insert = ' INSERT INTO ' . $table;

        $fields = implode(', ', array_keys($params));
        $values = implode(', ', array_keys($tmp));

        $this->query .= ' INSERT INTO ' . $table . '(' . $fields . ')' . ' VALUES(' . $values . ')';
        
        return $this;
    }

    public function update($table, $params)
    {
        $this->query_type = static::QUERY_UPDATE;

        $set_section = implode(', ', array_map( function () { return '?'; }, $params ) );

        $this->params = array_merge($this->params, $params);

        $this->query = 'UPDATE ' . $table . ' SET ' .  $set_section;

        return $this;
    }

    public function delete()
    {
        $this->query .= ' DELETE';
        return $this;
    }

    public function from(string $table)
    {
        $this->query .= ' FROM ' . $table;
        return $this;
    }

    public function where($first, $operator, $second)
    {
        $this->query_type &= static::QUERY_WHERE;

        $this->query .= ' WHERE ' . $first . ' ' . $operator . ' ' . $second;

        return $this;
    }

    public function whereIn($field, array $params)
    {
        $this->query_type &= static::QUERY_WHERE;

        $inBlock = implode(', ', array_map( function () { return '?'; }, $params ) );

        $this->query .= ' WHERE ' . $field . ' IN (' . $inBlock . ')';

        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function whereNotIn($field, array $params)
    {
        $this->query_type &= static::QUERY_WHERE;

        $inBlock = implode(', ', array_map( function () { return '?'; }, $params ) );

        $this->query .= ' WHERE ' . $field . ' NOT IN (' . $inBlock . ')';

        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function whereBetween(string $field, int $first, int $second)
    {
        $this->query_type &= static::QUERY_WHERE;

        $this->query .= ' WHERE ' . $field . ' BETWEEN ' . $first . ' AND ' . $second;

        $this->params = array_merge( $this->params, array($first, $second) );

        return $this;
    }

    public function whereRaw(string $raw, array $params)
    {
        $this->query_type &= static::QUERY_WHERE;

        $this->query .= ' WHERE ' . $raw;

        $this->params = array_merge( $this->params, $params );

        return $this;
    }

    public function and($first, $operator, $second)
    {
        $this->query .= ' AND ' . $first . ' ' . $operator . ' ' . $second;

        return $this;
    }

    public function or($first, $operator, $second)
    {
        $this->query .= ' OR ' . $first . ' ' . $operator . ' ' . $second;

        return $this;
    }

    public function not($first, $operator, $second) 
    {
        $this->query .= ' NOT ';
    }

    public function innerJoin($table, $other_table)
    {
        $this->query .= $table . ' INNER JOIN ' . $other_table;

        return $this;
    }

    public function leftJoin($table, $other_table)
    {
        $this->qeury .= $table . ' LEFT JOIN ' . $other_table;

        return $this;
    }

    public function rightJoin($table, $other_table)
    {
        $this->qeury .= $table . ' RIGHT JOIN ' . $other_table;

        return $this;
    }

    public function on(string $first, string $operator, string $second)
    {
        $this->query .= ' ON ' . $first . ' ' . $operator . ' ' . $second;

        return $this;
    }

    public function orderBy(string $field, $type = MyQueryBuilder::ASC_ORDER)
    {
        $this->query .= ' ORDER BY ' . $field . ' ' . $type;

        return $this;
    }

    public function limit($num, $start = 0)
    {
        $this->query .= ' LIMIT ' . $start . ', ' . $num;

        return $this;
    }

    public function when(string $condition, callable $then)
    {
        $when_section = $condition;

        $this->query .= ' WHEN ' . $when_section . ' ';
        call_user_func($then, $this);

        $this->call_type = static::QUERY_WHEN;
    }
}
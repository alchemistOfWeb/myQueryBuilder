<?php

namespace Builders;

use Closure;
use DB\Connection;

/**
 * 
 * @method MyQueryBuilder where(string $first, string $operator, string|int $second)
 * @method MyQueryBuilder where(callable $subwhere)
 * @method MyQueryBuilder where(array ...$condition_list)
 * 
 * @method MyQueryBuilder orWhere(string $first, string $operator, string|int $second)
 * @method MyQueryBuilder orWhere(callable $subwhere)
 * @method MyQueryBuilder orWhere(array ...$condition_list)
 * 
 */
class MyQueryBuilder 
{
    const ASC_ORDER = 'ASC';
    const DESC_ORDER = 'DESC';

    const QUERY_SELECT = 'select';
    const QUERY_DELETE = 'delete';
    const QUERY_UPDATE = 'update';
    const QUERY_INSERT = 'insert';

    /**
     * @param string $query
     */
    private string $query = '';

    /**
     * @param array $params
     */
    private array $params = [];

    /**
     * @param array $parts
     */
    private $parts = [
        'columns'   => [],
        'from'      => '',
        'joins'     => [],
        'wheres'    => [],
        'groups'    => [],
        'orders'    => [],
        'limit'     => null,
    ];

    /**
     * @param string $query_type
     */
    private $query_type = 0;
    
    /**
     * @param string $builder
     */
    private string $call_type;
    
    /**
     * @param $connection
     */
    private $connection;


    private $grammar;


    private array $columns = [];
    
    private string $from = '';

    private array $joins = [];
    
    private array $wheres = [];
    
    private array $groups = [];

    private array $orders = [];

    private int $limit;


    /**
     * All of the available clause operators.
     *
     * @var string[]
     */
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'not rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];


    public function __construct(array|\PDO $con)
    {
        if ($con instanceof \PDO) {
            $this->connection = $con;
        } else {
            $this->connection = Connection::connect($con);
        }

    }

    /**
     * 
     */
    public function __call($name, $arguments)
    {
        // if ( ( ($this->call_type & static::QUERY_WHEN) == true ) && $name != 'when') {
        //     $this->call_type ^=  static::QUERY_WHEN;
        //     $this->query .= ' END';
        // } elseif ($name == 'when') {
        //     $this->call_type ^= static::QUERY_WHEN;
        // }

        $is_where = ($name == 'where');

        if ($is_where) {
            // if ( $arguments[0] instanceof Closure ) {
            //     return $this->whereNested($arguments[0], $arguments[3]);
            // }
            if ( is_string($arguments[0]) ) {
                return call_user_func([$this => 'where'], $arguments);
            }
            if ( is_array($arguments[0]) ) {
                return call_user_func([$this => 'whereConditions'], $arguments);
            }

        }
    }

    public function getQuery() 
    {
        return $this->query;
    }

    private function build(array $parts) 
    {
        foreach($parts as $part) {
            ($part . 'Builder')::build($part);
        }
    }

    public function execute()
    {
        ///
        $qeury = $this->builder->build($this->parts);
        ///

        $stmt = $this->connection->prepare($this->query);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute($this->params);

        $this->query = '';
        $this->params = [];

        return $stmt->fetchAll();

        // if ($this->query_type === static::QUERY_INSERT) {
        // }

        // if ($this->query_type === static::QUERY_SELECT) {
        //     return $stmt->fetchAll();
        // }
    }

    public function select(...$fields = ['*'])
    {
        // $this-> query_type = static::QUERY_SELECT;

        $fields = is_array($fields) ? $fields : func_get_args();

        // $this->query .= ' SELECT ' . implode(', ', $fields);

        foreach($fields as $alias => $field) {
            if ( is_string($alias) ) {
                $this->columns[] = $field . ' as ' . $alias;
            } else {
                $this->columns[] = $field;
            }
        }

        return $this;
    }

    public function insertInto(string $table, array $params)
    {
        // $this->query_type = static::QUERY_INSERT;
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
        // $this->query_type = static::QUERY_UPDATE;

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
        // $this->query .= ' FROM ' . $table;

        $this->from = $table;

        return $this;
    }

    public function getWheres()
    {
        return $this->wheres;
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * @ignore
     * @param string $col
     * @param string $operator
     * @param string|integer $second
     * @param string $boolean = 'AND'|'OR'
     * @return $this
     */
    public function where(string $col, string $operator = null, string|int $val = null, string $boolean = 'AND')
    {
        if ( $col instanceof Closure ) {
            return $this->whereNested($col, $boolean);
        }

        $this->bindings['where'][] = $val;

        $this->wheres[] = [$col, $operator, $val, $boolean];

        return $this;
    }

    /**
     * @ignore
     */
    public function orWhere($col, $operator, $val) 
    {
        return $this->where($col, $operator, $val, 'OR');
    }

    /**
     * overloaded where method
     * @param array ...$condition_list
     * 
     * @return $this
     */
    public function whereConditions(array ...$condition_list)
    {
        foreach ($condition_list as $val) {
            $this->where($val[0], $val[1], $val[2], isset($val[3]) ? $val[3] : 'AND');
        }

        return $this;
    }

    /**
     * overloaded where method
     * @param Closure $whereNested
     * @param string $boolean
     * 
     * @return $this
     */
    public function whereNested(Closure $callback, string $boolean = 'AND')
    {
        $whereNested = call_user_func($callback, new static($this->connection) );

        $this->wheres[] = $whereNested->getWheres();

        $this->params = array_merge($this->params, $whereNested->getParams());

        return $this;
    }

    public function whereIn(string $col, array $values, string $boolean = 'and', bool $not = false)
    {
        // $this->query_type &= static::QUERY_WHERE;

        // $inBlock = implode(', ', array_map( function () { return '?'; }, $params ) );

        // $this->query .= ' WHERE ' . $col . ' IN (' . $inBlock . ')';

        // $this->params = array_merge($this->params, $params);

        $operator = $not ? 'NOT IN' : 'IN';

        $this->bindings['where'] = array_merge($this->bindings, $values);

        $this->wheres[] = [$col, $operator, $values, $boolean];

        return $this;
    }

    public function whereNotIn(string $col, array $values)
    {
        return $this->whereIn($col, $values, 'and', true);
    }

    public function orWhereIn(string $col, array $values)
    {
        return $this->whereIn($col, $values, 'or');
    }

    public function orWhereNotIn(string $col, array $values)
    {
        return $this->whereIn($col, $values, 'or', true);
    }

    public function whereBetween(string $col, int $first, int $second, string $boolean = 'AND', bool $not = false)
    {
        $values = array($first, $second);

        $this->bindings['where'] = array_merge( $this->bindings['where'], $values );

        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        $this->wheres[] = [$col, $operator, $values, $boolean];

        return $this;
    }

    public function whereNotBetween(string $col, int $first, int $second)
    {
        return $this->whereBetween($col, $first, $second, 'AND', true);
    }

    public function orWhereBetween(string $col, int $first, int $second)
    {
        return $this->whereBetween($col, $first, $second, 'OR');
    }

    public function orWhereNotBetween(string $col, int $first, int $second)
    {
        return $this->whereBetween($col, $first, $second, 'OR', true);
    }

    public function whereRaw(string $raw, array $params)
    {
        $this->query .= ' WHERE ' . $raw;

        $this->params = array_merge( $this->params, $params );

        return $this;
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
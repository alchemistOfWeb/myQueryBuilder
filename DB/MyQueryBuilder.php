<?php

namespace DB;

use Closure;
use DB\Compilers\Compiler;
use DB\Compilers\CompilerDelete;
use DB\Compilers\CompilerInsert;
use DB\Compilers\CompilerSelect;
use DB\Compilers\CompilerUpdate;
use DB\Exceptions\BuilderException;
use Exception;

/**
 * 
 * @method MyQueryBuilder where(string $first, string $operator, string|int $second)
 * @method MyQueryBuilder where(Closure $subwhere)
 * @method MyQueryBuilder where(array ...$condition_list)
 * 
 * method MyQueryBuilder orWhere(string $first, string $operator, string|int $second)
 * @method MyQueryBuilder orWhere(Closure $subwhere)
 * @method MyQueryBuilder orWhere(array ...$condition_list)
 * 
 */
class MyQueryBuilder 
{
    const ASC_ORDER = 'ASC';
    const DESC_ORDER = 'DESC';

    /**
     * @param array $params
     */
    private array $params = [];

    /**
     * These are the variables which can be gotten but cannot be changed
     * @param array $available
     */
    private $available = [
        'columns',
        'table',
        'joins',
        'wheres',
        'groups',
        'orders',
        'limit',
        'params',
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
     * @param \PDO $connection
     */
    private $connection;

    /**
     * @param Compiler $compiler
     */
    private $compiler = null;


    private array $columns = [];
    
    private string $table = '';

    private array $joins = [];
    
    private array $wheres = [];
    
    private array $groups = [];

    private array $orders = [];

    private array $limit = [];


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

    /**
     * @param array|\PDO $con
     * 
     */
    public function __construct($con)
    {
        if ($con instanceof \PDO) {
            $this->connection = $con;
        } else {
            $this->connection = Connection::connect($con);
        }

    }

    public function __get($name)
    {
        if ( in_array($name, $this->available) ) {
            return $this->$name;
        }
    }

    /**
     * 
     */
    public function __call($name, $arguments)
    {
        if ($name == 'where') {

            if ( is_string($arguments[0]) ) {
                return $this->where(...$arguments);
            }
            if ( is_array($arguments[0]) ) {
                return $this->whereConditions(...$arguments);
            }
            
        }
    }

    /**
     * @return string query
     */
    public function getQuery() 
    {
        return $this->query;
    }

    /**
     * 
     */
    public function execute()
    {
        $query = $this->compiler->toSql($this);
        // dd($query);
        // dd($this->params);
        $stmt = $this->connection->prepare($query);
        
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $stmt->execute($this->params);

        if ($this->compiler instanceof CompilerSelect) {
            $results = $stmt->fetchAll();
        } else {
            $results = (bool)$stmt;
        }

        $this->params = [];
        $this->compiler = null;

        return $results;
    }

    /**
     * @param $fields = ['*'] 
     * 
     */
    public function select($fields = ['*'])
    {
        if ($this->compiler) {
            throw new BuilderException('This query already have ' . get_class($this->compiler));
        }

        $this->compiler = new CompilerSelect();

        $fields = is_array($fields) ? $fields : func_get_args();

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
        if ($this->compiler) {
            throw new BuilderException('This builder already have compiler ' . get_class($this->compiler));
        }

        $this->compiler = new CompilerInsert();

        $this->params = array_values($params);

        $this->table = $table;

        $this->columns = array_keys($params);
        
        return $this;
    }

    public function update($table, $params)
    {
        if ($this->compiler) {
            throw new BuilderException('This builder already have compiler ' . get_class($this->compiler));
        }

        $this->compiler = new CompilerUpdate();

        $this->table = $table;

        $this->params = array_values($params);

        $this->columns = array_keys($params);

        return $this;
    }

    public function delete()
    {
        if ($this->compiler) {
            throw new BuilderException('This builder already have compiler ' . get_class($this->compiler));
        }

        $this->compiler = new CompilerDelete();

        return $this;
    }

    public function from(string $table)
    {
        $this->table = $table;

        return $this;
    }


    public function getParams()
    {
        return $this->params;
    }

    /**
     * @ignore
     * @param string $col
     * @param string $operator
     * @param string|int $second
     * @param string $boolean = 'AND'|'OR'
     * @return $this
     */
    private function where(string $col, string $operator = null, $val = null, string $boolean = 'AND')
    {
        if ( $col instanceof Closure ) {
            return $this->whereNested($col, $boolean);
        }

        $this->params[] = $val;

        $this->wheres[] = [$col, $operator, $val, $boolean];

        return $this;
    }

    /**
     * @param string $col
     * @param string $operator
     * @param $val
     */
    public function orWhere(string $col, string $operator, $val) 
    {
        return $this->where($col, $operator, $val, 'OR');
    }

    /**
     * overloaded where method
     * @param array ...$condition_list
     * 
     * @return $this
     */
    private function whereConditions(array ...$condition_list)
    {
        foreach ($condition_list as $val) {
            $this->where($val[0], $val[1], $val[2], isset($val[3]) ? $val[3] : 'AND');
        }

        return $this;
    }

    /**
     * overloaded where method
     * @param Closure $whereNested
     * @param string $boolean = AND (AND|OR)
     * 
     * @return $this
     */
    private function whereNested(Closure $callback, string $boolean = 'AND')
    {
        $whereNested = call_user_func($callback, new static($this->connection) );

        $this->wheres[] = $whereNested->wheres;

        $this->params = array_merge($this->params, $whereNested->params);

        return $this;
    }

    /**
     * @param string $col
     * @param array $values
     * @param string $boolean = 'and'
     * @param bool $not = false
     */
    public function whereIn(string $col, array $values, string $boolean = 'and', bool $not = false)
    {
        $operator = $not ? 'NOT IN' : 'IN';

        $this->params = array_merge($this->params, $values);

        $this->wheres[] = [$col, $operator, $values, $boolean];

        return $this;
    }

    /**
     * @param string $col
     * @param array $values
     */
    public function whereNotIn(string $col, array $values)
    {
        return $this->whereIn($col, $values, 'AND', true);
    }

    /**
     * @param string $col
     * @param array $values
     */
    public function orWhereIn(string $col, array $values)
    {
        return $this->whereIn($col, $values, 'OR');
    }

    /**
     * @param string $col
     * @param array $values
     */
    public function orWhereNotIn(string $col, array $values)
    {
        return $this->whereIn($col, $values, 'OR', true);
    }

    /**
     * @param string $col
     * @param int $first 
     * @param int $second
     * @param string $boolean = 'AND'
     * @param bool $not = false
     */
    public function whereBetween(string $col, int $first, int $second, string $boolean = 'AND', bool $not = false)
    {
        $values = array($first, $second);

        $this->params = array_merge( $this->params, $values );

        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        $this->wheres[] = [$col, $operator, $values, $boolean];

        return $this;
    }

    /**
     * @param string $col
     * @param int $first 
     * @param int $second
     */
    public function whereNotBetween(string $col, int $first, int $second)
    {
        return $this->whereBetween($col, $first, $second, 'AND', true);
    }

    /**
     * @param string $col
     * @param int $first 
     * @param int $second
     */
    public function orWhereBetween(string $col, int $first, int $second)
    {
        return $this->whereBetween($col, $first, $second, 'OR');
    }

    /**
     * @param string $col
     * @param int $first 
     * @param int $second
     */
    public function orWhereNotBetween(string $col, int $first, int $second)
    {
        return $this->whereBetween($col, $first, $second, 'OR', true);
    }

    /**
     * @param string $table
     */
    public function innerJoin(string $table)
    {
        $this->joins[] = ['table' => $table, 'type' => 'INNER'];

        return $this;
    }

    /**
     * @param string $table
     */
    public function leftJoin(string $table)
    {
        $this->joins[] = ['table' => $table, 'type' => 'LEFT'];

        return $this;
    }

    /**
     * @param string $table
     */
    public function rightJoin(string $table)
    {
        $this->joins[] = ['table' => $table, 'type' => 'RIGHT'];

        return $this;
    }

    /**
     * @param string $first
     * @param string $operator = null
     * @param string $second = null
     * @param string $boolean = AND
     */
    public function on($first, string $operator = null, string $second = null, string $boolean = 'AND')
    {
        if ($first instanceof Closure) {
            return $this->onNested($first, $boolean);
        }

        $this->joins[count($this->joins) - 1]['on'][] = [$first, $operator, $second, $boolean];

        return $this;
    }

    /**
     * @param string $first
     * @param string $operator = null
     * @param string $second = null
     */
    public function orOn($first, string $operator = null, string $second = null)
    {
        $this->on($first, $operator, $second, 'OR');

        return $this;
    }

    /**
     * @param string $column
     * @param string $order = ASC (ASC|DESC)
     */
    public function orderBy(string $column, $order = 'ASC')
    {
        $order = strtoupper($order);

        if ($order != 'ASC' && $order != 'DESC') {
            throw new BuilderException('unvalid parameter $order');
        }

        $this->orders[] = ['column' => $column, 'order' => $order];

        return $this;
    }

    /**
     * @param int $num
     * @param int $start = 0
     */
    public function limit(int $num, int $start = 0)
    {
        $this->limit = [$start, $num];

        $this->params[] = $start;
        $this->params[] = $num;

        return $this;
    }
}
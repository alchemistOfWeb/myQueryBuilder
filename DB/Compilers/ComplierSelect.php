<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

class CompilerSelect extends Compiler
{
    public function toSql(MyQueryBuilder $builder)
    {
        $table = $builder->table;

        $joins = $this->compileJoins($builder->joins);

        $where = $this->compileWheres($builder->wheres);

        $orders = empty($builder->orders) 
            ? '' 
            : 'ORDER BY' . implode(', ', array_map( function($val){ return $val['col'] . ' ' . $val['order']; }, $builder->orders) )
        ;

        $limit = 'LIMIT ' . implode(', ', $builder->limit);

        $sql = "SELECT FROM {$table} {$joins} {$where} {$orders} {$limit}";
    }
}
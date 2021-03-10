<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

class CompilerSelect extends Compiler
{
    /**
     * @param MyQueryBuilder $builder
     */
    public function toSql(MyQueryBuilder $builder)
    {
        $columns = implode(', ', $builder->columns);

        $table = $builder->table;

        $joins = $this->compileJoins($builder->joins);

        $where = $this->compileWheres($builder->wheres);
        
        $orders = !$builder->orders
            ? '' 
            : ' ORDER BY ' . implode(', ', array_map( function($val){ return $val['column'] . ' ' . $val['order']; }, $builder->orders) )
        ;
        
        $limit = $this->compileLimit($builder->limit);

        $sql = "SELECT {$columns} FROM {$table}{$joins}{$where}{$orders}{$limit}";

        return $sql;
    }
}
<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

class CompilerUpdate extends Compiler
{
    public function toSql(MyQueryBuilder $builder)
    {
        $table = $builder->table;

        $columns = implode( ', ', array_map(function($val) {return $val . '=?';}, $builder->columns) );

        $where = $this->compileWheres($builder->wheres);

        $sql = "UPDATE {$table} SET {$columns} {$where}";

        return $sql;
    }
}
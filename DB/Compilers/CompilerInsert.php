<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

class CompilerInsert extends Compiler
{
    public function toSql(MyQueryBuilder $builder)
    {
        $values = implode(', ', array_map( function () { return '?'; }, $builder->columns) );

        $table = $builder->table;

        $columns = implode(', ', $builder->columns);

        $limit = $this->compileLimit($builder->limit);

        $sql = "INSERT INTO {$table}({$columns}) VALUES({$values}){$limit}";

        return $sql;
    }
}
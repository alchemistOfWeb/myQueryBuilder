<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

class CompilerDelete extends Compiler
{
    public function toSql(MyQueryBuilder $builder)
    {
        $from = 'FROM ' . $builder->table;

        $joins = $this->compileJoins($builder->joins);

        $where = $this->compileWheres($builder->wheres);

        $limit = $this->compileLimit($builder->limit);

        $sql = "DELETE {$from}{$joins}{$where}{$limit}";

        return $sql;
    }
}
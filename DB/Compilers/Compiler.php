<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

abstract class Compiler
{
    abstract public function toSql(MyQueryBuilder $builder);

    protected function compileJoins(array $elements)
    {
        $sql = implode( ' ', array_map(function($val){

            $on = $this->compileWheres($val['on'], true);

            return $val['type'] . ' JOIN ' . $val['table'] . ' ' . $on;

        }, $elements) );

        return $sql;
    }

    protected function compileWheres(array $elements, bool $on = false)
    {
        $firstWord = $on ? 'ON' : 'WHERE';

        $conditions = $this->compileConditions($elements);

        $sql = $firstWord . ' ' . $conditions;

        return $sql;
    }

    protected function compileConditions(array $conditions)
    {
        $conditions[0][3] = '';

        return array_map(function($val){

            return is_array($val[0])
                ? '( ' . $this->compileConditions($val) . ' )'
                : $val[3] . ' ' . $val[0] . ' ' . $val[1] . ' ' . (is_array($val[2]) ? '(' . makePlaceholders($val[2]) . ')' : $val[2]);

        }, $conditions);
    }
}
<?php

namespace DB\Compilers;

use DB\MyQueryBuilder;

abstract class Compiler
{
    abstract public function toSql(MyQueryBuilder $builder);

    /**
     * @param array $elements
     */
    protected function compileJoins(array $elements)
    {
        if ( empty($elements) ) {
            return '';
        }

        $sql = implode( ' ', array_map(function($val){

            $on = $this->compileWheres($val['on'], true, false);

            return $val['type'] . ' JOIN ' . $val['table'] . $on;

        }, $elements) );

        return ' ' . $sql;
    }

    /**
     * @param array $elements
     * @param bool $on = false
     * @param bool $ph = true - make placeholders in string if true or not if false
     */
    protected function compileWheres(array $elements, bool $on = false, bool $ph = true)
    {
        if ( empty($elements) ) {
            return '';
        }

        $firstWord = $on ? 'ON' : 'WHERE';

        $conditions = $this->compileConditions($elements, $ph);

        $sql = $firstWord . ' ' . $conditions;

        return ' ' . $sql;
    }

    /**
     * @param array $conditions
     * @param bool $ph = true - make placeholders in string if true or not if false
     */
    protected function compileConditions(array $conditions, bool $ph = true)
    {
        $conditions[0][3] = null;

        return implode( ' ', array_map(function($val) use($ph){
            $boolean = ($val[3] ? $val[3] . ' ' : '');
            $column = $val[0];
            $operator = $val[1];
            $value = ( is_array($val[2]) ? '(' . makePlaceholders($val[2]) . ')' : ($ph ? '?' : $val[2]) );

            return is_array($val[0])
                ? '( ' . $this->compileConditions($val) . ' )'
                : "{$boolean}{$column} {$operator} {$value}";

        }, $conditions) );
    }

    /**
     * @param array $limits
     */
    protected function compileLimit(array $limits)
    {
        return !$limits
            ? ''
            : ' LIMIT ' . makePlaceholders($limits);
    }
}
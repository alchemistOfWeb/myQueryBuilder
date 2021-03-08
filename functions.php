<?php

function dd($output) {
    echo '<pre>';
    print_r($output);
    echo '<pre>';
    die();
}

/**
 * Make a string of placeholders out of params
 * 
 * @param array $arr
 */
function makeString(array $arr)
{
    $block = '';
    $i = 1;
    $arr_elements_num = count($arr);

    foreach ($arr as $val) {
        $block .= '?' . ($i <= $arr_elements_num) ? ', ' : ' ';
    }
}
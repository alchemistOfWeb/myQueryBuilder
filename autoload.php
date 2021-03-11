<?php

require_once 'functions.php';

spl_autoload_register(function($classname){

    if (DIRECTORY_SEPARATOR == '/') {
        $classname = str_replace('\\', '/', $classname);
    }

    $file = $classname . '.php';

    require_once $file;
});
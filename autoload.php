<?php

spl_autoload_register(function($className) {
    $file = __DIR__ . '\\inc\\' . $className . '.php';
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
    if (file_exists($file)) {
        include $file;
    }
});

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/gregavola/untappdphp/lib/untappdPHP.php';
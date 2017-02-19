<?php
require_once __DIR__."/../vendor/autoload.php";

require_once __DIR__."/TestCase.php";

// Bookstore
require_once __DIR__."/bookstore/src/BookSearcher.php";

$adapters = array(
    'bookstore' => new \Labi\Adapters\Mysql\Adapter('bookstore', array(
        'name' => 'bookstore',
        'adapter' => 'mysql',

        // 'host' => '192.168.10.115',
        'host' => 'localhost',
        'dbname' => 'bookstore',
        // 'username' => 'user',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8'
    ))
);

\Labi\Tests\TestCase::adapters($adapters);

<?php
require __DIR__."/../vendor/autoload.php";

$adapter = new \Labi\Adapters\Mysql('bookstore', array(
    'adapter' => 'mysql',
    'host' => '192.168.10.115',
    'dbname' => 'bookstore_source',
    'username' => 'user',
    'password' => '',
    'charset' => 'utf8'
));

$select = $adapter->searcher();

$select->from('books');

// todo : delete
die(print_r($select->search(), true));
// endtodo

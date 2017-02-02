<?php
require __DIR__."/../vendor/autoload.php";

use Pimple\Container;

$labi = new \Labi\Labi(__DIR__."/config.php");

$adapter = $labi->adapter('bookstore');

$select = $adapter->searcher();

// simple example of select
$select
    ->from('authors', 'cs')
;

// todo : delete
die(print_r($select->search(), true));
// endtodo

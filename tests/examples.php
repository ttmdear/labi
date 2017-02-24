<?php

require_once __DIR__."/../vendor/autoload.php";

$adapter = new \Labi\Adapters\Mysql\Adapter('bookstore', array(
    'name' => 'bookstore',
    'adapter' => 'mysql',

    // 'host' => '192.168.10.115',
    'host' => 'localhost',
    'dbname' => 'bookstore',
    // 'username' => 'user',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8'
));

$searcher = $adapter->searcher();

$searcher
    ->from('books')
    ->column('name')
    ->column('idCategory')
;

$rows = $searcher->search();

# Complex query

$searcher = $adapter->searcher();

$searcher
    ->from('books', 'b')
    ->column('name')
    ->column('idCategory')
;

$join = $searcher
    ->innerJoin('booksTags', 'bt')
    ->using('tagId')
;

$searcher
    ->in('idBook', array(1,2,3))
    ->notIn('idBook', array(5,6,7))

    ->isNull('idCategory')
    ->isNotNull('tagId')

    ->startWith('name', 'A')
    ->endWith('name', 'g')
    ->contains('name', '-')

    ->eq('cover', '1')
    ->neq('cover', '2')

    ->lt('data', '2017-01-30')
    ->lte('dataR', '2017-01-30')

    ->gt('data', '2017-01-30')
    ->gte('dataR', '2017-01-30')

    ->expr('(idBook != :idBlocked OR (dataR = :dataR AND data is not null))')

    ->between('dataC', '2017-01.01', '2017-02-01')

    ->limit(10, 5)

    ->orderAsc('id')
;

die($searcher->toSql());



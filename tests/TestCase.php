<?php
namespace Labi\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    private static $adapters;

    public function adapters($adapters = null)
    {
        if (is_null($adapters)) {
            return self::$adapters;
        }

        self::$adapters = $adapters;
    }

    public function adapter($name)
    {
        return self::$adapters[$name];
    }

    public function reset()
    {
        $bookstore = self::adapter('bookstore');
        $bookstore->execute(file_get_contents(__DIR__."./bookstore/doc/bookstore.sql"));
    }

    public function md5($var)
    {
        return md5(var_export($var, true));
    }

    public function randomDate() {
        $y = rand(1900, 2010);
        $m = rand(1, 12);
        $d = rand(1, 28);
        $h = rand(1, 23);
        $i = rand(1, 59);
        $s = rand(1, 59);

        return date("$y-$m-$d");
    }

    public function randomDatetime() {
        $y = rand(1900, 2010);
        $m = rand(1, 12);
        $d = rand(1, 28);
        $h = rand(1, 23);
        $i = rand(1, 59);
        $s = rand(1, 59);

        return date("$y-$m-$d $h:$i:$s");
    }
}

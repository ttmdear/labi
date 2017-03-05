<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database\Utility;

class Field
{
    private $table;
    private $name;
    private $quoteChar;

    function __construct($table, $name, $quoteChar = null)
    {
        $this->table = $table;
        $this->name = $name;
        $this->quoteChar = is_null($quoteChar) ? "" : $quoteChar;
    }

    public function __toString()
    {
        $quoteChar = $this->quoteChar;

        return "{$quoteChar}{$this->table}{$quoteChar}.{$quoteChar}{$this->name}{$quoteChar}";
    }
}

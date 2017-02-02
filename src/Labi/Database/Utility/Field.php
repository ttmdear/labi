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

    function __construct($table, $name)
    {
        $this->table = $table;
        $this->name = $name;
    }

    public function __toString()
    {
        return "`{$this->table}`.`{$this->name}`";
    }
}

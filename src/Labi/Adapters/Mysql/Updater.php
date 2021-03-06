<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Adapters\Mysql;

class Updater extends \Labi\Database\Updater
{
    protected function quoteChar()
    {
        return "`";
    }
}

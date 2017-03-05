<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Adapters\Pgsql;

class Remover extends \Labi\Database\Remover
{
    protected function quoteChar()
    {
        return "\"";
    }
}

<?php
/**
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database\Utility;

class Uid
{
    private static $uId = 1;

    public static function uId()
    {
        self::$uId++;

        return "uId".self::$uId;
    }
}

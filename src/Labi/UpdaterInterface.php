<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi;

use Labi\Adapters\AdapterInterface;

interface UpdaterInterface
{
    function __construct(AdapterInterface $adapter);
    public function update($params = array());
}

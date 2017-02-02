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

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;

use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer implements ContainerInterface
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);
    }

    public function get($id)
    {
        if (!$this->offsetExists($id)) {
            throw new NotFoundException(sprintf('Identifier "%s" is not defined.', $id));
        }

        try {
            return $this->offsetGet($id);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function set($name, $value)
    {
        $this->offsetSet($name, $value);
        return $this;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }
}

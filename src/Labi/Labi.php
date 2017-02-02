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

use Labi\Container;
use Interop\Container\ContainerInterface;
use Labi\Adapters\AdapterInterface;

class Labi
{
    private $container;
    private $adapters = array();
    private $default = array(
        'labi.adapters.mysql' => '\\Labi\\Adapters\\Mysql'
    );

    function __construct($container)
    {
        if (is_string($container) && is_readable($container) && is_file($container)) {
            $container = include($container);
            if (!is_array($container)) {
                throw new \Exception("Can not read config file. Config file {$container} should be proper array.");
            }
        }

        if (is_array($container)) {
            $container = new Container($container);
        }

        if (!($container instanceof ContainerInterface)) {
            throw new \Exception("Container must be instance of Interop\Container\ContainerInterface.");
        }

        foreach ($this->default as $name => $value) {
            if (!$container->has($name)) {
                $container->set($name, $value);
            }
        }

        $this->container = $container;
    }

    public function adapter($source)
    {
        if (isset($this->adapters[$source])) {
            return $this->adapters[$source];
        }

        // init adapter
        if (!$this->container->has("labi.sources.{$source}")) {
            throw new \Exception("There are no config for {$source} source.");
        }

        // pobieram konfiguracje
        $config = $this->container->get("labi.sources.{$source}");

        if (!isset($config['adapter'])) {
            throw new \Exception("Source {$source} has not defined adapter type.");
        }

        $adapter = $config['adapter'];

        if (!$this->container->has("labi.adapters.{$adapter}")) {
            throw new \Exception("The {$adapter} is not defined at labi.adapters.{$adapter} .");
        }

        $adapterClass = $this->container->get("labi.adapters.{$adapter}");

        if (!class_exists($adapterClass)) {
            throw new \Exception("Can not load {$adapterClass} adapter class.");
        }

        if (!in_array(AdapterInterface::class, class_implements($adapterClass))) {
            throw new \Exception("Adapter class {$adapterClass} must implements ".AdapterInterface::class);
        }

        return $this->adapters[$source] = new $adapterClass($source, $config, $this->container);
    }
}


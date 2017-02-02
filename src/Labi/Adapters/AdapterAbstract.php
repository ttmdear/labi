<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Adapters;

use Labi\Container;
use Labi\Adapters\AdapterInterface;

abstract class AdapterAbstract implements AdapterInterface
{
    abstract public function execute($command, $params = array());
    abstract public function fetch($command, $params = array());
    abstract public function lastId();
    abstract public function searcher();
    abstract public function creator();
    abstract public function remover();
    abstract public function updater();

    private $container;
    private $source;
    private $config;

    function __construct($source, $config, Container $container)
    {
        $this->source = $source;
        $this->config = $config;
        $this->container = $container;
    }

    protected function config()
    {
        return $this->config;
    }

    public function source()
    {
        return $this->source;
    }
}

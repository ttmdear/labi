<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database\Statements;

use Labi\Database\Statements\Select;
use Labi\Container;

abstract class Statement
{
    abstract public function toSql($params = array());

    private $params = array();
    private $pparams = array();
    private $container;


    function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function params($proccess = false)
    {
        if ($proccess) {
            return $this->pparams;
        }else{
            return $this->params;
        }
    }

    public function reset($proccess = false)
    {
        if ($proccess) {
            $this->pparams = array();
        }else{
            $this->params = array();
        }

        return $this;
    }

    public function param($name, $value, $proccess = false)
    {
        if ($proccess) {
            $this->pparams[$name] = $value;
        }else{
            $this->params[$name] = $value;
        }

        return $this;
    }
}

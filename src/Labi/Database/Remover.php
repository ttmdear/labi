<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database;

use Labi\Database\Utility\Condition;
use Labi\Database\Utility\ConditionInterface;
use Labi\Adapters\AdapterInterface;
use Labi\Operators\RemoverInterface;

class Remover implements ConditionInterface, RemoverInterface
{
    private $adapter = null;
    private $table = null;
    private $condition = null;

    private $params = array();
    private $pparams = array();

    function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        $this->condition = new Condition($this);
    }

    public function table($table = null)
    {
        if (!is_null($table)) {
            $this->table = $table;
            return $this;
        }

        return $this->table;
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

    public function toSql($params = array())
    {
        $table = $this->table();

        if(is_null($table)){
            throw new \Exception("Delete statement must be defined table.");
        }

        $sql = "delete from `{$table}`\n";

        $condition = $this->condition->toSql();
        if (!is_null($condition)) {
            $sql .= "where $condition";
        }

        return $sql;
    }

    // + RemoverInterface
    public function remove($params = array())
    {
        $sql = $this->toSql();
        $this->adapter->execute($sql, $this->params(true));
        return true;
    }
    // - RemoverInterface

    // + ConditionInterface
    public function brackets($function, $scope = null)
    {
        $this->condition->brackets($function, $this);
        return $this;
    }

    public function andOperator()
    {
        $this->condition->andOperator();
        return $this;
    }

    public function orOperator()
    {
        $this->condition->orOperator();
        return $this;
    }

    public function in($column, $value)
    {
        $this->condition->in($column, $value);
        return $this;
    }

    public function notIn($column, $value)
    {
        $this->condition->notIn($column, $value);
        return $this;
    }

    public function isNull($column)
    {
        $this->condition->isNull($column);
        return $this;
    }

    public function isNotNull($column)
    {
        $this->condition->isNotNull($column);
        return $this;
    }

    public function startWith($column, $value)
    {
        $this->condition->startWith($column, $value);
        return $this;
    }

    public function endWith($column, $value)
    {
        $this->condition->endWith($column, $value);
        return $this;
    }

    public function contains($column, $value)
    {
        $this->condition->contains($column, $value);
        return $this;
    }

    public function like($column, $value)
    {
        $this->condition->like($column, $value);
        return $this;
    }

    public function eq($column, $value)
    {
        $this->condition->eq($column, $value);
        return $this;
    }

    public function neq($column, $value)
    {
        $this->condition->neq($column, $value);
        return $this;
    }

    public function lt($column, $value)
    {
        $this->condition->lt($column, $value);
        return $this;
    }

    public function lte($column, $value)
    {
        $this->condition->lte($column, $value);
        return $this;
    }

    public function gt($column, $value)
    {
        $this->condition->gt($column, $value);
        return $this;
    }

    public function gte($column, $value)
    {
        $this->condition->gte($column, $value);
        return $this;
    }

    public function expr($expr)
    {
        $this->condition->expr($expr);
        return $this;
    }

    public function exists($value)
    {
        $this->condition->exists($value);
        return $this;
    }

    public function notExists($value)
    {
        $this->condition->notExists($value);
        return $this;
    }

    public function between($column, $begin, $end)
    {
        $this->condition->between($column, $begin, $end);
        return $this;
    }
    // - ConditionInterface
}

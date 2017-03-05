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

use Labi\Database\Utility\Join;
use Labi\Database\Utility\Field;
use Labi\Database\Utility\Column;
use Labi\Database\Utility\Condition;
use Labi\Database\Utility\ConditionInterface;
use Labi\Adapters\AdapterInterface;
use Labi\Operators\RemoverInterface;

abstract class Remover implements ConditionInterface, RemoverInterface
{
    abstract protected function quoteChar();

    private $adapter = null;
    private $table = null;
    private $columns = array();
    private $condition = null;

    private $params = array();
    private $pparams = array();

    function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        $this->condition = new Condition($this);
    }

    public function column($cname)
    {
        if (is_null($this->table)) {
            throw new \Exception("Please define table, before refer to column.");
        }

        // rozbicie zapisuj kolumny na table i tabele
        $cname = Column::convColumnId($cname, $this->table);

        if (isset($this->columns[$cname->id])) {

            // podana kolumna zostala juz zdefiniowana
            $column = $this->columns[$cname->id];
            $column->context($this);
            $column->condition($this->condition);

            return $column;
        }

        // tworze nowa kolumne, gdzie wartoscia jest pole
        $column = new Column(new Field($cname->table, $cname->name, $this->quoteChar()));
        $column
            // ustawiam context na selecta
            ->context($this)
            // przekazuje obiekt warunku z ktorego bedzie korzystac kolumna
            ->condition($this->condition)
        ;

        // zapisuje kolmne
        $this->columns[$cname->id] = $column;

        return $column;
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
        $quoteChar = $this->quoteChar();

        if(is_null($table)){
            throw new \Exception("Delete statement must be defined table.");
        }

        $sql = "delete from {$quoteChar}{$table}{$quoteChar}\n";

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

    // + \Labi\Database\Utility\ConditionInterface
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
        $this->condition->in($this->column($column, false), $value);
        return $this;
    }

    public function notIn($column, $value)
    {
        $this->condition->notIn($this->column($column, false), $value);
        return $this;
    }

    public function isNull($column)
    {
        $this->condition->isNull($this->column($column, false));
        return $this;
    }

    public function isNotNull($column)
    {
        $this->condition->isNotNull($this->column($column, false));
        return $this;
    }

    public function startWith($column, $value)
    {
        $this->condition->startWith($this->column($column, false), $value);
        return $this;
    }

    public function endWith($column, $value)
    {
        $this->condition->endWith($this->column($column, false), $value);
        return $this;
    }

    public function contains($column, $value)
    {
        $this->condition->contains($this->column($column, false), $value);
        return $this;
    }

    public function like($column, $value)
    {
        $this->condition->like($this->column($column, false), $value);
        return $this;
    }

    public function eq($column, $value)
    {
        $this->condition->eq($this->column($column, false), $value);
        return $this;
    }

    public function neq($column, $value)
    {
        $this->condition->neq($this->column($column, false), $value);
        return $this;
    }

    public function lt($column, $value)
    {
        $this->condition->lt($this->column($column, false), $value);
        return $this;
    }

    public function lte($column, $value)
    {
        $this->condition->lte($this->column($column, false), $value);
        return $this;
    }

    public function gt($column, $value)
    {
        $this->condition->gt($this->column($column, false), $value);
        return $this;
    }

    public function gte($column, $value)
    {
        $this->condition->gte($this->column($column, false), $value);
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
    // - \Labi\Database\Utility\ConditionInterface
}

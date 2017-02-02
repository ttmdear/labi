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

use Labi\Database\Utility\Condition;
use Labi\Database\Utility\ConditionInterface;
use Labi\UpdaterInterface;

class Update extends Statement implements ConditionInterface, UpdaterInterface
{
    private $table;
    private $adapter;
    private $values = array();
    private $condition = null;

    function __construct($adapter, $container)
    {
        parent::__construct($container);

        $this->adapter = $adapter;

        $this->condition = new Condition($this, $this);
    }

    public function table($table = null)
    {
        if (!is_null($table)) {
            $this->table = $table;
            return $this;
        }

        return $this->table;
    }

    public function values($values = null)
    {
        if (is_null($values)) {
            return $this->values;
        }

        $this->values = $values;

        return $this;
    }

    // + Statement
    public function toSql($params = array())
    {
        $table = $this->table();
        $values = $this->values();

        if(is_null($table)){
            throw new \Exception("Define table to update.");
        }

        if (empty($values)) {
            throw new \Exception("No values to update.");
        }

        // usuwam rzeczy zwiazane z przetwarzaniem zapytania
        $this->reset(true);

        $sql = "update `{$table}`\n";

        $columns = array_keys($this->values);

        // zliczam ilosc kolumn
        $ccount = count($columns);

        if ($ccount === 0) {
            throw new \Exception("No values to update.");
        }else{
            $sql .= "set \n";
        }

        for ($i=0; $i < $ccount; $i++) {
            $column = $columns[$i];
            $value = $this->values[$column];

            $direct = false;
            if (is_int($value)) {
                $value = (int)$value;
                $direct = true;
            }

            if (is_float($value)) {
                $value = (float)$value;
                $direct = true;
            }

            if (is_null($value)) {
                $value = "null";
                $direct = true;
            }

            if ($direct) {
                $sql .= "    `$column` = {$value}";
            }else{
                $uId = Uid::uId();
                $sql .= "    `$column` = :{$uId}";
                $this->param($uId, $value, true);
            }

            if ($i !== $ccount-1) {
                $sql .= ",\n";
            }
        }

        $condition = $this->condition->toSql();

        if (!is_null($condition)) {
            $sql .= "\nwhere ".$condition;
        }

        return $sql;
    }
    // - Statement

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

    // + UpdaterInterface
    public function update($params = array())
    {
        $sql = $this->toSql();

        $params = array_merge($this->params(), $this->params(true), $params);

        $this->adapter->execute($sql, $params);
        return true;
    }
    // - UpdaterInterface
}

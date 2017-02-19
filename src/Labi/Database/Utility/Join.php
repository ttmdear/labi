<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database\Utility;

use Labi\Database\Utility\Condition;
use Labi\Database\Utility\ConditionInterface;

class Join implements ConditionInterface
{
    /**
     * Kontekst w ktorym dziala join. Może to byc np. Select, Delete, Update
     */
    private $context;

    /**
     * Join nadrzedny.
     */
    private $join;

    /**
     * ,Tabela dolaczana.
     */
    private $table;

    /**
     * Alias dla tabeli
     */
    private $alias;

    /**
     * Obiekt obslugujacy warunek zlaczenia.
     */
    private $condition;

    /**
     * Typ zlaczenia.
     */
    private $type;

    function __construct($context, $table, $alias = null, $join = null)
    {
        $this->context = $context;
        $this->condition = new Condition($this);

        $this->join = $join;

        if (is_null($alias)) {
            $alias = $table;
        }

        $this->table = $table;
        $this->alias = $alias;
    }

    // + magic
    public function __clone()
    {
        $this->join = null;

        $this->condition = clone($this->condition);
        $this->condition->context($this);
    }
    // - magic

    public function param($name, $value)
    {
        $this->context->param($name, $value);

        return $this;
    }

    public function params()
    {
        return $this->context->params();
    }

    public function alias()
    {
        return $this->alias;
    }

    public function table()
    {
        return $this->table;
    }

    public function context($context = null)
    {
        if (is_null($context)) {
            return $this->context;
        }

        $this->context = $context;

        return $this;
    }

    public function column($cname, $show = true)
    {
        $cname = Column::convColumnId($cname, $this->alias);
        $column = $this->context->column($cname->id, $show);

        // set context of column to join, because context
        $column->context($this);
        $column->condition($this->condition);

        return $column;
    }

    // join type
    public function typeInner()
    {
        $this->type = 'inner';
        return $this;
    }

    public function typeLeft()
    {
        $this->type = 'left';
        return $this;
    }

    public function typeOuter()
    {
        $this->type = 'outer';
        return $this;
    }

    // joins
    public function innerJoin($table, $alias = null)
    {
        $join = new Join($this->context, $table, $alias, $this);
        $join->typeInner();
        $this->joins[] = $join;

        return $join;
    }

    public function outerJoin($table, $alias = null)
    {
        $join = new Join($this->context, $table, $alias, $this);
        $join->typeOuter();
        $this->joins[] = $join;

        return $join;
    }

    public function leftJoin($table, $alias = null)
    {
        $join = new Join($this->context, $table, $alias, $this);
        $join->typeLeft();
        $this->joins[] = $join;

        return $join;
    }

    public function join($table, $alias = null)
    {
        $join = new Join($this->context, $table, $alias, $this);
        $join->typeInner();
        $this->joins[] = $join;

        return $join;
    }

    public function toSql($params = array())
    {
        $on = $this->condition->toSql($params);

        if (is_null($on)) {
            return "{$this->type} join `{$this->table}` as `{$this->alias}`";
        }else{
            return "{$this->type} join `{$this->table}` as `{$this->alias}` \n    on {$on}";
        }
    }

    public function using($using)
    {
        if (!is_array($using)) {
            $using = array($using);
        }

        $join = $this->context;

        if (!is_null($this->join)) {
            if (!is_null($this->join)) {
                $join = $this->join;
            }
        }

        foreach ($using as $column) {
            $this->column($column, false)->eq($join->column($column, false));
        }

        return $this;
    }

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

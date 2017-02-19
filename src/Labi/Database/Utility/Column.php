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

class Column
{
    const UNDEFINED = '#21089487035279118854#@-';

    private $value;
    private $alias;

    private $context;
    private $condition;
    private $hide = true;

    public function __construct($value)
    {
        $this->value = $value;
    }

    // + magic
    public function __toString()
    {
        return (string)$this->value();
    }
    // - magic

    public static function convColumnId($id, $table)
    {
        if (!is_string($id)) {
            throw new \Exception("Column id should be string.");
        }

        $exploded = explode('.', $id);
        $count = count($exploded);

        if ($count === 1) {
            $std = new \stdClass();

            $std->table = $table;
            $std->name = $id;
            $std->id = "{$table}.{$id}";

            return $std;
        }elseif($count === 2){
            $std = new \stdClass();

            $std->table = $exploded[0];
            $std->name = $exploded[1];
            $std->id = $id;

            return $std;
        }else{
            throw new \Exception("Incorrect column name $id.");

        }
    }

    public function hide()
    {
        $this->hide = true;
        return $this;
    }

    public function show()
    {
        $this->hide = false;
        return $this;
    }

    public function isHidden()
    {
        return $this->hide === true;
    }

    public function alias($alias = null)
    {
        if (is_null($alias)) {
            return $this->alias;
        }

        $this->alias = $alias;

        return $this;
    }

    public function string($value)
    {
        return $this->value("\"$value\"");
    }

    public function value($value = self::UNDEFINED)
    {
        if ($value === self::UNDEFINED) {
            return $this->value;
        }

        $this->value = $value;

        return $this;
    }

    public function context($context = null)
    {
        if (is_null($context)) {
            return $this->context;
        }

        $this->context = $context;
        return $this;
    }

    /**
     * Ustawia obiekt warunku z kótrym współpracuje kolumna.
     *
     * @param \Labi\Database\Utility\Condition
     * @return self
     */
    public function condition($condition)
    {
        $this->condition = $condition;
        return $this;
    }

    public function column($cname, $show = true)
    {
        return $this->context->column($cname, $show);
    }

    // + ConditionInterface
    public function brackets($function)
    {
        throw new \Exception("Column condition do not support brackets.");
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

    public function in($value)
    {
        $this->condition->in($this, $value);
        return $this;
    }

    public function notIn($value)
    {
        $this->condition->notIn($this, $value);
        return $this;
    }

    public function isNull()
    {
        $this->condition->isNull($this);
        return $this;
    }

    public function isNotNull()
    {
        $this->condition->isNotNull($this);
        return $this;
    }

    public function startWith($value)
    {
        $this->condition->startWith($this, $value);
        return $this;
    }

    public function endWith($value)
    {
        $this->condition->endWith($this, $value);
        return $this;
    }

    public function contains($value)
    {
        $this->condition->contains($this, $value);
        return $this;
    }

    public function like($value)
    {
        $this->condition->like($this, $value);
        return $this;
    }

    public function eq($value)
    {
        $this->condition->eq($this, $value);
        return $this;
    }

    public function neq($value)
    {
        $this->condition->neq($this, $value);
        return $this;
    }

    public function lt($value)
    {
        $this->condition->lt($this, $value);
        return $this;
    }

    public function lte($value)
    {
        $this->condition->lte($this, $value);
        return $this;
    }

    public function gt($value)
    {
        $this->condition->gt($this, $value);
        return $this;
    }

    public function gte($value)
    {
        $this->condition->gte($this, $value);
        return $this;
    }

    public function between($begin, $end)
    {
        $this->condition->between($this, $begin, $end);
        return $this;
    }
    // end of ConditionInterface
}

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

use Labi\Database\Searcher;
use Labi\Database\Utility\Column;
use Labi\Database\Utility\ConditionInterface;
use Labi\Database\Utility\Uid;

class Condition implements ConditionInterface
{
    private $mstack = array();
    private $brackets;
    private $mbrackets;

    private $context;

    function __construct($context)
    {
        $this->context = $context;

        // pierwsze nawiasy
        $brackets = array();

        // przekazanie referencji do brackets aktualnych i do roota
        $this->mbrackets = new \stdClass();
        $this->mbrackets->type = 'brackets';
        $this->mbrackets->operator = 'and';
        $this->mbrackets->elements = array();

        $this->brackets = $this->mbrackets;
    }

    // + magic
    public function __clone()
    {
        $cloned = $this->cloneBrackets($this->mbrackets);

        $this->mbrackets = &$cloned;
        $this->brackets = &$this->mbrackets;
    }
    // - magic

    private function cloneBrackets($brackets)
    {
        // tworze klon nawiasow
        $cloned = new \stdClass();
        $cloned->type = $brackets->type;
        $cloned->operator = $brackets->operator;
        $cloned->elements = array();

        foreach ($brackets->elements as $element) {
            if ($element->type === 'brackets') {
                // kolunuje nawiasy
                array_push($cloned->elements, $this->cloneBrackets($element));
            }else{
                // wrzucam elementy do listy
                array_push($cloned->elements, $element);
            }
        }

        return $cloned;
    }

    public function context($context = null)
    {
        if (is_null($context)) {
            return $this->context;
        }

        $this->context = $context;
        return $this;
    }

    public function brackets($function, $scope = null)
    {
        if (is_null($scope)) {
            $scope = $this;
        }

        $this->openBrackets();
        call_user_func_array($function, array($scope));

        $this->closeBrackets();

        return $this->context;
    }

    private function openBrackets()
    {
        // wrzucam na stos aktualnie przetwarzany nawias
        // $this->mstack[] = &$this->brackets;
        $this->mstack[] = $this->brackets;

        $brackets = new \stdClass();
        $brackets->type = 'brackets';
        $brackets->operator = 'and';
        $brackets->elements = array();

        if (count($this->brackets->elements) > 0) {
            $element = new \stdClass();
            $element->type = 'operator';
            $element->value = $this->brackets->operator;

            array_push($this->brackets->elements, $element);
        }

        array_push($this->brackets->elements, $brackets);

        $this->brackets = $brackets;
    }

    private function closeBrackets()
    {
        $brackets = array_pop($this->mstack);

        $this->brackets = $brackets;
    }

    // (wA AND wB)
    public function andOperator()
    {
        // aktualnie przetwarzany nawias ma AND operator
        $this->brackets->operator = 'and';

        return $this->context;
    }

    public function orOperator()
    {
        // aktualnie przetwarzany nawias ma OR operator
        $this->brackets->operator = 'or';

        return $this->context;
    }

    // methods
    public function in($column, $value)
    {
        if (is_numeric($value) || is_string($value)) {
            $value = array($value);
        }

        $element = new \stdClass();
        $element->type = 'in';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function notIn($column, $value)
    {
        if (is_numeric($value) || is_string($value)) {
            $value = array($value);
        }

        $element = new \stdClass();
        $element->type = 'notIn';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function isNull($column)
    {
        $element = new \stdClass();
        $element->type = 'isNull';
        $element->column = $column;
        $element->value = null;

        $this->addElement($element);

        return $this->context;
    }

    public function isNotNull($column)
    {
        $element = new \stdClass();
        $element->type = 'isNotNull';
        $element->column = $column;
        $element->value = null;

        $this->addElement($element);

        return $this->context;
    }

    public function startWith($column, $value)
    {
        return $this->like($column, "$value%");
    }

    public function endWith($column, $value)
    {
        return $this->like($column, "%$value");
    }

    public function contains($column, $value)
    {
        return $this->like($column, "%$value%");
    }

    public function like($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'like';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function eq($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'eq';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function neq($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'neq';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function lt($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'lt';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function lte($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'lte';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function gt($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'gt';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function gte($column, $value)
    {
        $element = new \stdClass();
        $element->type = 'gte';
        $element->column = $column;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function expr($expr)
    {
        $element = new \stdClass();
        $element->type = 'expr';
        $element->column = null;
        $element->value = $expr;

        $this->addElement($element);

        return $this->context;
    }

    public function exists($value)
    {
        $element = new \stdClass();
        $element->type = 'exists';
        $element->column = null;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function notExists($value)
    {
        $element = new \stdClass();
        $element->type = 'notExists';
        $element->column = null;
        $element->value = $value;

        $this->addElement($element);

        return $this->context;
    }

    public function between($column, $begin, $end)
    {
        $this->gte($column, $begin);
        $this->lte($column, $end);

        // $this->addElement(array(
        //     'type' => 'between',
        //     'column' => $column,
        //     'value' => null,
        //     'begin' => $begin,
        //     'end' => $end,
        // ));

        return $this->context;
    }

    public function toSql($params = array())
    {
        $sql = "";

        $this->travers($this->mbrackets, $sql);

        if (empty($sql)) {
            return null;
        }

        return $sql;
    }

    private function addElement($element)
    {
        if (count($this->brackets->elements) > 0) {
            $operator = new \stdClass();
            $operator->type = 'operator';
            $operator->value = $this->brackets->operator;

            array_push($this->brackets->elements, $operator);
        }

        array_push($this->brackets->elements, $element);
    }

    private function travers($brakets, &$conditions)
    {
        foreach ($brakets->elements as $key => $element) {

            if ($element->type === 'brackets') {
                $conditions .= '(';
                $this->travers($element, $conditions);
                $conditions = rtrim($conditions);
                $conditions .= ') ';
            }elseif($element->type === 'operator'){
                $conditions .= "{$element->value} ";
            }else{
                $type = $element->type;
                $column = $element->column;
                $value = $element->value;

                $condition = null;

                // obsluga warunkow z operatorem prostym
                $operators = array(
                    'like' => "like",
                    'eq' => "=",
                    'neq' => "!=",
                    'lt' => "<",
                    'lte' => "<=",
                    'gt' => ">",
                    'gte' => ">=",
                );

                if (isset($operators[$type])) {
                    $operator = $operators[$type];
                    if ($value instanceof Column) {
                        $condition = "{$column} {$operator} {$value->value()}";
                    }else{
                        $uId = Uid::uId();
                        $condition = "{$column} {$operator} :$uId";
                        $this->context->param($uId, $value, true);
                    }
                }

                // obsluga zlozonych warunkow
                switch ($type) {
                case 'notIn':
                case 'in':
                    $operator = "in";

                    if ($type === 'notIn') {
                        $operator = 'not in';
                    }

                    if (empty($value)) {
                        $condition = "1=2";
                    }elseif ($value instanceof Searcher){
                        $condition = "{$column} {$operator}({$value->toSql()})";

                        // przenosze parametry
                        foreach ($value->params(true) as $name => $val) {
                            $this->context->param($name, $val, true);
                        }

                    }elseif(is_array($value)){
                        $condition = "{$column} {$operator}(";

                        $count = count($value);
                        for ($i=0; $i < $count; $i++) {
                            $inValue = $value[$i];
                            $direct = false;

                            if (is_int($inValue)) {
                                $inValue = (int)$inValue;
                                $direct = true;
                            }

                            if (is_float($inValue)) {
                                $inValue = (float)$inValue;
                                $direct = true;
                            }

                            if ($inValue instanceof Searcher) {
                                $inValue = "({$inValue->toSql()})";

                                // przenosze parametry
                                foreach ($inValue->params(true) as $name => $val) {
                                    $this->context->param($name, $val, true);
                                }

                                $direct = true;
                            }

                            if ($direct) {
                                $condition .= "$inValue";
                            }else{
                                $uId = Uid::uId();
                                $condition .= ":{$uId}";
                                $this->context->param($uId, $value[$i], true);
                            }

                            if ($i !== $count-1) {
                                $condition .= ",";
                            }
                        }

                        $condition .= ")";
                    }else{
                        throw new \Exception("Unsupported type of value for in(...)");
                    }

                    break;
                case 'isNull':
                    $condition = "{$column} is null";
                    break;
                case 'isNotNull':
                    $condition = "{$column} is not null";
                    break;
                case 'expr':
                    $condition = $value;
                    break;
                case 'exists':
                case 'notExists':
                    $operator = 'exists';

                    if ($type === 'notExists') {
                        $operator = 'not exists';
                    }

                    if ($value instanceof Searcher) {
                        $condition = "{$operator}({$value->toSql()})";

                        // przenosze parametry
                        foreach ($value->params(true) as $name => $val) {
                            $this->context->param($name, $val, true);
                        }

                    }else{
                        $condition = "{$operator}({$value})";
                    }

                    break;
                case 'between':
                    $begin = $element->begin;
                    $end = $element->end;

                    if ($begin instanceof Searcher) {
                        $begin = $begin->toSql();
                    }

                    if ($end instanceof Searcher) {
                        $end = $end->toSql();
                    }

                    $condition = "{$column} between {$begin} and {$end}";
                    break;
                }

                if (is_null($condition)) {
                    throw new \Exception("Not supported type of condition $type.");
                }

                $conditions .= "{$condition} ";
            }
        }
    }
}

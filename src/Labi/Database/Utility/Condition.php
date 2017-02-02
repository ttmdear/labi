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

use Labi\Database\Utility\Brackets;
use Labi\Database\Statements\Select;
use Labi\Database\Utility\Column;
use Labi\Database\Utility\ConditionInterface;
use Labi\Database\Utility\Uid;
use Labi\Database\Statements\Statement;

class Condition implements ConditionInterface
{
    private $bracketsstack = array();
    private $brackets;
    private $mbrackets;

    private $context;
    private $statement;

    function __construct($context, $statement)
    {
        $this->context = $context;
        $this->statement = $statement;

        // pierwszy nawias
        $brackets = new Brackets();

        $this->mbrackets = $brackets;
        $this->brackets = $brackets;
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
        // save previous brackets
        $this->bracketsstack[] = $this->brackets;

        // create new brackets
        $brackets = new Brackets();
        $this->brackets->add($brackets);

        $this->brackets = $brackets;
    }

    private function closeBrackets()
    {
        $last = array_pop($this->bracketsstack);
        $this->brackets = $last;
    }

    // (wA AND wB)
    public function andOperator()
    {
        $this->brackets->andOperator();

        return $this->context;
    }

    public function orOperator()
    {
        $this->brackets->orOperator();

        return $this->context;
    }

    // methods
    public function in($column, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        $this->brackets->add(array(
            'type' => 'in',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function notIn($column, $value)
    {
        if (!is_array($value)) {
            $value = array($value);
        }

        $this->brackets->add(array(
            'type' => 'notIn',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function isNull($column)
    {
        $this->brackets->add(array(
            'type' => 'isNull',
            'column' => $column,
            'value' => null
        ));

        return $this->context;
    }

    public function isNotNull($column)
    {
        $this->brackets->add(array(
            'type' => 'isNotNull',
            'column' => $column,
            'value' => null
        ));

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
        $this->brackets->add(array(
            'type' => 'like',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function eq($column, $value)
    {
        $this->brackets->add(array(
            'type' => 'eq',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function neq($column, $value)
    {
        $this->brackets->add(array(
            'type' => 'neq',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function lt($column, $value)
    {
        $this->brackets->add(array(
            'type' => 'lt',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function lte($column, $value)
    {
        $this->brackets->add(array(
            'type' => 'lte',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function gt($column, $value)
    {
        $this->brackets->add(array(
            'type' => 'gt',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function gte($column, $value)
    {
        $this->brackets->add(array(
            'type' => 'gte',
            'column' => $column,
            'value' => $value,
        ));

        return $this->context;
    }

    public function expr($expr)
    {
        $this->brackets->add(array(
            'type' => 'expr',
            'column' => null,
            'value' => $expr
        ));

        return $this->context;
    }

    public function exists($value)
    {
        $this->brackets->add(array(
            'type' => 'exists',
            'column' => null,
            'value' => $value,
        ));

        return $this->context;
    }

    public function notExists($value)
    {
        $this->brackets->add(array(
            'type' => 'notExists',
            'column' => null,
            'value' => $value,
        ));

        return $this->context;
    }

    public function between($column, $begin, $end)
    {
        $this->brackets->add(array(
            'type' => 'between',
            'column' => $column,
            'value' => null,
            'begin' => $begin,
            'end' => $end,
        ));

        return $this->context;
    }

    public function toSql($params = array())
    {
        $sql = "";

        $this->travers($this->mbrackets->elements(), $sql);

        if (empty($sql)) {
            return null;
        }

        return $sql;
    }

    private function travers($elements, &$conditions)
    {
        foreach ($elements as $element) {
            if ($element instanceof Brackets) {
                $conditions .= '(';
                $this->travers($element->elements(), $conditions);
                $conditions = rtrim($conditions);
                $conditions .= ') ';
            }elseif(is_string($element)){
                $conditions .= "{$element} ";
            }elseif(is_array($element)){
                $type = $element['type'];
                $column = $element['column'];
                $value = $element['value'];

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
                        $this->statement->param($uId, $value, true);
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
                    }elseif ($value instanceof Select){
                        $condition = "{$column} {$operator}({$value->toSql()})";
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

                            if ($inValue instanceof Select) {
                                $inValue = "({$inValue->toSql()})";
                                $direct = true;
                            }

                            if ($direct) {
                                $condition .= "$inValue";
                            }else{
                                $uId = Uid::uId();
                                $condition .= ":{$uId}";
                                $this->statement->param($uId, $value[$i], true);
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

                    if ($value instanceof Select) {
                        $condition = "{$operator}({$value->toSql()})";
                    }else{
                        $condition = "{$operator}({$value})";
                    }

                    break;
                case 'between':
                    $begin = $element['begin'];
                    $end = $element['end'];

                    if ($begin instanceof Select) {
                        $begin = $begin->toSql();
                    }

                    if ($end instanceof Select) {
                        $end = $end->toSql();
                    }

                    $condition = "{$column} between {$begin} and {$end}";
                    break;
                }

                if (is_null($condition)) {
                    throw new \Exception("Not supported type of condition $type.");
                }

                $conditions .= "{$condition} ";
            }else{
                throw new \Exception("Wrong type of element.");
            }
        }
    }

}

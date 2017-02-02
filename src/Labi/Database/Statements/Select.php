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

use Labi\Database\Utility\Join;
use Labi\Database\Utility\Field;
use Labi\Database\Utility\Column;
use Labi\Database\Utility\Condition;
use Labi\Database\Utility\ConditionInterface;
use Labi\Database\Statements\Statement;
use Labi\SearcherInterface;

class Select extends Statement implements ConditionInterface, SearcherInterface
{
    private $adapter = null;
    private $columns = array();
    private $table = null;
    private $alias = null;
    private $limit = null;
    private $orders = array();
    private $joins = array();
    private $condition = null;

    private $rules = array();
    private $defaultRule;

    function __construct($adapter, $container)
    {
        parent::__construct($container);

        $this->adapter = $adapter;
        $this->condition = new Condition($this, $this);

        $this->defaultRule = function($name, $value, $context){
            // standardowa metoda ustawia ze podana wartosc jest rowna tej
            // przekazanej w parametrze
            $context->column($name, false)->equal($value);

            return true;
        };
    }

    // magic
    public function __get($column)
    {
        return $this->column($column, false);
    }

    public function __set($column, $value)
    {
        return $this->column($column)->value($value);
    }

    public function __call($column, $args)
    {
        return $this->column($column, true);
    }
    // end of magic

    public function from($table, $alias = null)
    {
        if (is_null($alias)) {
            $alias = $table;
        }

        $this->table = $table;
        $this->alias = $alias;

        return $this;
    }

    public function alias()
    {
        return $this->alias;
    }

    public function table()
    {
        return $this->table;
    }

    public function column($cname, $show = true)
    {
        if (is_null($this->table)) {
            throw new \Exception("Please define from, before add column.");
        }

        // rozbicie zapisuj kolumny na alias i tabele
        $cname = Column::convColumnId($cname, $this->alias);

        if (isset($this->columns[$cname->id])) {

            // podana kolumna zostala juz zdefiniowana
            $column = $this->columns[$cname->id];
            $column->context($this);
            $column->condition($this->condition);

            if ($show) {
                // jest to sytuacja gdy np. kolumna zostanie zainicjowana jako
                // ukryta, a nastepnie uzytkownik odwola sie do niej
                // ->column('test')
                $column->show();
            }

            return $column;
        }

        // tworze nowa kolumne, gdzie wartoscia jest pole
        $column = new Column(new Field($cname->table, $cname->name));
        $column
            // ustawiam context na selecta
            ->context($this)
            // przekazuje obiekt warunku z ktorego bedzie korzystac kolumna
            ->condition($this->condition)
            // ustawiam select dla kolumny
            ->select($this)
        ;

        if ($show) {
            // podobnie jak wyzej
            $column->show();
        }

        // zapisuje kolmne
        $this->columns[$cname->id] = $column;

        return $column;
    }

    public function columns($columns)
    {
        if (!is_array($columns)) {
            throw new \Exception("Definiotion of columns must be array.");
        }

        foreach ($columns as $field => $val) {
            if (is_numeric($field)) {
                $this->column($val);
            }else{
                if (is_string($val)) {
                    $this
                        ->column($field)
                        ->alias($val)
                    ;

                }elseif(is_array($val) && count($val) === 2){
                    $this
                        ->column($field)
                        ->alias($val[0])
                        ->value($val[1])
                    ;

                }else{
                    throw new \Exception("Incorrect definition of columns.");
                }
            }
        }

        return $this;
    }

    // Join
    public function innerJoin($table, $alias = null)
    {
        $join = new Join($this, $table, $alias);
        $join->typeInner();
        $this->joins[] = $join;

        return $join;
    }

    public function outerJoin($table, $alias = null)
    {
        $join = new Join($this, $table, $alias);
        $join->typeOuter();
        $this->joins[] = $join;

        return $join;
    }

    public function leftJoin($table, $alias = null)
    {
        $join = new Join($this, $table, $alias);
        $join->typeLeft();
        $this->joins[] = $join;

        return $join;
    }

    public function join($table, $alias = null)
    {
        $join = new Join($this, $table, $alias);
        $join->typeInner();
        $this->joins[] = $join;

        return $join;
    }
    // end of Join

    // Rules
    public function defaultRule($defaultRule)
    {
        $this->defaultRule = $defaultRule;
        return $this;
    }

    public function rule($params, $rule = null)
    {
        if (!is_array($params)) {
            $params = array($params);
        }

        $this->rules[] = array(
            'params' => $params,
            'rule' => $rule
        );

        return $this;
    }

    public function proccess($params = array())
    {
        $params = array_merge($this->params(), $params);

        $paramsKeys = array_keys($params);
        foreach ($this->rules as $rule) {
            $values = array();

            foreach ($rule['params'] as $param) {
                if (in_array($param, $paramsKeys)) {
                    $values[$param] = $params[$param];
                }else{
                    continue 2;
                }
            }

            call_user_func_array($rule['rule'], array($values, $this));
        }

        return $this;
    }

    // end of Rules

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

    public function toSql($params = array())
    {
        if (is_null($this->table)) {
            // brak tabeli
            throw new \Exception("Please define table for select.");
        }

        // usuwam rzeczy zwiazane z przetwarzaniem zapytania
        $this->reset(true);

        // tworze bazowe zapytanie
        $alias = $this->alias;
        $table = $this->table;

        // build select
        $sql = "select\n";

        // columns
        $fields = array_keys($this->columns);
        $count = count($fields);
        $added = false;
        for ($i=0; $i < $count; $i++) {
            $column = $this->columns[$fields[$i]];

            if ($column->isHidden()) {
                // pomijam ukryte kolumny
                continue;
            }

            $value = $column->value();
            $cAlias = $column->alias();

            if (!is_null($cAlias)) {
                $cAlias = " as `{$cAlias}`";
            }else{
                $cAlias = "";
            }

            if(is_null($value)){
                $value = "null";
            }

            if ($i == $count-1) {
                // last column
                $sql .= "    {$value}{$cAlias}\n";
            }else{
                $sql .= "    {$value}{$cAlias},\n";
            }

            $added = true;
        }

        if ($added === false) {
            // jak nie ma kolumn to pobieram wszystkie
            $sql .= "    *\n";
        }

        $sql .= "from `{$table}` as `{$alias}`\n";

        // joins
        foreach ($this->joins as $join) {
            $sql .= $join->toSql()."\n";
        }

        // condition
        $condition = $this->condition->toSql();
        if (!is_null($condition)) {
            $sql .= "where $condition\n";
        }

        // orders
        if(count($this->orders) > 0){
            $sql .= 'order by ';
            foreach ($this->orders as $order) {
                $column = $order['column'];
                $type = $order['type'];

                $sql .= "$column $type, ";
            }

            $sql = rtrim($sql, ", ");
            $sql .= "\n";
        }

        // limit
        if (!is_null($this->limit)) {
            $offset = $this->limit['offset'];
            $limit = $this->limit['limit'];

            if($offset > 0){
                $sql .= "limit {$limit}, {$offset}";
            }else{
                $sql .= "limit {$limit}";
            }
        }

        $sql = rtrim($sql, "\n");

        return $sql;
    }

    public function limit($limit = null , $offset = 0)
    {
        if (is_null($limit)) {
            return $this->limit;
        }

        $this->limit = array(
            'limit' => $limit,
            'offset' => $offset
        );

        return $this;
    }

    public function orderAsc($column)
    {
        $this->orders[] = array(
            'column' => $column,
            'type' => 'asc'
        );

        return $this;
    }

    public function orderDesc($column)
    {
        $this->orders[] = array(
            'column' => $column,
            'type' => 'desc'
        );

        return $this;
    }

    // + SearcherInterface
    public function search($params = array())
    {
        return $this->all($params);
    }
    // - SearcherInterface

    public function one($params = array())
    {
        $this->limit(1);

        $rows = $this->all($params);

        if (empty($rows)) {
            return null;
        }

        return $rows[0];
    }

    public function all($params = array())
    {
        $sql = $this->toSql();

        $params = array_merge($this->params(), $this->params(true), $params);

        return $this->adapter->fetch($sql, $params);
    }
}

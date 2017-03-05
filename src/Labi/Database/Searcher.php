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

abstract class Searcher implements
    \Labi\Database\Utility\ConditionInterface,
    \Labi\Operators\SearcherInterface
{
    abstract protected function quoteChar();

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

    private $params = array();
    private $pparams = array();

    public function __construct(\Labi\Adapters\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
        $this->condition = new \Labi\Database\Utility\Condition($this);

        $this->defaultRule = function($name, $value, $searcher){
            // standardowa metoda ustawia ze podana wartosc jest rowna tej
            // przekazanej w parametrze
            $searcher->column($name, false)->equal($value);

            return true;
        };
    }

    // + magic
    // public function __get($column)
    // {
    //     return $this->column($column, false);
    // }

    // public function __set($column, $value)
    // {
    //     return $this->column($column)->value($value);
    // }

    // public function __call($column, $args)
    // {
    //     return $this->column($column, true);
    // }

    public function __clone()
    {
        // condition
        $this->condition = clone($this->condition);
        $this->condition->context($this);

        // columns
        $columns = $this->columns;
        $this->columns = array();

        foreach ($columns as $cname => $column) {
            $cloned = clone($column);
            $cloned->context($this);

            $this->columns[$cname] = $cloned;
        }

        // joins
        $joins = $this->joins;
        $this->joins = array();

        foreach ($joins as $join) {
            $cloned = clone($join);
            $cloned->context($this);
            $this->joins[] = $cloned;
        }
    }
    // - magic

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
        $cname = \Labi\Database\Utility\Column::convColumnId($cname, $this->alias);

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
        $column = new \Labi\Database\Utility\Column(new \Labi\Database\Utility\Field($cname->table, $cname->name, $this->quoteChar()));
        $column
            // ustawiam context na selecta
            ->context($this)
            // przekazuje obiekt warunku z ktorego bedzie korzystac kolumna
            ->condition($this->condition)
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

    // + \Labi\Database\Utility\Join
    public function innerJoin($table, $alias = null)
    {
        $join = new \Labi\Database\Utility\Join($this, $table, $alias, null, $this->quoteChar());
        $join->typeInner();
        $this->joins[] = $join;

        return $join;
    }

    public function outerJoin($table, $alias = null)
    {
        $join = new \Labi\Database\Utility\Join($this, $table, $alias, null, $this->quoteChar());
        $join->typeOuter();
        $this->joins[] = $join;

        return $join;
    }

    public function leftJoin($table, $alias = null)
    {
        $join = new \Labi\Database\Utility\Join($this, $table, $alias, null, $this->quoteChar());
        $join->typeLeft();
        $this->joins[] = $join;

        return $join;
    }

    public function join($table, $alias = null)
    {
        $join = new \Labi\Database\Utility\Join($this, $table, $alias, null, $this->quoteChar());
        $join->typeInner();
        $this->joins[] = $join;

        return $join;
    }
    // - \Labi\Database\Utility\Join

    // + Rules
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
    // - Rules

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
        $this->condition->between($this->column($column, false), $begin, $end);

        return $this;
    }
    // - \Labi\Database\Utility\ConditionInterface

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
        $quoteChar = $this->quoteChar();

        // build select
        $sql = "select\n";

        // columns
        $fields = array();

        // wybieram tylko te kolumny ktore nie sa ukryte
        foreach (array_keys($this->columns) as $field) {
            $column = $this->columns[$field];

            if ($column->isHidden()) {
                continue;
            }else{
                $fields[] = $field;
            }
        }

        // zliczam wszystkie kolumny
        $ccolumn = count($fields);
        $added = false;

        for ($i=0; $i < $ccolumn; $i++) {
            // pobieram zdefiniowana kolumne
            $column = $this->columns[$fields[$i]];

            if ($column->isHidden()) {
                // pomijam ukryte kolumny
                continue;
            }

            $value = $column->value();
            $calias = $column->alias();

            if (!is_null($calias)) {
                $calias = " as {$quoteChar}{$calias}{$quoteChar}";
            }else{
                $calias = "";
            }

            if(is_null($value)){
                $value = "null";
            }

            if ($i == $ccolumn-1) {
                // last column
                $sql .= "    {$value}{$calias}\n";
            }else{
                $sql .= "    {$value}{$calias},\n";
            }

            $added = true;
        }

        if ($added === false) {
            // jak nie ma kolumn to pobieram wszystkie
            $sql .= "    *\n";
        }

        $sql .= "from {$quoteChar}{$table}{$quoteChar} as {$quoteChar}{$alias}{$quoteChar}\n";

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

    public function first($params = array())
    {
        $this->limit(1);

        $rows = $this->search($params);

        if (empty($rows)) {
            return null;
        }

        return $rows[0];
    }

    // + \Labi\Operators\SearcherInterface
    public function search($params = array())
    {
        // tworze sql
        $sql = $this->toSql();

        // lacze parametry
        $params = array_merge($this->params(), $this->params(true), $params);

        // zwracam wyniki
        return $this->adapter->fetch($sql, $params);
    }
    // - \Labi\Operators\SearcherInterface
}

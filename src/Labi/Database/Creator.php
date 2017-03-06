<?php
/**
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database;

abstract class Creator implements
    \Labi\Operators\CreatorInterface
{
    abstract protected function quoteChar();

    private $adapter = null;
    private $table = null;
    private $values = array();

    private $columns = null;
    private $params = array();
    private $pparams = array();

    function __construct(\Labi\Adapters\AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function table($table = null)
    {
        if (!is_null($table)) {
            $this->table = $table;

            return $this;
        }

        return $this->table;
    }

    public function columns($columns = null)
    {
        if (is_null($columns)) {
            return $this->columns;
        }

        $this->columns = $columns;
        return $this;
    }

    public function values($values = null)
    {
        if (is_null($values)) {
            return $this->values;
        }

        $this->values = $values;
        return $this;
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

    public function add($values)
    {
        if (!is_array($values)) {
            throw new \Exception("Values should be array.");
        }

        $this->values[] = $values;

        return $this;
    }

    // + \Labi\Operators\CreatorInterface
    public function create($params = array())
    {
        $this->adapter->execute($this->toSql(), array_merge($this->params(), $this->params(true), $params));

        return true;
    }
    // - \Labi\Operators\CreatorInterface

    public function toSql()
    {
        $table = $this->table();
        $values = $this->values();
        $quoteChar = $this->quoteChar();

        if(is_null($table)){
            throw new \Exception("Define table to insert.");
        }

        if (empty($this->values)) {
            throw new \Exception("No values to insert.");
        }

        // usuwam rzeczy zwiazane z przetwarzaniem zapytania
        $this->reset(true);

        // zliczam ilosc kolumn
        $count = count($this->columns);

        // tworze naglowek
        $scolumns = "";
        for ($i=0; $i < $count; $i++) {
            $column = $this->columns[$i];

            $scolumns .= "{$quoteChar}{$column}{$quoteChar}";

            if ($i != $count-1) {
                $scolumns .= ",";
            }
        }

        // values
        $svalues = "";
        $ccount = count($this->columns);
        $vcount = count($this->values);

        for ($i=0; $i < $vcount; $i++) {
            // pobieram wszystkie wartosci
            $values = $this->values[$i];

            // pobieram klucze
            $keys = array_keys($values);

            // zmienna przetrzymuje wartosci dla VALUES w insercie
            $svalues .= "(";

            for ($j=0; $j < $ccount; $j++) {
                // nazwa kolumny
                $column = $this->columns[$j];

                if (!in_array($column, $keys)) {
                    throw new \Exception("Incorrect values to insert.");
                }

                // pobieram wartosc kolumny
                $value = $values[$column];

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
                    $svalues .= "{$value}";
                }else{
                    $uId = \Labi\Database\Utility\Uid::uId();
                    $svalues .= ":$uId";
                    $this->param($uId, $value, true);
                }

                if ($j !== $ccount-1) {
                    $svalues .= ",";
                }
            }

            $svalues .= ")";

            if ($i !== $vcount-1) {
                $svalues .= ",";
            }
        }

        // budowanie insertu
        $sql = "insert into {$quoteChar}{$table}{$quoteChar} ({$scolumns}) VALUES {$svalues}";

        return $sql;
    }
}

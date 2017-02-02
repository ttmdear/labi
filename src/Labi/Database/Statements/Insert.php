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

use Labi\Database\Statements\Statement;
use Labi\Database\Utility\Condition;
use Labi\Database\Utility\Uid;
use Labi\CreatorInterface;

class Insert extends Statement implements CreatorInterface
{
    private $adapter = null;
    private $table = null;
    private $columns = null;
    private $values = array();

    function __construct($adapter, $container)
    {
        parent::__construct($container);

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

    public function columns($columns)
    {
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

    public function add($values)
    {
        if (!is_array($values)) {
            throw new \Exception("Values should be array.");
        }

        $this->values[] = $values;

        return $this;
    }

    // + CreatorInterface
    public function create($params = array())
    {
        $sql = $this->toSql();

        $params = array_merge($this->params(), $this->params(true), $params);
        $this->adapter->execute($sql, $params);

        return $this->adapter->lastId();
    }
    // - CreatorInterface

    // + Statement
    public function toSql($params = array())
    {
        $table = $this->table();
        $values = $this->values();

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
            $scolumns .= "`{$column}`,";
        }

        // usuwam ostatni nadmiarowy przecinek
        $scolumns = trim($scolumns, ',');

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
                    $uId = Uid::uId();
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
        $sql = "insert into `{$table}` ({$scolumns}) VALUES {$svalues}";

        return $sql;
    }
    // - Statement
}

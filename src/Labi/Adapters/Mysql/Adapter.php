<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Adapters\Mysql;

use Labi\Adapters\Mysql\Searcher;
use Labi\Adapters\Mysql\Creator;
use Labi\Adapters\Mysql\Updater;
use Labi\Adapters\Mysql\Remover;

class Adapter implements \Labi\Adapters\AdapterInterface
{
    private $pdo;
    private $name;
    private $config;

    function __construct($name, $config = array())
    {
        $this->name = $name;
        $this->config = $config;
    }

    private function init()
    {
        if (!is_null($this->pdo)) {
            return;
        }

        $host = $this->config['host'];
        $dbname = $this->config['dbname'];
        $username = $this->config['username'];
        $password = $this->config['password'];
        $charset = $this->config['charset'];

        // connection
        if (!empty($password)) {
            $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
        }else{
            $this->pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username);
        }

        if (is_null($this->pdo)) {
            throw new \Exception("The connection to source {$source} cannot be established.");
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    // + AdapterInterface
    public function execute($sql, $params = array())
    {
        $statement = $this->prepare($sql, $params);
        return true;
    }

    public function fetch($sql, $params = array())
    {
        $statement = $this->prepare($sql, $params);

        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return $result;
    }

    public function searcher($class = null)
    {
        if (is_null($class)) {
            $class = Searcher::class;
        }

        return new $class($this);
    }

    public function creator($class = null)
    {
        if (is_null($class)) {
            $class = Creator::class;
        }

        return new $class($this);
    }

    public function remover($class = null)
    {
        if (is_null($class)) {
            $class = Remover::class;
        }

        return new $class($this);
    }

    public function updater($class = null)
    {
        if (is_null($class)) {
            $class = Updater::class;
        }

        return new $class($this);
    }
    // - AdapterInterface

    private function prepare($sql, $params = array())
    {
        $this->init();

        $statement = $this->pdo->prepare($sql);

        foreach ($params as $name => $value) {
            $statement->bindValue($name, $value);
        }

        $statement->execute();

        return $statement;
    }
}

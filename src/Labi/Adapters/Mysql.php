<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Adapters;

use Labi\Database\Statements\Select;
use Labi\Database\Statements\Insert;
use Labi\Database\Statements\Update;
use Labi\Database\Statements\Delete;
use Labi\Container;

class Mysql extends AdapterAbstract
{
    private $pdo;
    private $container;

    function __construct($source, $config, Container $container)
    {
        parent::__construct($source, $config, $container);

        $this->container = $container;
    }

    private function init()
    {
        if (!is_null($this->pdo)) {
            return;
        }

        $config = $this->config();

        // init pdo
        $adapter = $config['adapter'];
        $host = $config['host'];
        $dbname = $config['dbname'];
        $username = $config['username'];
        $password = $config['password'];
        $charset = $config['charset'];

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

    // + AdapterAbstract
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

    public function lastId()
    {
        $this->init();

        return $this->pdo->lastInsertId();
    }

    public function searcher()
    {
        return new Select($this, $this->container);
    }

    public function creator()
    {
        return new Insert($this, $this->container);
    }

    public function remover()
    {
        return new Delete($this, $this->container);
    }

    public function updater()
    {
        return new Update($this, $this->container);
    }
    // - AdapterAbstract

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

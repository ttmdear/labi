<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Adapters\Pgsql;

// use Database Utility
use Labi\Adapters\Pgsql\Searcher;
use Labi\Adapters\Pgsql\Creator;
use Labi\Adapters\Pgsql\Updater;
use Labi\Adapters\Pgsql\Remover;

class Adapter implements \Labi\Adapters\AdapterInterface
{
    private $pdo;
    private $name;
    private $config;

    function __construct($name, $config = array())
    {
        $this->name = $name;

        $dconfig = array(
            'host' => null,
            'dbname' => null,
            'username' => null,
            'password' => null,
            'charset' => 'utf-8',
            'options' => array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            )
        );

        $this->config = array_merge($dconfig, $config);
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
            $this->pdo = new \PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        }else{
            $this->pdo = new \PDO("pgsql:host=$host;dbname=$dbname", $username);
        }

        $this->pdo->exec("SET NAMES '$charset';");

        if (is_null($this->pdo)) {
            throw new \Exception("The connection to source {$source} cannot be established.");
        }

        foreach ($this->config['options'] as $key => $value) {
            $this->pdo->setAttribute($key, $value);
        }
    }

    // + AdapterInterface
    public function execute($sql, $params = array())
    {
        $this->init();

        foreach ($params as $name => $value) {
            $sql = str_replace(":$name", $this->pdo->quote($value), $sql);
        }

        // nie wiem czemu ale zwykle prepare dla zloznych zapytan dla sqlite
        // wykonuje tylko pierwsze polecenie exec
        $this->pdo->exec($sql);

        return true;
    }

    public function fetch($sql, $params = array(), $options = array())
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

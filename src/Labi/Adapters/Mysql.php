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

// use Database Utility
use Labi\Database\Statements\Select;
use Labi\Database\Statements\Insert;
use Labi\Database\Statements\Update;
use Labi\Database\Statements\Delete;

use Labi\Assert;
use Labi\Adapters\AdapterAbstract;

use Labi\RemoverInterface;
use Labi\UpdaterInterface;
use Labi\CreatorInterface;
use Labi\SearcherInterface;

class Mysql extends AdapterAbstract
{
    private $pdo;
    private $config = array(
        'updaterClass' => Update::class,
        'removerClass' => Delete::class,
        'creatorClass' => Insert::class,
        'searcherClass' => Select::class,
    );

    function __construct($name, $config = array())
    {
        Assert::alpha($name);
        Assert::isArray($config, "Config should be array.");

        // lacze konfiguracje z konfiguracja standardowa
        $this->config = array_merge($this->config, $config);

        Assert::keysExist($this->config, array(
            'adapter',
            'host',
            'dbname',
            'username',
            'password',
            'charset',
        ));

        Assert::implementsInterface($this->config['updaterClass'], UpdaterInterface::class);
        Assert::implementsInterface($this->config['removerClass'], RemoverInterface::class);
        Assert::implementsInterface($this->config['searcherClass'], SearcherInterface::class);
        Assert::implementsInterface($this->config['creatorClass'], CreatorInterface::class);
    }

    private function init()
    {
        if (!is_null($this->pdo)) {
            return;
        }

        // init pdo
        $adapter = $this->config['adapter'];
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

    public function searcher()
    {
        $class = $this->config['searcherClass'];
        return new $class($this);
    }

    public function creator()
    {
        $class = $this->config['creatorClass'];
        return new $class($this);
    }

    public function remover()
    {
        $class = $this->config['removerClass'];
        return new $class($this);
    }

    public function updater()
    {
        $class = $this->config['updaterClass'];
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

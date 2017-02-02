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
}

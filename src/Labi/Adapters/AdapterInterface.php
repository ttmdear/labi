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

interface AdapterInterface
{
    /**
     * Performs any type of command on adapter.
     *
     * @param string $command
     * @param array $params
     *
     * @return bool Returns true if the operation was successful.
     */
    public function execute($command, $params = array());

    /**
     * Performing a command to fetch data.
     *
     * @param string $command
     * @param array $params
     *
     * @return array
     */
    public function fetch($command, $params = array());

    /**
     * Returns object of searcher.
     *
     * @return \Labi\Operators\SearcherInterface
     */
    public function searcher($class = null);

    /**
     * Returns object of creator.
     *
     * @return \Labi\Operators\CreatorInterface
     */
    public function creator($class = null);

    /**
     * Returns object of remover.
     *
     * @return \Labi\Operators\RemoverInterface
     */
    public function remover($class = null);

    /**
     * Returns object of updater.
     *
     * @return \Labi\Operators\UpdaterInterface
     */
    public function updater($class = null);
}

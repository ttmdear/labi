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
     * @return \Labi\SearcherInterface
     */
    public function searcher();

    /**
     * Returns object of creator.
     *
     * @return \Labi\CreatorInterface
     */
    public function creator();

    /**
     * Returns object of remover.
     *
     * @return \Labi\RemoverInterface
     */
    public function remover();

    /**
     * Returns object of updater.
     *
     * @return \Labi\UpdaterInterface
     */
    public function updater();
}

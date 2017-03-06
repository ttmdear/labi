<?php
/**
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Operators;

use Labi\Adapters\AdapterInterface;

/**
 * Searcher jest obiektem odpowiedzialnym tworzenie polecenia pobierania
 * danych.
 */
interface SearcherInterface
{
    /**
     * Metoda zwraca dane.
     *
     * @params array $params Dodatkowe parametry.
     * @return array W przypadku braku danych, zwracany jest pusty array.
     */
    public function search($params = array());
}

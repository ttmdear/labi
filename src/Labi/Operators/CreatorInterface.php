<?php
/*
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
 * Creator jest obiektem odpowiedzialnym za stworzenie polecenia tworzacego
 * nowy wiersz w danym źródle.
 */
interface CreatorInterface
{
    /**
     * Wykonuje polecenie stworzenia nowego wiersza.
     *
     * @param array $params Dodatkowe parametry przy wykonywaniu zapytania.
     */
    public function create($params = array());
}

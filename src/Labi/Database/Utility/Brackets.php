<?php
/*
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database\Utility;

class Brackets
{
    private $elements = array();
    private $operator;

    function __construct()
    {
        $this->andOperator();
    }

    public function add($element)
    {
        if (count($this->elements) > 0) {
            $this->elements[] = $this->operator;
        }

        $this->elements[] = $element;

        return $this;
    }

    public function elements()
    {
        return $this->elements;
    }

    public function andOperator()
    {
        $this->operator = 'AND';
        return $this;
    }

    public function orOperator()
    {
        $this->operator = 'OR';
        return $this;
    }

}

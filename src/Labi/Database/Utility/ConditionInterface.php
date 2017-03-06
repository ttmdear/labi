<?php
/**
 * This file is part of the Labi package.
 *
 * (c) Paweł Bobryk <bobryk.pawel@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Labi\Database\Utility;

interface ConditionInterface
{
    public function brackets($function, $scope = null);
    public function andOperator();
    public function orOperator();
    public function in($column, $value);
    public function notIn($column, $value);
    public function isNull($column);
    public function isNotNull($column);
    public function startWith($column, $value);
    public function endWith($column, $value);
    public function contains($column, $value);
    public function like($column, $value);
    public function eq($column, $value);
    public function neq($column, $value);
    public function lt($column, $value);
    public function lte($column, $value);
    public function gt($column, $value);
    public function gte($column, $value);
    public function expr($expr);
    public function exists($value);
    public function notExists($value);
    public function between($column, $begin, $end);
}

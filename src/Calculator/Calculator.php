<?php

/*
 * This file is part of the Arkitekto\Math library.
 *
 * (c) Alexandru Furculita <alex@rhetina.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Arki\Math\Calculator;

use Arki\Math\RoundingMode;

/**
 * Performs basic operations on arbitrary size integers.
 *
 * All parameters must be validated as non-empty strings of digits,
 * without leading zero, and with an optional leading minus sign if the number is not zero.
 *
 * Any other parameter format will lead to undefined behaviour.
 * All methods must return strings respecting this format.
 *
 * @internal
 */
abstract class Calculator
{
    /**
     * Extracts the digits, sign, and length of the operands.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param string $leftDig  A variable to store the digits of the first operand.
     * @param string $rightDig A variable to store the digits of the second operand.
     * @param bool   $leftNeg  A variable to store whether the first operand is negative.
     * @param bool   $rightNeg A variable to store whether the second operand is negative.
     * @param bool   $leftLen  A variable to store the number of digits in the first operand.
     * @param bool   $rightLen A variable to store the number of digits in the second operand.
     */
    final protected function init($left, $right, &$leftDig, &$rightDig, &$leftNeg, &$rightNeg, &$leftLen, &$rightLen)
    {
        $leftNeg = ($left[0] === '-');
        $rightNeg = ($right[0] === '-');

        $leftDig = $leftNeg ? substr($left, 1) : $left;
        $rightDig = $rightNeg ? substr($right, 1) : $right;

        $leftLen = strlen($leftDig);
        $rightLen = strlen($rightDig);
    }

    /**
     * Returns the absolute value of a number.
     *
     * @param string $operand The number.
     *
     * @return string The absolute value.
     */
    public function abs($operand)
    {
        return ltrim($operand, '-');
    }

    /**
     * Negates a number.
     *
     * @param string $operand The number.
     *
     * @return string The negated value.
     */
    public function neg($operand)
    {
        if ($operand === '0') {
            return '0';
        }

        if ($operand[0] === '-') {
            return substr($operand, 1);
        }

        return '-'.$operand;
    }

    /**
     * Compares two numbers.
     *
     * @param string $left  The first number.
     * @param string $right The second number.
     *
     * @return int [-1, 0, 1] If the first number is less than, equal to, or greater than the second number.
     */
    abstract public function cmp($left, $right);

    /**
     * Adds two numbers.
     *
     * @param string $left  The augment.
     * @param string $right The addend.
     *
     * @return string The sum.
     */
    abstract public function add($left, $right);

    /**
     * Subtracts one number from another.
     *
     * @param string $left  The minuend.
     * @param string $right The subtrahend.
     *
     * @return string The difference.
     */
    abstract public function sub($left, $right);

    /**
     * Multiplies two numbers.
     *
     * @param string $left  The multiplicand.
     * @param string $right The multiplier.
     *
     * @return string The product.
     */
    abstract public function mul($left, $right);

    /**
     * Returns the quotient of the division of two numbers.
     *
     * @param string $left  The dividend.
     * @param string $right The divisor, must not be zero.
     *
     * @return string The quotient.
     */
    abstract public function divQ($left, $right);

    /**
     * Returns the remainder of the division of two numbers.
     *
     * @param string $left  The dividend.
     * @param string $right The divisor, must not be zero.
     *
     * @return string The remainder.
     */
    abstract public function divR($left, $right);

    /**
     * Returns the quotient and remainder of the division of two numbers.
     *
     * @param string $left  The dividend.
     * @param string $right The divisor, must not be zero.
     *
     * @return string[] An array containing the quotient and remainder.
     */
    abstract public function divQR($left, $right);

    /**
     * Exponentiates a number.
     *
     * @param string $left     The base.
     * @param int    $exponent The exponent, validated as an integer between 0 and MAX_POWER.
     *
     * @return string The power.
     */
    abstract public function pow($left, $exponent);

    /**
     * Returns the greatest common divisor of the two numbers.
     *
     * This method can be overridden by the concrete implementation if the underlying library
     * has built-in support for GCD calculations.
     *
     * @param string $left  The first number.
     * @param string $right The second number.
     *
     * @return string The GCD, always positive, or zero if both arguments are zero.
     */
    public function gcd($left, $right)
    {
        if ($left === '0') {
            return $this->abs($right);
        }

        if ($right === '0') {
            return $this->abs($left);
        }

        return $this->gcd($right, $this->divR($left, $right));
    }

    /**
     * Performs a rounded division.
     *
     * Rounding is performed when the remainder of the division is not zero.
     *
     * @param string $left         The dividend.
     * @param string $right        The divisor.
     * @param int    $roundingMode The rounding mode.
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the rounding mode is invalid.
     * @throws \ArithmeticError          If RoundingMode::UNNECESSARY is provided but rounding is necessary.
     */
    public function divRound($left, $right, $roundingMode)
    {
        list($quotient, $remainder) = $this->divQR($left, $right);

        $hasDiscardedFraction = ($remainder !== '0');
        $isPositiveOrZero = ($left[0] === '-') === ($right[0] === '-');

        $discardedFractionSign = function () use ($remainder, $right) {
            $r = $this->abs($this->mul($remainder, '2'));
            $right = $this->abs($right);

            return $this->cmp($r, $right);
        };

        $increment = false;

        switch ($roundingMode) {
            case RoundingMode::UNNECESSARY:
                if ($hasDiscardedFraction) {
                    throw new \ArithmeticError(
                        'Rounding is necessary to represent the result of the operation at this scale.'
                    );
                }
                break;

            case RoundingMode::UP:
                $increment = $hasDiscardedFraction;
                break;

            case RoundingMode::DOWN:
                break;

            case RoundingMode::CEILING:
                $increment = $hasDiscardedFraction && $isPositiveOrZero;
                break;

            case RoundingMode::FLOOR:
                $increment = $hasDiscardedFraction && !$isPositiveOrZero;
                break;

            case RoundingMode::HALF_UP:
                $increment = $discardedFractionSign() >= 0;
                break;

            case RoundingMode::HALF_DOWN:
                $increment = $discardedFractionSign() > 0;
                break;

            case RoundingMode::HALF_CEILING:
                $increment = $isPositiveOrZero ? $discardedFractionSign() >= 0 : $discardedFractionSign() > 0;
                break;

            case RoundingMode::HALF_FLOOR:
                $increment = $isPositiveOrZero ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;

            case RoundingMode::HALF_EVEN:
                $lastDigit = (int) substr($quotient, -1);
                $lastDigitIsEven = ($lastDigit % 2 === 0);
                $increment = $lastDigitIsEven ? $discardedFractionSign() > 0 : $discardedFractionSign() >= 0;
                break;

            default:
                throw new \InvalidArgumentException('Invalid rounding mode.');
        }

        if ($increment) {
            return $this->add($quotient, $isPositiveOrZero ? '1' : '-1');
        }

        return $quotient;
    }
}

<?php

/*
 * This file is part of the Arkitekto\Math library.
 *
 * (c) Alexandru Furculita <alex@rhetina.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Arki\Math;

use Arki\Math\Calculator\BcMathCalculator;
use Arki\Math\Calculator\Calculator;
use Arki\Math\Calculator\GmpCalculator;
use Arki\Math\Calculator\NativeCalculator;

/**
 * The class Math contains methods for performing basic numeric operations.
 */
final class Math
{
    /**
     * The maximum exponent value allowed for the pow() method.
     */
    const MAX_POWER = 1000000;

    /**
     * The Calculator instance in use.
     *
     * @var Calculator|null
     */
    private static $calculator;

    /**
     * @var Calculator[]
     */
    private static $calculators = [
        BcMathCalculator::class,
        GmpCalculator::class,
        NativeCalculator::class,
    ];

    /**
     * This can't be constructed.
     */
    private function __construct()
    {
    }

    /**
     * Sets the Calculator instance to use.
     *
     * An instance is typically set only in unit tests: the autodetect is usually the best option.
     *
     * @param Calculator|null $calculator The calculator instance, or NULL to revert to autodetect.
     */
    public static function with(Calculator $calculator = null)
    {
        self::$calculator = $calculator;
    }

    /**
     * Returns the absolute value of a number.
     *
     * @param string $operand The number.
     *
     * @return string The absolute value.
     */
    public static function abs($operand)
    {
        return self::get()->abs($operand);
    }

    /**
     * Negates a number.
     *
     * @param string $operand The number.
     *
     * @return string The negated value.
     */
    public static function neg($operand)
    {
        return self::get()->neg($operand);
    }

    /**
     * Compares two numbers.
     *
     * @param string $left  The first number.
     * @param string $right The second number.
     *
     * @return int [-1, 0, 1] If the first number is less than, equal to, or greater than the second number.
     */
    public static function cmp($left, $right)
    {
        return self::get()->cmp($left, $right);
    }

    /**
     * Adds two numbers.
     *
     * @param string $left  The augment.
     * @param string $right The addend.
     *
     * @return string The sum.
     */
    public static function add($left, $right)
    {
        return self::get()->add($left, $right);
    }

    /**
     * Subtracts one number from another.
     *
     * @param string $left  The minuend.
     * @param string $right The subtrahend.
     *
     * @return string The difference.
     */
    public static function sub($left, $right)
    {
        return self::get()->sub($left, $right);
    }

    /**
     * Multiplies two numbers.
     *
     * @param string $left  The multiplicand.
     * @param string $right The multiplier.
     *
     * @return string The product.
     */
    public static function mul($left, $right)
    {
        return self::get()->mul($left, $right);
    }

    /**
     * Returns the quotient of the division of two numbers.
     *
     * @param string $left  The dividend.
     * @param string $right The divisor, must not be zero.
     *
     * @return string The quotient.
     */
    public static function divQ($left, $right)
    {
        return self::get()->divQ($left, $right);
    }

    /**
     * Returns the remainder of the division of two numbers.
     *
     * @param string $left  The dividend.
     * @param string $right The divisor, must not be zero.
     *
     * @return string The remainder.
     */
    public static function divR($left, $right)
    {
        return self::get()->divR($left, $right);
    }

    /**
     * Returns the quotient and remainder of the division of two numbers.
     *
     * @param string $left  The dividend.
     * @param string $right The divisor, must not be zero.
     *
     * @return string[] An array containing the quotient and remainder.
     */
    public static function divQR($left, $right)
    {
        return self::get()->divQR($left, $right);
    }

    /**
     * Exponentiates a number.
     *
     * @param string $left     The base.
     * @param int    $exponent The exponent, validated as an integer between 0 and MAX_POWER.
     *
     * @return string The power.
     */
    public static function pow($left, $exponent)
    {
        return self::get()->pow($left, $exponent);
    }

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
    public static function gcd($left, $right)
    {
        return self::get()->gcd($left, $right);
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
    public static function divRound($left, $right, $roundingMode)
    {
        return self::get()->divRound($left, $right, $roundingMode);
    }

    /**
     * Returns the Calculator instance to use.
     *
     * If none has been explicitly set, the fastest available implementation will be returned.
     *
     * @return Calculator
     */
    private static function get()
    {
        if (self::$calculator === null) {
            self::$calculator = self::initializeCalculator();
        }

        return self::$calculator;
    }

    /**
     * @return Calculator
     *
     * @throws \RuntimeException If cannot find calculator for math calculations
     */
    private static function initializeCalculator()
    {
        foreach (self::$calculators as $calculator) {
            if ($calculator::supported()) {
                return new $calculator();
            }
        }

        throw new \RuntimeException('Cannot find calculator for math calculations');
    }
}

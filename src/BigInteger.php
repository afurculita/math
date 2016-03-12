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

use Arki\Math\Calculator\Calculator;
use Arki\Math\Exception\NumberFormatException;

/**
 * Immutable arbitrary-precision integers.
 *
 * All methods accepting a number as a parameter accept either a BigInteger instance,
 * an integer, or a string representing an arbitrary size integer.
 *
 * Semantics of arithmetic operations exactly mimic those of PHP's integer
 * arithmetic operators, as defined in The PHP Language Specification.
 * For example, division by zero throws an ArithmeticException, and division of a
 * negative by a positive yields a negative (or zero) remainder. All of the details
 * in the Spec concerning overflow are ignored, as BigIntegers are made as large as
 * necessary to accommodate the results of an operation.
 */
final class BigInteger extends Number implements \Serializable
{
    /**
     * The value, as a string of digits with optional leading minus sign.
     *
     * No leading zeros must be present.
     * No leading minus sign must be present if the number is zero.
     *
     * @var string
     */
    private $value;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param string $value A string of digits, with optional leading minus sign.
     */
    protected function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Creates a BigInteger of the given value.
     *
     * @param Number|int|float|string $value
     *
     * @return BigInteger
     *
     * @throws \ArithmeticError If the value cannot be converted to a BigInteger.
     */
    public static function of($value)
    {
        return parent::of($value)->toBigInteger();
    }

    /**
     * Parses a string containing an integer in an arbitrary base.
     *
     * The characters in the string must all be digits of the specified base, except that
     * the first character may be an ASCII minus sign '-' ('\u002D') to indicate a negative
     * value or an ASCII plus sign '+' ('\u002B') to indicate a positive value.
     *
     * @param string $number The string containing the integer representation to be parsed.
     * @param int    $base   The base of the number, between 2 and 36.
     *
     * @return BigInteger The integer represented by the string argument in the specified base.
     *
     * @throws NumberFormatException If the string does not contain a parsable int.
     * @throws \InvalidArgumentException If the base is out of range.
     */
    public static function parse($number, $base = 10)
    {
        $number = (string)$number;
        $base = (int)$base;

        if ($number === '') {
            throw new NumberFormatException('The value cannot be empty.');
        }

        if ($base < 2 || $base > 36) {
            throw new \InvalidArgumentException(sprintf('Base %d is not in range 2 to 36.', $base));
        }

        if ($number[0] === '-') {
            $sign = '-';
            $number = substr($number, 1);
        } elseif ($number[0] === '+') {
            $sign = '';
            $number = substr($number, 1);
        } else {
            $sign = '';
        }

        if ($number === false /* PHP 5 */ || $number === '' /* PHP 7 */) {
            throw new NumberFormatException('The value cannot be empty.');
        }

        $number = ltrim($number, '0');
        if ($number === '') {
            // The result will be the same in any base, avoid further calculation.
            return self::zero();
        }

        if ($number === '1') {
            // The result will be the same in any base, avoid further calculation.
            return new self($sign.'1');
        }

        if ($base === 10 && ctype_digit($number)) {
            // The number is usable as is, avoid further calculation.
            return new self($sign.$number);
        }

        $calc = Calculator::get();
        $number = strtolower($number);
        $result = '0';
        $power = '1';
        $dictionary = '0123456789abcdefghijklmnopqrstuvwxyz';

        for ($i = strlen($number) - 1; $i >= 0; --$i) {
            $char = $number[$i];
            $index = strpos($dictionary, $char);

            if ($index === false || $index >= $base) {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid character in base %d.', $char, $base));
            }

            if ($index !== 0) {
                $add = ($index === 1) ? $power : $calc->mul($power, (string)$index);
                $result = $calc->add($result, $add);
            }

            $power = $calc->mul($power, (string)$base);
        }

        return new self($sign.$result);
    }

    /**
     * Returns a BigInteger representing zero.
     *
     * @return BigInteger
     */
    public static function zero()
    {
        static $zero;
        if ($zero === null) {
            $zero = new self('0');
        }

        return $zero;
    }

    /**
     * Returns a BigInteger representing one.
     *
     * @return BigInteger
     */
    public static function one()
    {
        static $one;
        if ($one === null) {
            $one = new self('1');
        }

        return $one;
    }

    /**
     * Returns a BigInteger representing ten.
     *
     * @return BigInteger
     */
    public static function ten()
    {
        static $ten;
        if ($ten === null) {
            $ten = new self('10');
        }

        return $ten;
    }

    /**
     * Returns the sum of this number and the given one.
     *
     * @param Number|int|float|string $that The number to add. Must be convertible to a BigInteger.
     *
     * @return BigInteger The result.
     *
     * @throws \ArithmeticError If the number is not valid, or is not convertible to a BigInteger.
     */
    public function plus($that)
    {
        $that = self::of($that);
        if ($that->value === '0') {
            return $this;
        }
        $value = Calculator::get()->add($this->value, $that->value);

        return new self($value);
    }

    /**
     * Returns the difference of this number and the given one.
     *
     * @param Number|int|float|string $that The number to subtract. Must be convertible to a BigInteger.
     *
     * @return BigInteger The result.
     *
     * @throws \ArithmeticError If the number is not valid, or is not convertible to a BigInteger.
     */
    public function minus($that)
    {
        $that = self::of($that);
        if ($that->value === '0') {
            return $this;
        }
        $value = Calculator::get()->sub($this->value, $that->value);

        return new self($value);
    }

    /**
     * Returns the product of this number and the given one.
     *
     * @param Number|int|float|string $that The multiplier. Must be convertible to a BigInteger.
     *
     * @return BigInteger The result.
     *
     * @throws \ArithmeticError If the multiplier is not a valid number, or is not convertible to a BigInteger.
     */
    public function multipliedBy($that)
    {
        $that = self::of($that);
        if ($that->value === '1') {
            return $this;
        }
        $value = Calculator::get()->mul($this->value, $that->value);

        return new self($value);
    }

    /**
     * Returns the result of the division of this number by the given one.
     *
     * @param Number|int|float|string $that         The divisor. Must be convertible to a BigInteger.
     * @param int                     $roundingMode An optional rounding mode.
     *
     * @return BigInteger The result.
     *
     * @throws \ArithmeticError If the divisor is not a valid number, is not convertible to a BigInteger,
     *                             or RoundingMode::UNNECESSARY is used and the remainder is not zero.
     * @throws \DivisionByZeroError If the divisor is zero
     */
    public function dividedBy($that, $roundingMode = RoundingMode::UNNECESSARY)
    {
        $that = self::of($that);
        if ($that->value === '1') {
            return $this;
        }
        if ($that->value === '0') {
            throw new \DivisionByZeroError();
        }
        $result = Calculator::get()->divRound($this->value, $that->value, $roundingMode);

        return new self($result);
    }

    /**
     * Returns this number exponentiated to the given value.
     *
     * @param int $exponent The exponent.
     *
     * @return BigInteger The result.
     *
     * @throws \InvalidArgumentException If the exponent is not in the range 0 to 1,000,000.
     */
    public function power($exponent)
    {
        $exponent = (int)$exponent;
        if ($exponent === 0) {
            return self::one();
        }
        if ($exponent === 1) {
            return $this;
        }
        if ($exponent < 0 || $exponent > Calculator::MAX_POWER) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The exponent %d is not in the range 0 to %d.',
                    $exponent,
                    Calculator::MAX_POWER
                )
            );
        }

        return new self(Calculator::get()->pow($this->value, $exponent));
    }

    /**
     * Returns the quotient of the division of this number by the given one.
     *
     * @param Number|int|float|string $that The divisor. Must be convertible to a BigInteger.
     *
     * @return BigInteger
     *
     * @throws \DivisionByZeroError If the divisor is zero.
     */
    public function quotient($that)
    {
        $that = self::of($that);
        if ($that->value === '1') {
            return $this;
        }
        if ($that->value === '0') {
            throw new \DivisionByZeroError();
        }
        $quotient = Calculator::get()->divQ($this->value, $that->value);

        return new self($quotient);
    }

    /**
     * Returns the remainder of the division of this number by the given one.
     *
     * @param Number|int|float|string $that The divisor. Must be convertible to a BigInteger.
     *
     * @return BigInteger
     *
     * @throws \DivisionByZeroError If the divisor is zero.
     */
    public function remainder($that)
    {
        $that = self::of($that);
        if ($that->value === '0') {
            throw new \DivisionByZeroError();
        }
        $remainder = Calculator::get()->divR($this->value, $that->value);

        return new self($remainder);
    }

    /**
     * Returns the quotient and remainder of the division of this number by the given one.
     *
     * @param Number|int|float|string $that The divisor. Must be convertible to a BigInteger.
     *
     * @return BigInteger[] An array containing the quotient and the remainder.
     *
     * @throws \DivisionByZeroError If the divisor is zero.
     */
    public function quotientAndRemainder($that)
    {
        $that = self::of($that);
        if ($that->value === '0') {
            throw new \DivisionByZeroError();
        }
        list($quotient, $remainder) = Calculator::get()->divQR($this->value, $that->value);

        return [
            new self($quotient),
            new self($remainder),
        ];
    }

    /**
     * Returns a BigInteger whose value is the greatest common divisor of this number and the given one.
     *
     * The GCD is always positive, unless both operands are zero, in which case it is zero.
     *
     * @param Number|int|float|string $that The operand. Must be convertible to an integer number.
     *
     * @return BigInteger
     */
    public function gcd($that)
    {
        $that = self::of($that);
        if ($that->value === '0' && $this->value[0] !== '-') {
            return $this;
        }
        if ($this->value === '0' && $that->value[0] !== '-') {
            return $that;
        }
        $value = Calculator::get()->gcd($this->value, $that->value);

        return new self($value);
    }

    /**
     * Returns a BigInteger whose value is the absolute value of this BigInteger.
     *
     * @return BigInteger abs($this)
     */
    public function abs()
    {
        return $this->isNegative() ? $this->negate() : $this;
    }

    /**
     * Returns a BigInteger whose value is (-this).
     *
     * @return BigInteger -$this
     */
    public function negate()
    {
        return new self(Calculator::get()->neg($this->value));
    }

    /**
     * {@inheritdoc}
     */
    public function compareTo($that)
    {
        $that = Number::of($that);
        if ($that instanceof self) {
            return Calculator::get()->cmp($this->value, $that->value);
        }

        return -$that->compareTo($this);
    }

    /**
     * {@inheritdoc}
     */
    public function signum()
    {
        return ($this->value === '0') ? 0 : (($this->value[0] === '-') ? -1 : 1);
    }

    /**
     * {@inheritdoc}
     */
    public function toBigInteger()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toBigDecimal()
    {
        return BigDecimal::create($this->value);
    }

    /**
     * {@inheritdoc}
     */
    public function toBigRational()
    {
        return BigRational::create($this, self::one(), false);
    }

    /**
     * {@inheritdoc}
     */
    public function toScale($scale, $roundingMode = RoundingMode::UNNECESSARY)
    {
        return $this->toBigDecimal()->toScale($scale, $roundingMode);
    }

    /**
     * {@inheritdoc}
     */
    public function toInteger()
    {
        if ($this->isLessThan(~PHP_INT_MAX) || $this->isGreaterThan(PHP_INT_MAX)) {
            throw new \ArithmeticError(
                sprintf(
                    '%s is out of range %d to %d and cannot be represented as an integer.',
                    (string)$this,
                    ~PHP_INT_MAX,
                    PHP_INT_MAX
                )
            );
        }

        return (int)$this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function toFloat()
    {
        return (float)$this->value;
    }

    /**
     * Returns a string representation of this number in the given base.
     *
     * @param int $base
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the base is out of range.
     */
    public function toBase($base)
    {
        $base = (int)$base;
        if ($base === 10) {
            return $this->value;
        }
        if ($base < 2 || $base > 36) {
            throw new \InvalidArgumentException(sprintf('Base %d is out of range [2, 36]', $base));
        }
        $dictionary = '0123456789abcdefghijklmnopqrstuvwxyz';
        $calc = Calculator::get();
        $value = $this->value;
        $negative = ($value[0] === '-');
        if ($negative) {
            $value = substr($value, 1);
        }
        $base = (string)$base;
        $result = '';
        while ($value !== '0') {
            list($value, $remainder) = $calc->divQR($value, $base);
            $remainder = (int)$remainder;
            $result .= $dictionary[$remainder];
        }
        if ($negative) {
            $result .= '-';
        }

        return strrev($result);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * This method is required by interface Serializable and SHOULD NOT be accessed directly.
     *
     * @internal
     *
     * @return string
     */
    public function serialize()
    {
        return $this->value;
    }

    /**
     * This method is required by interface Serializable and MUST NOT be accessed directly.
     *
     * @internal
     *
     * @param string $value
     *
     * @throws \LogicException
     */
    public function unserialize($value)
    {
        if ($this->value !== null) {
            throw new \LogicException('unserialize() is an internal function, it must not be called directly.');
        }

        $this->value = $value;
    }
}

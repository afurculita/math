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

use Arki\Math\Exception\NumberFormatException;

/**
 * An arbitrarily large rational number.
 *
 * This class is immutable.
 */
final class BigRational extends Number implements \Serializable
{
    /**
     * The numerator.
     *
     * @var BigInteger
     */
    private $numerator;
    /**
     * The denominator. Must not be zero.
     *
     * @var BigInteger
     */
    private $denominator;

    /**
     * Protected constructor. Use a factory method to obtain an instance.
     *
     * @param BigInteger $numerator        The numerator.
     * @param BigInteger $denominator      The denominator.
     * @param bool       $checkDemominator Whether to check the denominator for negative and zero.
     *
     * @throws \DivisionByZeroError If the denominator is zero.
     */
    protected function __construct(BigInteger $numerator, BigInteger $denominator, $checkDemominator)
    {
        if ($checkDemominator) {
            if ($denominator->isZero()) {
                throw new \DivisionByZeroError('The denominator of a rational number cannot be zero.');
            }
            if ($denominator->isNegative()) {
                $numerator = $numerator->negate();
                $denominator = $denominator->negate();
            }
        }
        $this->numerator = $numerator;
        $this->denominator = $denominator;
    }

    /**
     * Creates a BigRational of the given value.
     *
     * @param \Arki\Math\Number|int|float|string $value
     *
     * @return BigRational
     *
     * @throws \ArithmeticError If the value cannot be converted to a BigRational.
     */
    public static function of($value)
    {
        return parent::of($value)->toBigRational();
    }

    /**
     * Creates a BigRational out of a numerator and a denominator.
     *
     * If the denominator is negative, the signs of both the numerator and the denominator
     * will be inverted to ensure that the denominator is always positive.
     *
     * @param \Arki\Math\Number|int|float|string $numerator   The numerator. Must be convertible to a BigInteger.
     * @param \Arki\Math\Number|int|float|string $denominator The denominator. Must be convertible to a BigInteger.
     *
     * @return BigRational
     *
     * @throws NumberFormatException If an argument does not represent a valid number.
     * @throws \ArithmeticError      If an argument represents a non-integer number.
     * @throws \DivisionByZeroError  If the denominator is zero.
     */
    public static function nd($numerator, $denominator)
    {
        $numerator = BigInteger::of($numerator);
        $denominator = BigInteger::of($denominator);

        return new self($numerator, $denominator, true);
    }

    /**
     * Returns a BigRational representing zero.
     *
     * @return BigRational
     */
    public static function zero()
    {
        static $zero;
        if ($zero === null) {
            $zero = new self(BigInteger::zero(), BigInteger::one(), false);
        }

        return $zero;
    }

    /**
     * Returns a BigRational representing one.
     *
     * @return BigRational
     */
    public static function one()
    {
        static $one;
        if ($one === null) {
            $one = new self(BigInteger::one(), BigInteger::one(), false);
        }

        return $one;
    }

    /**
     * Returns a BigRational representing ten.
     *
     * @return BigRational
     */
    public static function ten()
    {
        static $ten;
        if ($ten === null) {
            $ten = new self(BigInteger::ten(), BigInteger::one(), false);
        }

        return $ten;
    }

    /**
     * @return BigInteger
     */
    public function numerator()
    {
        return $this->numerator;
    }

    /**
     * @return BigInteger
     */
    public function denominator()
    {
        return $this->denominator;
    }

    /**
     * Returns the quotient of the division of the numerator by the denominator.
     *
     * @return BigInteger
     */
    public function quotient()
    {
        return $this->numerator->quotient($this->denominator);
    }

    /**
     * Returns the remainder of the division of the numerator by the denominator.
     *
     * @return BigInteger
     */
    public function remainder()
    {
        return $this->numerator->remainder($this->denominator);
    }

    /**
     * Returns the quotient and remainder of the division of the numerator by the denominator.
     *
     * @return BigInteger[]
     */
    public function quotientAndRemainder()
    {
        return $this->numerator->quotientAndRemainder($this->denominator);
    }

    /**
     * Returns the sum of this number and the given one.
     *
     * @param \Arki\Math\Number|int|float|string $that The number to add.
     *
     * @return BigRational The result.
     *
     * @throws \ArithmeticError If the number is not valid.
     */
    public function plus($that)
    {
        $that = self::of($that);
        $numerator = $this->numerator->multipliedBy($that->denominator);
        $numerator = $numerator->plus($that->numerator->multipliedBy($this->denominator));
        $denominator = $this->denominator->multipliedBy($that->denominator);

        return new self($numerator, $denominator, false);
    }

    /**
     * Returns the difference of this number and the given one.
     *
     * @param \Arki\Math\Number|int|float|string $that The number to subtract.
     *
     * @return BigRational The result.
     *
     * @throws \ArithmeticError If the number is not valid.
     */
    public function minus($that)
    {
        $that = self::of($that);
        $numerator = $this->numerator->multipliedBy($that->denominator);
        $numerator = $numerator->minus($that->numerator->multipliedBy($this->denominator));
        $denominator = $this->denominator->multipliedBy($that->denominator);

        return new self($numerator, $denominator, false);
    }

    /**
     * Returns the product of this number and the given one.
     *
     * @param \Arki\Math\Number|int|float|string $that The multiplier.
     *
     * @return BigRational The result.
     *
     * @throws \ArithmeticError If the multiplier is not a valid number.
     */
    public function multipliedBy($that)
    {
        $that = self::of($that);
        $numerator = $this->numerator->multipliedBy($that->numerator);
        $denominator = $this->denominator->multipliedBy($that->denominator);

        return new self($numerator, $denominator, false);
    }

    /**
     * Returns the result of the division of this number by the given one.
     *
     * @param \Arki\Math\Number|int|float|string $that The divisor.
     *
     * @return BigRational The result.
     *
     * @throws \ArithmeticError     If the divisor is not a valid number.
     * @throws \DivisionByZeroError If the divisor is zero.
     */
    public function dividedBy($that)
    {
        $that = self::of($that);
        $numerator = $this->numerator->multipliedBy($that->denominator);
        $denominator = $this->denominator->multipliedBy($that->numerator);

        return new self($numerator, $denominator, true);
    }

    /**
     * Returns this number exponentiated to the given value.
     *
     * @param int $exponent The exponent.
     *
     * @return BigRational The result.
     *
     * @throws \InvalidArgumentException If the exponent is not in the range 0 to 1,000,000.
     */
    public function power($exponent)
    {
        $exponent = (int) $exponent;
        if ($exponent === 0) {
            $one = BigInteger::one();

            return new self($one, $one, false);
        }
        if ($exponent === 1) {
            return $this;
        }

        return new self(
            $this->numerator->power($exponent),
            $this->denominator->power($exponent),
            false
        );
    }

    /**
     * Returns the reciprocal of this BigRational.
     *
     * The reciprocal has the numerator and denominator swapped.
     *
     * @return BigRational
     *
     * @throws \DivisionByZeroError If the numerator is zero.
     */
    public function reciprocal()
    {
        return new self($this->denominator, $this->numerator, true);
    }

    /**
     * Returns the absolute value of this BigRational.
     *
     * @return BigRational
     */
    public function abs()
    {
        return new self($this->numerator->abs(), $this->denominator, false);
    }

    /**
     * Returns the negated value of this BigRational.
     *
     * @return BigRational
     */
    public function negate()
    {
        return new self($this->numerator->negate(), $this->denominator, false);
    }

    /**
     * Returns the simplified value of this BigRational.
     *
     * @return BigRational
     */
    public function simplified()
    {
        $gcd = $this->numerator->gcd($this->denominator);
        $numerator = $this->numerator->quotient($gcd);
        $denominator = $this->denominator->quotient($gcd);

        return new self($numerator, $denominator, false);
    }

    /**
     * {@inheritdoc}
     */
    public function compareTo($that)
    {
        return $this->minus($that)->signum();
    }

    /**
     * {@inheritdoc}
     */
    public function signum()
    {
        return $this->numerator->signum();
    }

    /**
     * {@inheritdoc}
     */
    public function toBigInteger()
    {
        $simplified = $this->simplified();
        if (!$simplified->denominator->isEqualTo(1)) {
            throw new \ArithmeticError(
                'This rational number cannot be represented as an integer value without rounding.'
            );
        }

        return $simplified->numerator;
    }

    /**
     * {@inheritdoc}
     */
    public function toBigDecimal()
    {
        return $this->numerator->toBigDecimal()->exactlyDividedBy($this->denominator);
    }

    /**
     * {@inheritdoc}
     */
    public function toBigRational()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toScale($scale, $roundingMode = RoundingMode::UNNECESSARY)
    {
        return $this->numerator->toBigDecimal()->dividedBy($this->denominator, $scale, $roundingMode);
    }

    /**
     * {@inheritdoc}
     */
    public function toInteger()
    {
        return $this->toBigInteger()->toInteger();
    }

    /**
     * {@inheritdoc}
     */
    public function toFloat()
    {
        return $this->numerator->toFloat() / $this->denominator->toFloat();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $numerator = (string) $this->numerator;
        $denominator = (string) $this->denominator;
        if ($denominator === '1') {
            return $numerator;
        }

        return $numerator.'/'.$denominator;
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
        return $this->numerator.'/'.$this->denominator;
    }

    /**
     * This method is required by interface Serializable and MUST NOT be accessed directly.
     *
     * @internal
     *
     * @param string $value
     */
    public function unserialize($value)
    {
        if ($this->numerator !== null || $this->denominator !== null) {
            throw new \LogicException('unserialize() is an internal function, it must not be called directly.');
        }

        list($numerator, $denominator) = explode('/', $value);
        $this->numerator = BigInteger::of($numerator);
        $this->denominator = BigInteger::of($denominator);
    }
}

<?php

declare (strict_types = 1);

/*
 * This file is part of the Arkitekto\Math library.
 *
 * (c) Alexandru Furculita <alex@rhetina.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Arki\Math\Calculator;

/**
 * Calculator implementation built around the BCMath (Binary Calculator) library.
 *
 * @link http://php.net/manual/ro/book.bc.php
 *
 * @internal
 */
final class BcMathCalculator extends Calculator
{
    /**
     * Sets BCMath scale factor to zero.
     */
    public function __construct()
    {
        bcscale(0);
    }

    /**
     * {@inheritdoc}
     */
    public static function supported()
    {
        return extension_loaded('bcmath');
    }

    /**
     * {@inheritdoc}
     */
    public function add($left, $right)
    {
        return bcadd($left, $right);
    }

    /**
     * {@inheritdoc}
     */
    public function sub($left, $right)
    {
        return bcsub($left, $right);
    }

    /**
     * {@inheritdoc}
     */
    public function mul($left, $right)
    {
        return bcmul($left, $right);
    }

    /**
     * {@inheritdoc}
     */
    public function divQ($left, $right)
    {
        return bcdiv($left, $right);
    }

    /**
     * {@inheritdoc}
     */
    public function divR($left, $right)
    {
        return bcmod($left, $right);
    }

    /**
     * {@inheritdoc}
     */
    public function divQR($left, $right)
    {
        $quotient = bcdiv($left, $right);
        $remainder = bcmod($left, $right);

        return [$quotient, $remainder];
    }

    /**
     * {@inheritdoc}
     */
    public function pow($left, $exponent)
    {
        return bcpow($left, (string)$exponent);
    }

    /**
     * {@inheritdoc}
     */
    public function cmp($left, $right)
    {
        return bccomp($left, $right);
    }
}

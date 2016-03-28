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
 * Calculator implementation built around the GMP (GNU Multiple Precision) library.
 *
 * @link http://php.net/manual/ro/book.gmp.php
 *
 * @internal
 */
final class GmpCalculator extends Calculator
{
    /**
     * {@inheritdoc}
     */
    public function add($left, $right)
    {
        return gmp_strval(gmp_add($left, $right));
    }

    /**
     * {@inheritdoc}
     */
    public function sub($left, $right)
    {
        return gmp_strval(gmp_sub($left, $right));
    }

    /**
     * {@inheritdoc}
     */
    public function mul($left, $right)
    {
        return gmp_strval(gmp_mul($left, $right));
    }

    /**
     * {@inheritdoc}
     */
    public function divQ($left, $right)
    {
        return gmp_strval(gmp_div_q($left, $right));
    }

    /**
     * {@inheritdoc}
     */
    public function divR($left, $right)
    {
        return gmp_strval(gmp_div_r($left, $right));
    }

    /**
     * {@inheritdoc}
     */
    public function divQR($left, $right)
    {
        list($quotient, $remainder) = gmp_div_qr($left, $right);

        return [
            gmp_strval($quotient),
            gmp_strval($remainder),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function pow($left, $exponent)
    {
        return gmp_strval(gmp_pow($left, $exponent));
    }

    /**
     * {@inheritdoc}
     */
    public function gcd($left, $right)
    {
        return gmp_strval(gmp_gcd($left, $right));
    }

    /**
     * {@inheritdoc}
     */
    public function cmp($left, $right)
    {
        $result = gmp_cmp($left, $right);

        if ($result < 0) {
            $result = -1;
        } elseif ($result > 0) {
            $result = 1;
        }

        return $result;
    }
}

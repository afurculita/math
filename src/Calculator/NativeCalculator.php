<?php

declare (strict_types = 1);

/*
 * This file is part of the Arkitekto\Math library.
 *
 * (c) Alexandru Furculita <alex@furculita.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Arki\Math\Calculator;

/**
 * Calculator implementation using only native PHP code.
 *
 * @internal
 */
final class NativeCalculator extends Calculator
{
    /**
     * The max number of digits the platform can natively add, subtract or divide without overflow.
     *
     * @var int
     */
    private static $maxDigitsAddDiv = 0;
    /**
     * The max number of digits the platform can natively multiply without overflow.
     *
     * @var int
     */
    private static $maxDigitsMul = 0;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        switch (PHP_INT_SIZE) {
            case 8: // use 64-bit integers if int size is 8 bytes
                self::$maxDigitsAddDiv = 18;
                self::$maxDigitsMul = 9;
                break;
            //case 4: // use 64-bit floats if int size is 4 bytes
            default:
                self::$maxDigitsAddDiv = 9;
                self::$maxDigitsMul = 4;
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add($left, $right)
    {
        if ($left === '0') {
            return $right;
        }

        if ($right === '0') {
            return $left;
        }

        $this->init($left, $right, $leftDig, $rightDig, $leftNeg, $rightNeg, $leftLen, $rightLen);

        if ($leftLen <= self::$maxDigitsAddDiv && $rightLen <= self::$maxDigitsAddDiv) {
            return (string) ((int) $left + (int) $right);
        }

        if ($leftNeg === $rightNeg) {
            $result = $this->doAdd($leftDig, $rightDig, $leftLen, $rightLen);
        } else {
            $result = $this->doSub($leftDig, $rightDig, $leftLen, $rightLen);
        }

        if ($leftNeg) {
            $result = $this->neg($result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function sub($left, $right)
    {
        return $this->add($left, $this->neg($right));
    }

    /**
     * {@inheritdoc}
     */
    public function mul($left, $right)
    {
        if ($left === '0' || $right === '0') {
            return '0';
        }

        if ($left === '1') {
            return $right;
        }

        if ($right === '1') {
            return $left;
        }

        if ($left === '-1') {
            return $this->neg($right);
        }

        if ($right === '-1') {
            return $this->neg($left);
        }

        $this->init($left, $right, $leftDig, $rightDig, $leftNeg, $rightNeg, $leftLen, $rightLen);

        if ($leftLen <= self::$maxDigitsMul && $rightLen <= self::$maxDigitsMul) {
            return (string) ((int) $left * (int) $right);
        }

        $result = $this->doMul($leftDig, $rightDig, $leftLen, $rightLen);

        if ($leftNeg !== $rightNeg) {
            $result = $this->neg($result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function divQ($left, $right)
    {
        return $this->divQR($left, $right)[0];
    }

    /**
     * {@inheritdoc}
     */
    public function divR($left, $right)
    {
        return $this->divQR($left, $right)[1];
    }

    /**
     * {@inheritdoc}
     */
    public function divQR($left, $right)
    {
        if ($left === '0') {
            return ['0', '0'];
        }

        if ($left === $right) {
            return ['1', '0'];
        }

        if ($right === '1') {
            return [$left, '0'];
        }

        if ($right === '-1') {
            return [$this->neg($left), '0'];
        }

        $this->init($left, $right, $leftDig, $rightDig, $leftNeg, $rightNeg, $leftLen, $rightLen);

        if ($leftLen <= self::$maxDigitsAddDiv && $rightLen <= self::$maxDigitsAddDiv) {
            $left = (int) $left;
            $right = (int) $right;

            $rem = $left % $right;
            $quot = ($left - $rem) / $right;

            $quot = (string) $quot;
            $rem = (string) $rem;

            return [$quot, $rem];
        }

        list($quot, $rem) = $this->doDiv($leftDig, $rightDig, $leftLen, $rightLen);

        if ($leftNeg !== $rightNeg) {
            $quot = $this->neg($quot);
        }

        if ($leftNeg) {
            $rem = $this->neg($rem);
        }

        return [$quot, $rem];
    }

    /**
     * {@inheritdoc}
     */
    public function pow($left, $exponent)
    {
        if ($exponent === 0) {
            return '1';
        }

        if ($exponent === 1) {
            return $left;
        }

        $odd = $exponent % 2;
        $exponent -= $odd;

        $lefta = $this->mul($left, $left);
        $result = $this->pow($lefta, $exponent / 2);

        if ($odd === 1) {
            $result = $this->mul($result, $left);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function cmp($left, $right)
    {
        $this->init($left, $right, $leftDig, $rightDig, $leftNeg, $rightNeg, $leftLen, $rightLen);

        if ($leftNeg && !$rightNeg) {
            return -1;
        }

        if ($rightNeg && !$leftNeg) {
            return 1;
        }

        if ($leftLen < $rightLen) {
            $result = -1;
        } elseif ($leftLen > $rightLen) {
            $result = 1;
        } else {
            $result = strcmp($leftDig, $rightDig);

            if ($result < 0) {
                $result = -1;
            } elseif ($result > 0) {
                $result = 1;
            }
        }

        return $leftNeg ? -$result : $result;
    }

    /**
     * Performs the addition of two non-signed large integers.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param int    $leftLen  The length of the first operand.
     * @param int    $rightLen The length of the second operand.
     *
     * @return string
     */
    private function doAdd($left, $right, $leftLen, $rightLen)
    {
        $length = $this->pad($left, $right, $leftLen, $rightLen);

        $carry = 0;
        $result = '';

        for ($i = $length - 1; $i >= 0; --$i) {
            $sum = (int) $left[$i] + (int) $right[$i] + $carry;

            if ($sum >= 10) {
                $carry = 1;
                $sum -= 10;
            } else {
                $carry = 0;
            }

            $result .= $sum;
        }

        if ($carry !== 0) {
            $result .= $carry;
        }

        return strrev($result);
    }

    /**
     * Performs the subtraction of two non-signed large integers.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param int    $leftLen  The length of the first operand.
     * @param int    $rightLen The length of the second operand.
     *
     * @return string
     */
    private function doSub($left, $right, $leftLen, $rightLen)
    {
        if ($left === $right) {
            return '0';
        }

        $cmp = $this->doCmp($left, $right, $leftLen, $rightLen);

        $invert = ($cmp === -1);

        if ($invert) {
            $c = $left;
            $left = $right;
            $right = $c;

            $z = $leftLen;
            $leftLen = $rightLen;
            $rightLen = $z;
        }

        $length = $this->pad($left, $right, $leftLen, $rightLen);

        $carry = 0;
        $result = '';

        for ($i = $length - 1; $i >= 0; --$i) {
            $sum = (int) $left[$i] - (int) $right[$i] - $carry;

            if ($sum < 0) {
                $carry = 1;
                $sum += 10;
            } else {
                $carry = 0;
            }

            $result .= $sum;
        }

        $result = strrev($result);
        $result = ltrim($result, '0');

        if ($invert) {
            $result = $this->neg($result);
        }

        return $result;
    }

    /**
     * Performs the multiplication of two non-signed large integers.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param int    $leftLen  The length of the first operand.
     * @param int    $rightLen The length of the second operand.
     *
     * @return string
     */
    private function doMul($left, $right, $leftLen, $rightLen)
    {
        $result = '0';

        for ($i = $leftLen - 1; $i >= 0; --$i) {
            $line = str_repeat('0', $leftLen - 1 - $i);
            $carry = 0;
            for ($j = $rightLen - 1; $j >= 0; --$j) {
                $mul = (int) $left[$i] * (int) $right[$j] + $carry;
                $digit = $mul % 10;
                $carry = ($mul - $digit) / 10;
                $line .= $digit;
            }

            if ($carry !== 0) {
                $line .= $carry;
            }

            $line = rtrim($line, '0');

            if ($line !== '') {
                $result = $this->add($result, strrev($line));
            }
        }

        return $result;
    }

    /**
     * Performs the division of two non-signed large integers.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param int    $leftLen  The length of the first operand.
     * @param int    $rightLen The length of the second operand.
     *
     * @return string[] The quotient and remainder.
     */
    private function doDiv($left, $right, $leftLen, $rightLen)
    {
        $cmp = $this->doCmp($left, $right, $leftLen, $rightLen);

        if ($cmp === -1) {
            return ['0', $left];
        }

        // we now know that a > b && x >= y

        $q = '0'; // quotient
        $r = $left; // remainder
        $z = $rightLen; // focus length, always $y or $y+1

        for (; ;) {
            $focus = substr($left, 0, $z);

            $cmp = $this->doCmp($focus, $right, $z, $rightLen);

            if ($cmp === -1) {
                if ($z === $leftLen) { // remainder < dividend
                    break;
                }

                ++$z;
            }

            $zeros = str_repeat('0', $leftLen - $z);

            $q = $this->add($q, '1'.$zeros);
            $left = $this->sub($left, $right.$zeros);

            $r = $left;

            if ($r === '0') { // remainder == 0
                break;
            }

            $leftLen = strlen($left);

            if ($leftLen < $rightLen) { // remainder < dividend
                break;
            }

            $z = $rightLen;
        }

        return [$q, $r];
    }

    /**
     * Compares two non-signed large numbers.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param int    $leftLen  The length of the first operand.
     * @param int    $rightLen The length of the second operand.
     *
     * @return int [-1, 0, 1]
     */
    private function doCmp($left, $right, $leftLen, $rightLen)
    {
        if ($leftLen > $rightLen) {
            return 1;
        }
        if ($leftLen < $rightLen) {
            return -1;
        }

        for ($i = 0; $i < $leftLen; ++$i) {
            $lefti = (int) $left[$i];
            $righti = (int) $right[$i];

            if ($lefti > $righti) {
                return 1;
            }
            if ($lefti < $righti) {
                return -1;
            }
        }

        return 0;
    }

    /**
     * Pads the left of one of the given numbers with zeros if necessary to make both numbers the same length.
     *
     * The numbers must only consist of digits, without leading minus sign.
     *
     * @param string $left     The first operand.
     * @param string $right    The second operand.
     * @param int    $leftLen  The length of the first operand.
     * @param int    $rightLen The length of the second operand.
     *
     * @return int The length of both strings.
     */
    private function pad(&$left, &$right, $leftLen, $rightLen)
    {
        $length = $leftLen > $rightLen ? $leftLen : $rightLen;

        if ($leftLen < $length) {
            $left = str_repeat('0', $length - $leftLen).$left;
        }
        if ($rightLen < $length) {
            $right = str_repeat('0', $length - $rightLen).$right;
        }

        return $length;
    }
}

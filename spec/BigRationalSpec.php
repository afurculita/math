<?php

namespace spec\Arki\Math;

use Arki\Math\BigDecimal;
use Arki\Math\BigInteger;
use Arki\Math\BigRational;
use Arki\Math\Exception\NumberFormatException;
use Arki\Math\Number;
use Arki\Math\RoundingMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method void shouldReturn($value)
 * @method void shouldBeAnInstanceOf($class)
 * @method void shouldBeEqualTo($value)
 * @method void shouldHaveTheNumeratorEqualTo($numerator)
 * @method void shouldHaveTheDenominatorEqualTo($denominator)
 * @method void shouldBeABigRationalEqualTo($value)
 * @method void shouldBeABigIntegerEqualTo($value)
 *
 * @mixin BigRational
 */
class BigRationalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Arki\Math\BigRational');
    }

    function it_is_a_number()
    {
        $this->shouldHaveType(Number::class);
    }

    function it_is_serializable()
    {
        $numerator = '-1234567890987654321012345678909876543210123456789';
        $denominator = '347827348278374374263874681238374983729873401984091287439827467286';
        // let
        $this->beConstructedThroughNd($numerator, $denominator);
        // and
        $rational = unserialize(serialize(BigRational::nd($numerator, $denominator)));

        // then
        $this->shouldHaveType('Serializable');
        // and
        $this->shouldBeABigRationalEqualTo((string)$rational);
    }

    function it_throws_exception_on_a_direct_call_to_unserialize()
    {
        // let
        $this->beConstructedThroughNd(1, 2);

        // then
        $this->shouldThrow('\LogicException')->duringUnserialize('123/456');
    }

    function it_can_not_be_instantiated()
    {
        $this->shouldThrow('\Exception')->duringInstantiation();
    }

    /**
     * @dataProvider providerNd
     */
    function it_creates_a_BigRational_out_of_a_numerator_and_a_denominator($numerator, $denominator, $n, $d)
    {
        // let
        $this->beConstructedThroughNd($n, $d);

        // then
        $this->shouldBeAnInstanceOf(BigRational::class);
        // and
        $this->shouldHaveTheNumeratorEqualTo($numerator);
        // and
        $this->shouldHaveTheDenominatorEqualTo($denominator);
    }

    public static function providerNd()
    {
        return [
            ['7', '1', '7', 1],
            ['7', '36', 7, 36],
            ['-7', '36', 7, -36],
            ['9', '15', '-9', -15],
            ['-98765432109876543210', '12345678901234567890', '-98765432109876543210', '12345678901234567890'],
        ];
    }

    function it_throws_exception_if_denominator_is_zero()
    {
        // let
        $this->beConstructedThroughNd(1, 0);

        // then
        $this->shouldThrow('\DivisionByZeroError')->duringInstantiation();
    }

    /**
     * @dataProvider providerOf
     */
    function it_creates_BigRationals_of_given_values($numerator, $denominator, $string)
    {
        // let
        $this->beConstructedThroughOf($string);

        // then
        $this->shouldBeAnInstanceOf(BigRational::class);
        // and
        $this->shouldHaveTheNumeratorEqualTo($numerator);
        // and
        $this->shouldHaveTheDenominatorEqualTo($denominator);
    }

    public static function providerOf()
    {
        return [
            ['123', '456', '123/456'],
            ['123', '456', '+123/456'],
            ['-2345', '6789', '-2345/6789'],
            ['123456', '1', '123456'],
            ['-1234567', '1', '-1234567'],
            ['-1234567890987654321012345678909876543210', '9999', '-1234567890987654321012345678909876543210/9999'],
            ['1230000', '1', '123e4'],
            ['1125', '1000', '1.125'],
        ];
    }

    function it_throws_an_exception_when_created_from_a_value_with_denominator_zero()
    {
        // let
        $this->beConstructedThroughOf('2/0');

        // then
        $this->shouldThrow('\DivisionByZeroError')->duringInstantiation();
    }

    /**
     * @dataProvider providerOfInvalidString
     */
    function it_throws_an_exception_when_created_from_an_invalid_string($string)
    {
        // let
        $this->beConstructedThroughOf($string);

        // then
        $this->shouldThrow(NumberFormatException::class)->duringInstantiation();
    }

    public static function providerOfInvalidString()
    {
        return [
            ['123/-456'],
            ['1e4/2'],
            [' 1/2'],
            ['1/2 '],
            ['+'],
            ['-'],
            ['/',],
        ];
    }

    function it_returns_a_BigRational_representing_zero()
    {
        // let
        $this->beConstructedThroughZero();

        // then
        $this->shouldBeAnInstanceOf(BigRational::class);
        // and
        $this->shouldHaveTheNumeratorEqualTo('0');
        // and
        $this->shouldHaveTheDenominatorEqualTo('1');
        // and
        $this->shouldBeEqualTo(BigRational::zero());
    }

    function it_returns_a_BigRational_representing_one()
    {
        // let
        $this->beConstructedThroughOne();

        // then
        $this->shouldBeAnInstanceOf(BigRational::class);
        // and
        $this->shouldHaveTheNumeratorEqualTo('1');
        // and
        $this->shouldHaveTheDenominatorEqualTo('1');
        // and
        $this->shouldBeEqualTo(BigRational::one());
    }

    function it_returns_a_BigRational_representing_ten()
    {
        // let
        $this->beConstructedThroughTen();

        // then
        $this->shouldBeAnInstanceOf(BigRational::class);
        // and
        $this->shouldHaveTheNumeratorEqualTo('10');
        // and
        $this->shouldHaveTheDenominatorEqualTo('1');
        // and
        $this->shouldBeEqualTo(BigRational::ten());
    }

    function it_has_accessors_for_numerator_and_denominator()
    {
        // let
        $this->beConstructedThroughNd(123456789, 987654321);

        // then
        $this->numerator()->shouldBeABigIntegerEqualTo('123456789');
        // and
        $this->denominator()->shouldBeABigIntegerEqualTo('987654321');
    }

    /**
     * @dataProvider providerMin
     */
    function it_returns_the_minimum_of_the_given_values($values, $min)
    {
        // let
        $this->beConstructedThroughMin(... $values);

        // then
        $this->shouldBeABigRationalEqualTo($min);
    }

    public static function providerMin()
    {
        return [
            [['1/2', '1/4', '1/3'], '1/4'],
            [['1/2', '0.1', '1/3'], '1/10'],
            [['-0.25', '-0.3', '-1/8', '123456789123456789123456789', 2e25], '-3/10'],
            [['1e30', '123456789123456789123456789/3', 2e26], '123456789123456789123456789/3'],
        ];
    }

    function it_throws_exception_when_calculating_the_minimum_of_zero_values()
    {
        // let
        $this->beConstructedThroughMin();

        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    /**
     * @dataProvider providerMax
     */
    function it_returns_the_maximum_of_the_given_values($values, $max)
    {
        // let
        $this->beConstructedThroughMax(... $values);

        // then
        $this->shouldBeABigRationalEqualTo($max);
    }

    public static function providerMax()
    {
        return [
            [['-5532146515641651651321321064580/32453', '-1/2', '-1/99'], '-1/99'],
            [['1e-30', '123456789123456789123456789/2', 2e25], '123456789123456789123456789/2'],
            [['999/1000', '1'], '1'],
            [[0, 0.9, -1.00], '9/10'],
            [[0, 0.01, -1, -1.2], '1/100'],
            [['1e-30', '15185185062185185062185185047/123', 2e25], '15185185062185185062185185047/123'],
            [['1e-30', '15185185062185185062185185047/123', 2e26], '200000000000000000000000000'],
        ];
    }

    function it_throws_exception_when_calculating_the_maximum_of_zero_values()
    {
        // let
        $this->beConstructedThroughMax();

        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    /**
     * @dataProvider providerQuotientAndRemainder
     */
    function it_returns_the_quotient_and_remainder_of_the_division_of_the_numerator_by_the_denominator(
        $rational,
        $quotient,
        $remainder
    ) {
        // let
        $this->beConstructedThroughOf($rational);
        // and
        $quotientAndRemainder = $this->quotientAndRemainder();

        // then
        $this->quotient()->shouldBeABigIntegerEqualTo($quotient);
        // and
        $this->remainder()->shouldBeABigIntegerEqualTo($remainder);
        // and
        $quotientAndRemainder[0]->shouldBeABigIntegerEqualTo($quotient);
        // and
        $quotientAndRemainder[1]->shouldBeABigIntegerEqualTo($remainder);
    }

    public static function providerQuotientAndRemainder()
    {
        return [
            ['1000/3', '333', '1'],
            ['895/400', '2', '95'],
            ['-2.5', '-2', '-5'],
            [-2, '-2', '0'],
        ];
    }

    /**
     * @dataProvider providerPlus
     */
    function it_returns_the_sum_of_this_number_and_the_given_one($rational, $plus, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->plus($plus)->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerPlus()
    {
        return [
            ['123/456', 1, '579/456'],
            ['123/456', BigInteger::of(2), '1035/456'],
            ['123/456', BigRational::nd(2, 3), '1281/1368'],
            ['234/567', '123/28', '76293/15876'],
            ['-1234567890123456789/497', '79394345/109859892', '-135629495075630790047217323/54600366324'],
            ['-1234567890123456789/999', '-98765/43210', '-53345678532234666518925/43166790'],
            [
                '123/456789123456789123456789',
                '-987/654321987654321',
                '-450850864771369260370369260/298887167199121283949604203169112635269',
            ],
        ];
    }

    /**
     * @dataProvider providerMinus
     */
    function it_returns_the_difference_of_this_number_and_the_given_one($rational, $minus, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->minus($minus)->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerMinus()
    {
        return [
            ['123/456', '1', '-333/456'],
            ['234/567', '123/28', '-63189/15876'],
            ['-1234567890123456789/497', '79394345/109859892', '-135629495075630868965196253/54600366324'],
            ['-1234567890123456789/999', '-98765/43210', '-53345678532234469186455/43166790'],
            [
                '123/456789123456789123456789',
                '-987/654321987654321',
                '450850864932332469333332226/298887167199121283949604203169112635269',
            ],
        ];
    }

    /**
     * @dataProvider providerMultipliedBy
     */
    function it_returns_the_product_of_this_number_and_the_given_one($rational, $number, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->multipliedBy($number)->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerMultipliedBy()
    {
        return [
            ['123/456', '1', '123/456'],
            ['123/456', '2', '246/456'],
            ['123/456', '1/2', '123/912'],
            ['123/456', '2/3', '246/1368'],
            ['-123/456', '2/3', '-246/1368'],
            ['123/456', '-2/3', '-246/1368'],
            [
                '489798742123504/387590928349859',
                '324893948394/23609901123',
                '159132647246919822550452576/9150983494511948540991657',
            ],
        ];
    }

    /**
     * @dataProvider providerDividedBy
     */
    function it_returns_the_division_of_this_number_and_the_given_one($rational, $number, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->dividedBy($number)->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerDividedBy()
    {
        return [
            ['123/456', '1', '123/456'],
            ['123/456', '2', '123/912'],
            ['123/456', '1/2', '246/456'],
            ['123/456', '2/3', '369/912'],
            ['-123/456', '2/3', '-369/912'],
            ['123/456', '-2/3', '-369/912'],
            [
                '489798742123504/387590928349859',
                '324893948394/23609901123',
                '11564099871705704494294992/125925947073281641523176446',
            ],
        ];
    }

    /**
     * @dataProvider providerPower
     */
    function it_returns_this_number_exponentiated_to_the_given_value($number, $exponent, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->power($exponent)->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerPower()
    {
        return [
            ['-3', 0, '1'],
            ['-2/3', 0, '1'],
            ['-1/2', 0, '1'],
            ['0', 0, '1'],
            ['1/3', 0, '1'],
            ['2/3', 0, '1'],
            ['3/2', 0, '1'],
            ['-3/2', 1, '-3/2'],
            ['-2/3', 1, '-2/3'],
            ['-1/3', 1, '-1/3'],
            ['0', 1, '0'],
            ['1/3', 1, '1/3'],
            ['2/3', 1, '2/3'],
            ['3/2', 1, '3/2'],
            ['-3/4', 2, '9/16'],
            ['-2/3', 2, '4/9'],
            ['-1/2', 2, '1/4'],
            ['0', 2, '0'],
            ['1/2', 2, '1/4'],
            ['2/3', 2, '4/9'],
            ['3/4', 2, '9/16'],
            ['-3/4', 3, '-27/64'],
            ['-2/3', 3, '-8/27'],
            ['-1/2', 3, '-1/8'],
            ['0', 3, '0'],
            ['1/2', 3, '1/8'],
            ['2/3', 3, '8/27'],
            ['3/4', 3, '27/64'],
            ['0', 1000000, '0'],
            ['1', 1000000, '1'],
            ['-2/3', 99, '-633825300114114700748351602688/171792506910670443678820376588540424234035840667'],
            ['-2/3', 100, '1267650600228229401496703205376/515377520732011331036461129765621272702107522001'],
            [
                '-123/33',
                25,
                '-17685925284953355608333258649989090388842388168292443/91801229324973413645775482048441660193',
            ],
            [
                '123/33',
                26,
                '2175368810049262739824990813948658117827613744699970489/3029440567724122650310590907598574786369',
            ],
            ['-123456789/2', 8, '53965948844821664748141453212125737955899777414752273389058576481/256'],
            ['9876543210/3', 7, '9167159269868350921847491739460569765344716959834325922131706410000000/2187'],
        ];
    }

    /**
     * @dataProvider providerReciprocal
     */
    function it_returns_the_reciprocal_of_this_BigRational($rational, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->reciprocal()->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerReciprocal()
    {
        return [
            ['1', '1'],
            ['2', '1/2'],
            ['1/2', '2'],
            ['123/456', '456/123'],
            ['-234/567', '-567/234'],
            ['489798742123504998877665/387590928349859112233445', '387590928349859112233445/489798742123504998877665'],
        ];
    }

    function it_throws_exception_when_calculates_reciprocal_for_rational_with_nominator_zero()
    {
        // let
        $this->beConstructedThroughNd(0, 2);

        // then
        $this->shouldThrow('\DivisionByZeroError')->duringReciprocal();
    }

    /**
     * @dataProvider providerAbs
     */
    function it_returns_the_absolute_value_of_this_BigRational($rational, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->abs()->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerAbs()
    {
        return [
            ['0', '0'],
            ['1', '1'],
            ['-1', '1'],
            ['123/456', '123/456'],
            ['-234/567', '234/567'],
            ['-489798742123504998877665/387590928349859112233445', '489798742123504998877665/387590928349859112233445'],
        ];
    }

    /**
     * @dataProvider providerNegated
     */
    function it_returns_the_negated_value_of_this_BigRational($rational, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->negate()->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerNegated()
    {
        return [
            ['0', '0'],
            ['1', '-1'],
            ['-1', '1'],
            ['123/456', '-123/456'],
            ['-234/567', '234/567'],
            ['-489798742123504998877665/387590928349859112233445', '489798742123504998877665/387590928349859112233445'],
            ['489798742123504998877665/387590928349859112233445', '-489798742123504998877665/387590928349859112233445'],
        ];
    }

    /**
     * @dataProvider providerSimplified
     */
    function it_returns_the_simplified_value_of_this_BigRational($rational, $expected)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->simplified()->shouldBeABigRationalEqualTo($expected);
    }

    public static function providerSimplified()
    {
        return [
            ['0', '0'],
            ['1', '1'],
            ['-1', '-1'],
            ['0/123456', '0'],
            ['-0/123456', '0'],
            ['-1/123456', '-1/123456'],
            ['4/6', '2/3'],
            ['-4/6', '-2/3'],
            ['123/456', '41/152'],
            ['-234/567', '-26/63'],
            ['489798742123504998877665/387590928349859112233445', '32653249474900333258511/25839395223323940815563'],
            ['-395651984391591565172038784/445108482440540510818543632', '-8/9'],
            ['1.125', '9/8'],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_compares_this_number_to_the_given_one($a, $b, $cmp)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->compareTo($b)->shouldBeEqualTo($cmp);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_number_is_equal_to_the_given_one($a, $b, $cmp)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isEqualTo($b)->shouldBeEqualTo($cmp === 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_number_is_strictly_lower_than_the_given_one($a, $b, $cmp)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isLessThan($b)->shouldBeEqualTo($cmp < 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_number_is_lower_than_or_equal_to_the_given_one($a, $b, $cmp)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isLessThanOrEqualTo($b)->shouldBeEqualTo($cmp <= 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_number_is_strictly_greater_than_the_given_one($a, $b, $cmp)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isGreaterThan($b)->shouldBeEqualTo($cmp > 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_number_is_greater_than_or_equal_to_the_given_one($a, $b, $cmp)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isGreaterThanOrEqualTo($b)->shouldBeEqualTo($cmp >= 0);
    }

    public static function providerCompareTo()
    {
        return [
            ['-1', '1/2', -1],
            ['1', '1/2', 1],
            ['1', '-1/2', 1],
            ['-1', '-1/2', -1],
            ['1/2', '1/2', 0],
            ['-1/2', '-1/2', 0],
            ['1/2', '2/4', 0],
            ['1/3', '122/369', 1],
            ['1/3', '123/369', 0],
            ['1/3', '124/369', -1],
            ['1/3', '123/368', -1],
            ['1/3', '123/370', 1],
            ['-1/3', '-122/369', -1],
            ['-1/3', '-123/369', 0],
            ['-1/3', '-124/369', 1],
            ['-1/3', '-123/368', 1],
            ['-1/3', '-123/370', -1],
            ['999999999999999999999999999999/1000000000000000000000000000000', '1', -1],
            ['1', '999999999999999999999999999999/1000000000000000000000000000000', 1],
            ['999999999999999999999999999999/1000000000000000000000000000000', '999/1000', 1],
            ['-999999999999999999999999999999/1000000000000000000000000000000', '-999/1000', -1],
            ['-999999999999999999999999999999/1000000000000000000000000000000', -1, 1],
            ['-999999999999999999999999999999/1000000000000000000000000000000', '-10e-1', 1],
            ['-999999999999999999999999999999/1000000000000000000000000000000', '-0.999999999999999999999999999999', 0],
            [
                '-999999999999999999999999999999/1000000000000000000000000000000',
                '-0.999999999999999999999999999998',
                -1,
            ],
        ];
    }

    /**
     * @dataProvider providerSign
     */
    function it_returns_the_signum_function_of_this_Number($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->signum()->shouldBeEqualTo($sign);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_number_equals_zero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isZero()->shouldBeEqualTo($sign === 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_number_is_negative($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isNegative()->shouldBeEqualTo($sign < 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_number_is_negative_or_equals_zero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isNegativeOrZero()->shouldBeEqualTo($sign <= 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_number_is_positive($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isPositive()->shouldBeEqualTo($sign > 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_number_is_positive_or_equals_zero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isPositiveOrZero()->shouldBeEqualTo($sign >= 0);
    }

    public static function providerSign()
    {
        return [
            ['0', 0],
            ['-0', 0],
            ['-2', -1],
            ['2', 1],
            ['0/123456', 0],
            ['-0/123456', 0],
            ['-1/23784738479837498273817307948739875387498374983749837984739874983749834384938493284934', -1],
            ['1/3478378924784729749873298479832792487498789012890843098490820480938092849032809480932840', 1],
        ];
    }

    /**
     * @dataProvider providerToBigDecimal
     */
    function it_converts_this_number_to_a_BigDecimal($number, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        if ($expected === null || is_object($expected)) {
            $this->shouldThrow('\ArithmeticError')->duringToBigDecimal();

            return;
        }

        $this->toBigDecimal()->shouldBeABigDecimalEqualTo($expected);
    }

    public static function providerToBigDecimal()
    {
        return iterator_to_array(self::providerToBigDecimalIterator());
    }

    private static function providerToBigDecimalIterator()
    {
        $tests = [
            ['1', '1'],
            ['1/2', '0.5'],
            ['2/2', '1'],
            ['3/2', '1.5'],
            ['1/3', null],
            ['2/3', null],
            ['3/3', '1'],
            ['4/3', null],
            ['1/4', '0.25'],
            ['2/4', '0.5'],
            ['3/4', '0.75'],
            ['4/4', '1'],
            ['5/4', '1.25'],
            ['1/5', '0.2'],
            ['2/5', '0.4'],
            ['1/6', null],
            ['2/6', null],
            ['3/6', '0.5'],
            ['4/6', null],
            ['5/6', null],
            ['6/6', '1'],
            ['7/6', null],
            ['1/7', null],
            ['2/7', null],
            ['6/7', null],
            ['7/7', '1'],
            ['14/7', '2'],
            ['15/7', null],
            ['1/8', '0.125'],
            ['2/8', '0.25'],
            ['3/8', '0.375'],
            ['4/8', '0.5'],
            ['5/8', '0.625'],
            ['6/8', '0.75'],
            ['7/8', '0.875'],
            ['8/8', '1'],
            ['17/8', '2.125'],
            ['1/9', null],
            ['2/9', null],
            ['9/9', '1'],
            ['10/9', null],
            ['17/9', null],
            ['18/9', '2'],
            ['19/9', null],
            ['1/10', '0.1'],
            ['10/2', '5'],
            ['10/20', '0.5'],
            ['100/20', '5'],
            ['100/2', '50'],
            ['8/360', null],
            ['9/360', '0.025'],
            ['10/360', null],
            ['17/360', null],
            ['18/360', '0.05'],
            ['19/360', null],
            ['1/500', '0.002'],
            ['1/600', null],
            ['1/400', '0.0025'],
            ['1/800', '0.00125'],
            ['1/1600', '0.000625'],
            ['2/1600', '0.00125'],
            ['3/1600', '0.001875'],
            ['4/1600', '0.0025'],
            ['5/1600', '0.003125'],
            [
                '669433117850846623944075755499/3723692145740642445161938667297363281250',
                '0.0000000001797767086134066979625344023536861184',
            ],
            ['669433117850846623944075755498/3723692145740642445161938667297363281250', null],
            ['669433117850846623944075755499/3723692145740642445161938667297363281251', null],
            [
                '438002367448868006942618029488152554057431119072727/9',
                '48666929716540889660290892165350283784159013230303',
            ],
            ['438002367448868006942618029488152554057431119072728/9', null],
            [
                '1278347892548908779/181664161764972047166111224214546382427215576171875',
                '0.0000000000000000000000000000000070368744177664',
            ],
            [
                '1278347892548908779/363328323529944094332222448429092764854431152343750',
                '0.0000000000000000000000000000000035184372088832',
            ],
            ['1278347892548908778/363328323529944094332222448429092764854431152343750', null],
            ['1278347892548908779/363328323529944094332222448429092764854431152343751', null],
            ['1274512848871262052662/181119169279677131024612890541902743279933929443359375', null],
            [
                '1274512848871262052663/181119169279677131024612890541902743279933929443359375',
                '0.0000000000000000000000000000000070368744177664',
            ],
            ['1274512848871262052664/181119169279677131024612890541902743279933929443359375', null],
        ];

        foreach ($tests as list ($number, $expected)) {
            yield [$number, $expected];
            yield ['-'.$number, $expected === null ? null : '-'.$expected];
        }
    }

    /**
     * @dataProvider providerToScale
     */
    function it_converts_this_number_to_a_BigDecimal_with_the_given_scale($number, $scale, $roundingMode, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        if (self::isException($expected)) {
            $this->shouldThrow($expected)->duringToScale($scale, $roundingMode);

            return;
        }

        $this->toScale($scale, $roundingMode)->shouldBeABigDecimalEqualTo($expected);
    }

    public static function providerToScale()
    {
        return [
            ['1/8', 3, RoundingMode::UNNECESSARY, '0.125'],
            ['1/16', 3, RoundingMode::UNNECESSARY, '\ArithmeticError'],
            ['1/16', 3, RoundingMode::HALF_DOWN, '0.062'],
            ['1/16', 3, RoundingMode::HALF_UP, '0.063'],
            ['1/9', 30, RoundingMode::DOWN, '0.111111111111111111111111111111'],
            ['1/9', 30, RoundingMode::UP, '0.111111111111111111111111111112'],
            ['1/9', 100, RoundingMode::UNNECESSARY, '\ArithmeticError'],
        ];
    }

    /**
     * @dataProvider providerToInteger
     */
    function it_returns_the_exact_value_of_this_number_as_a_native_integer($rational, $integer)
    {
        // let
        $this->beConstructedThroughOf($rational);

        // then
        $this->toInteger()->shouldBeEqualTo($integer);
    }

    public static function providerToInteger()
    {
        return [
            [PHP_INT_MAX, PHP_INT_MAX],
            [~PHP_INT_MAX, ~PHP_INT_MAX],
            [PHP_INT_MAX.'0/10', PHP_INT_MAX],
            [~PHP_INT_MAX.'0/10', ~PHP_INT_MAX],
            ['246913578/2', 123456789],
            ['-246913578/2', -123456789],
            ['625/25', 25],
            ['-625/25', -25],
            ['0/3', 0],
            ['-0/3', 0],
        ];
    }

    /**
     * @dataProvider providerToIntegerThrowsException
     */
    function it_throws_exception_if_this_number_cannot_be_converted_to_a_native_integer_without_losing_precision($number
    ) {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->shouldThrow('\ArithmeticError')->duringToInteger();
    }

    public static function providerToIntegerThrowsException()
    {
        return [
            ['-999999999999999999999999999999'],
            ['9999999999999999999999999999999/2'],
            ['1/2'],
            ['2/3'],
        ];
    }

    /**
     * @dataProvider providerToFloat
     */
    function it_returns_the_exact_value_of_this_number_as_a_native_float($value, $float)
    {
        // let
        $this->beConstructedThroughOf($value);

        // then
        $this->toFloat()->shouldBeEqualTo($float);
    }

    public static function providerToFloat()
    {
        return [
            ['0', 0.0],
            ['1.6', 1.6],
            ['-1.6', -1.6],
            ['1000000000000000000000000000000000000000/3', 3.3333333333333333333333333333333333e+38],
            ['-2/300000000000000000000000000000000000000', -6.666666666666666666666666666666666e-39],
            ['9.9e3000', INF],
            ['-9.9e3000', -INF],
        ];
    }

    /**
     * @dataProvider providerToString
     */
    function it_returns_a_string_representation_of_this_number($numerator, $denominator, $expected)
    {
        // let
        $this->beConstructedThroughNd($numerator, $denominator);

        // then
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerToString()
    {
        return [
            ['-1', '1', '-1'],
            ['2', '1', '2'],
            ['1', '2', '1/2'],
            ['-1', '-2', '1/2'],
            ['1', '-2', '-1/2'],
            ['34327948737247817984738927598572389', '32565046546', '34327948737247817984738927598572389/32565046546'],
            ['34327948737247817984738927598572389', '-32565046546', '-34327948737247817984738927598572389/32565046546'],
            ['34327948737247817984738927598572389', '1', '34327948737247817984738927598572389'],
            ['34327948737247817984738927598572389', '-1', '-34327948737247817984738927598572389'],
        ];
    }

    public function getMatchers()
    {
        return [
            'haveTheNumeratorEqualTo'   => function (BigRational $subject, $key) {
                return $key === (string)$subject->numerator();
            },
            'haveTheDenominatorEqualTo' => function (BigRational $subject, $key) {
                return $key === (string)$subject->denominator();
            },
            'beABigIntegerEqualTo'      => function (BigInteger $subject, $key) {
                return $subject instanceof BigInteger && $key === (string)$subject;
            },
            'beABigRationalEqualTo'     => function (BigRational $subject, $key) {
                return $subject instanceof BigRational && $key === (string)$subject;
            },
            'beABigDecimalEqualTo'      => function (BigDecimal $subject, $key) {
                return $subject instanceof BigDecimal && $key === (string)$subject;
            },
        ];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private static function isException($name)
    {
        return is_subclass_of($name, '\Exception') || is_subclass_of($name, '\Error');
    }
}

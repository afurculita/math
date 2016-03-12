<?php

namespace spec\Arki\Math;

use Arki\Math\BigDecimal;
use Arki\Math\BigInteger;
use Arki\Math\Number;
use Arki\Math\BigRational;
use Arki\Math\Exception\NumberFormatException;
use Arki\Math\RoundingMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @method $this unscaledValue()
 * @method $this scale()
 * @method void shouldReturn($value)
 * @method void shouldBeAnInstanceOf($class)
 * @method void shouldBeEqualTo($value)
 * @method void beConstructedThroughMin(... $arguments)
 * @method void beConstructedThroughMax(... $arguments)
 * @method $this beConstructedThroughOfUnscaledValue($value, $scale)
 */
class BigDecimalSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Arki\Math\BigDecimal');
    }

    function it_is_a_number()
    {
        $this->shouldBeAnInstanceOf(Number::class);
    }

    function it_is_serializable()
    {
        $value = '-1234567890987654321012345678909876543210123456789';
        $scale = 37;
        // let
        $decimal = BigDecimal::ofUnscaledValue($value, $scale);
        // and
        $decimal = unserialize(serialize($decimal));
        // and
        $this->beConstructedThroughOf($decimal);

        // then
        $this->shouldHaveType('Serializable');
        // and
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($value);
        // and
        $this->scale()->shouldReturn($scale);
    }

    function it_can_not_be_instantiated()
    {
        $this->shouldThrow('\Exception')->duringInstantiation();
    }

    /**
     * @dataProvider factoryMethodValidValuesProvider
     */
    function it_creates_a_big_decimal_of_a_given_value_at_a_given_scale($value, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThrough('of', [$value]);

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $this->scale()->shouldReturn($scale);
    }

    /**
     * [$value, $unscaledValue, $scale]
     *
     * $value         The value to convert to a BigDecimal.
     * $unscaledValue The expected unscaled value.
     * $scale         The expected scale.
     *
     * @return array
     */
    public static function factoryMethodValidValuesProvider()
    {
        return [
            [0, '0', 0],
            [1, '1', 0],
            [-1, '-1', 0],
            [123456789, '123456789', 0],
            [-123456789, '-123456789', 0],
            [PHP_INT_MAX, (string)PHP_INT_MAX, 0],
            [~PHP_INT_MAX, (string)~PHP_INT_MAX, 0],
            [0.0, '0', 0],
            [0.1, '1', 1],
            [1.0, '1', 0],
            [1.1, '11', 1],
            ['0', '0', 0],
            ['+0', '0', 0],
            ['-0', '0', 0],
            ['00', '0', 0],
            ['+00', '0', 0],
            ['-00', '0', 0],
            ['1', '1', 0],
            ['+1', '1', 0],
            ['-1', '-1', 0],
            ['01', '1', 0],
            ['+01', '1', 0],
            ['-01', '-1', 0],
            ['0.0', '0', 1],
            ['+0.0', '0', 1],
            ['-0.0', '0', 1],
            ['00.0', '0', 1],
            ['+00.0', '0', 1],
            ['-00.0', '0', 1],
            ['1.0', '10', 1],
            ['+1.0', '10', 1],
            ['-1.0', '-10', 1],
            ['01.0', '10', 1],
            ['+01.0', '10', 1],
            ['-01.0', '-10', 1],
            ['0.1', '1', 1],
            ['+0.1', '1', 1],
            ['-0.1', '-1', 1],
            ['0.10', '10', 2],
            ['+0.10', '10', 2],
            ['-0.10', '-10', 2],
            ['0.010', '10', 3],
            ['+0.010', '10', 3],
            ['-0.010', '-10', 3],
            ['00.1', '1', 1],
            ['+00.1', '1', 1],
            ['-00.1', '-1', 1],
            ['00.10', '10', 2],
            ['+00.10', '10', 2],
            ['-00.10', '-10', 2],
            ['00.010', '10', 3],
            ['+00.010', '10', 3],
            ['-00.010', '-10', 3],
            ['01.1', '11', 1],
            ['+01.1', '11', 1],
            ['-01.1', '-11', 1],
            ['01.010', '1010', 3],
            ['+01.010', '1010', 3],
            ['-01.010', '-1010', 3],
            ['0e-2', '0', 2],
            ['0e-1', '0', 1],
            ['0e-0', '0', 0],
            ['0e0', '0', 0],
            ['0e1', '0', 0],
            ['0e2', '0', 0],
            ['0e+0', '0', 0],
            ['0e+1', '0', 0],
            ['0e+2', '0', 0],
            ['0.0e-2', '0', 3],
            ['0.0e-1', '0', 2],
            ['0.0e-0', '0', 1],
            ['0.0e0', '0', 1],
            ['0.0e1', '0', 0],
            ['0.0e2', '0', 0],
            ['0.0e+0', '0', 1],
            ['0.0e+1', '0', 0],
            ['0.0e+2', '0', 0],
            ['0.1e-2', '1', 3],
            ['0.1e-1', '1', 2],
            ['0.1e-0', '1', 1],
            ['0.1e0', '1', 1],
            ['0.1e1', '1', 0],
            ['0.1e2', '10', 0],
            ['0.1e+0', '1', 1],
            ['0.1e+1', '1', 0],
            ['0.1e+2', '10', 0],
            ['1.23e+011', '123000000000', 0],
            ['1.23e-011', '123', 13],
            ['0.01e-2', '1', 4],
            ['0.01e-1', '1', 3],
            ['0.01e-0', '1', 2],
            ['0.01e0', '1', 2],
            ['0.01e1', '1', 1],
            ['0.01e2', '1', 0],
            ['0.01e+0', '1', 2],
            ['0.01e+1', '1', 1],
            ['0.01e+2', '1', 0],
            ['0.10e-2', '10', 4],
            ['0.10e-1', '10', 3],
            ['0.10e-0', '10', 2],
            ['0.10e0', '10', 2],
            ['0.10e1', '10', 1],
            ['0.10e2', '10', 0],
            ['0.10e+0', '10', 2],
            ['0.10e+1', '10', 1],
            ['0.10e+2', '10', 0],
            ['00.10e-2', '10', 4],
            ['+00.10e-1', '10', 3],
            ['-00.10e-0', '-10', 2],
            ['00.10e0', '10', 2],
            ['+00.10e1', '10', 1],
            ['-00.10e2', '-10', 0],
            ['00.10e+0', '10', 2],
            ['+00.10e+1', '10', 1],
            ['-00.10e+2', '-10', 0],
        ];
    }

    /**
     * @dataProvider factoryMethodInvalidValuesProvider
     */
    function it_throws_exception_when_creating_decimals_of_value_with_invalid_format($value)
    {
        // let
        $this->beConstructedThrough('of', [$value]);
        // then
        $this->shouldThrow(NumberFormatException::class)->duringInstantiation();
    }

    public static function factoryMethodInvalidValuesProvider()
    {
        return [
            [''],
            ['a'],
            [' 1'],
            ['1 '],
            ['1.'],
            ['.1'],
            ['+'],
            ['-'],
            ['+a'],
            ['-a'],
            ['a1'],
            [INF],
            [-INF],
            [NAN],
        ];
    }

    function it_returns_the_current_instance_if_big_decimal_is_used_as_argument()
    {
        $decimal = BigDecimal::of(123);
        $this->beConstructedThroughOf($decimal);

        $this->shouldBeEqualTo($decimal);
    }

    /**
     * @dataProvider providerOfUnscaledValue
     */
    function it_creates_decimals_from_unscaled_values($unscaledValue, $scale, $expectedUnscaledValue)
    {
        // let
        $this->beConstructedThrough('ofUnscaledValue', [$unscaledValue, $scale]);

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($expectedUnscaledValue);
        // and
        $this->scale()->shouldReturn($scale);
    }

    public static function providerOfUnscaledValue()
    {
        return [
            [123456789, 0, '123456789'],
            [123456789, 1, '123456789'],
            [-123456789, 0, '-123456789'],
            [-123456789, 1, '-123456789'],
            ['123456789012345678901234567890', 0, '123456789012345678901234567890'],
            ['123456789012345678901234567890', 1, '123456789012345678901234567890'],
            ['+123456789012345678901234567890', 0, '123456789012345678901234567890'],
            ['+123456789012345678901234567890', 1, '123456789012345678901234567890'],
            ['-123456789012345678901234567890', 0, '-123456789012345678901234567890'],
            ['-123456789012345678901234567890', 1, '-123456789012345678901234567890'],
            ['0123456789012345678901234567890', 0, '123456789012345678901234567890'],
            ['0123456789012345678901234567890', 1, '123456789012345678901234567890'],
            ['+0123456789012345678901234567890', 0, '123456789012345678901234567890'],
            ['+0123456789012345678901234567890', 1, '123456789012345678901234567890'],
            ['-0123456789012345678901234567890', 0, '-123456789012345678901234567890'],
            ['-0123456789012345678901234567890', 1, '-123456789012345678901234567890'],
        ];
    }

    function it_throws_exception_when_creating_from_unscaled_value_with_negative_scale()
    {
        // let
        $this->beConstructedThrough('ofUnscaledValue', ['0', -1]);
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_creates_the_decimal_zero()
    {
        // let
        $this->beConstructedThrough('zero');

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn('0');
        // and
        $this->scale()->shouldReturn(0);
        // and
        $this->shouldBeEqualTo(BigDecimal::zero());
    }

    function it_creates_the_decimal_one()
    {
        // let
        $this->beConstructedThrough('one');

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn('1');
        // and
        $this->scale()->shouldReturn(0);
        // and
        $this->shouldBeEqualTo(BigDecimal::one());
    }

    function it_creates_the_decimal_ten()
    {
        // let
        $this->beConstructedThrough('ten');

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn('10');
        // and
        $this->scale()->shouldReturn(0);
        // and
        $this->shouldBeEqualTo(BigDecimal::ten());
    }

    /**
     * @dataProvider minimumProvider
     */
    function it_returns_the_minimum_of_the_given_values($values, $min)
    {
        // let
        $this->beConstructedThroughMin(...$values);

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->__toString()->shouldReturn($min);
    }

    public static function minimumProvider()
    {
        return [
            [[0, 1, -1], '-1'],
            [[0, 1, -1, -1.2], '-1.2'],
            [['1e30', '123456789123456789123456789', 2e25], '20000000000000000000000000'],
            [['1e30', '123456789123456789123456789', 2e26], '123456789123456789123456789'],
            [[0, '10', '5989', '-3/3'], '-1'],
            [['-0.0000000000000000000000000000001', '0'], '-0.0000000000000000000000000000001'],
            [['0.00000000000000000000000000000001', '0'], '0'],
            [['-1', '1', '2', '3', '-2973/30'], '-99.1'],
            [
                ['999999999999999999999999999.99999999999', '1000000000000000000000000000'],
                '999999999999999999999999999.99999999999',
            ],
            [
                ['-999999999999999999999999999.99999999999', '-1000000000000000000000000000'],
                '-1000000000000000000000000000',
            ],
            [['9.9e50', '1e50'], '100000000000000000000000000000000000000000000000000'],
            [['9.9e50', '1e51'], '990000000000000000000000000000000000000000000000000'],
        ];
    }

    function it_throws_exception_when_calculating_min_for_empty_value_collection()
    {
        // let
        $this->beConstructedThroughMin();
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_exception_when_calculating_min_for_non_decimal_values()
    {
        // let
        $this->beConstructedThroughMin(1, '1/3');
        // then
        $this->shouldThrow('\ArithmeticError')->duringInstantiation();
    }

    /**
     * @dataProvider providerMax
     */
    function it_finds_the_max_from_a_collection_of_decimals($values, $max)
    {
        // let
        $this->beConstructedThroughMax(...$values);

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->__toString()->shouldReturn($max);
    }

    public static function providerMax()
    {
        return [
            [[0, 0.9, -1.00], '0.9'],
            [[0, 0.01, -1, -1.2], '0.01'],
            [[0, 0.01, -1, -1.2, '2e-1'], '0.2'],
            [['1e-30', '123456789123456789123456789', 2e25], '123456789123456789123456789'],
            [['1e-30', '123456789123456789123456789', 2e26], '200000000000000000000000000'],
            [[0, '10', '5989', '-1'], '5989'],
            [
                [0, '10', '5989', '5989.000000000000000000000000000000001', '-1'],
                '5989.000000000000000000000000000000001',
            ],
            [[0, '10', '5989', '5989.000000000000000000000000000000001', '-1', '5990'], '5990'],
            [['-0.0000000000000000000000000000001', 0], '0'],
            [['0.00000000000000000000000000000001', '0'], '0.00000000000000000000000000000001'],
            [['-1', '1', '2', '3', '-99.1'], '3'],
            [['-1', '1', '2', '3', '-99.1', '31/10'], '3.1'],
            [
                ['999999999999999999999999999.99999999999', '1000000000000000000000000000'],
                '1000000000000000000000000000',
            ],
            [
                ['-999999999999999999999999999.99999999999', '-1000000000000000000000000000'],
                '-999999999999999999999999999.99999999999',
            ],
            [['9.9e50', '1e50'], '990000000000000000000000000000000000000000000000000'],
            [['9.9e50', '1e51'], '1000000000000000000000000000000000000000000000000000'],
        ];
    }

    function it_throws_exception_when_calculating_max_for_empty_value_collection()
    {
        // let
        $this->beConstructedThroughMax();
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_exception_when_calculating_max_for_non_decimal_values()
    {
        // let
        $this->beConstructedThroughMax(1, '4/9');
        // then
        $this->shouldThrow('\ArithmeticError')->duringInstantiation();
    }

    /**
     * @dataProvider providerPlus
     */
    function it_adds_two_decimals($a, $b, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b) {
                return BigDecimal::of($a)->plus($b);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $this->scale()->shouldReturn($scale);
    }

    public static function providerPlus()
    {
        return [
            ['123', '999', '1122', 0],
            ['123', '999.0', '11220', 1],
            ['123', '999.00', '112200', 2],
            ['123.0', '999', '11220', 1],
            ['123.0', '999.0', '11220', 1],
            ['123.0', '999.00', '112200', 2],
            ['123.00', '999', '112200', 2],
            ['123.00', '999.0', '112200', 2],
            ['123.00', '999.00', '112200', 2],
            ['123', '-999', '-876', 0],
            ['123', '-999.0', '-8760', 1],
            ['123', '-999.00', '-87600', 2],
            ['123.0', '-999', '-8760', 1],
            ['123.0', '-999.0', '-8760', 1],
            ['123.0', '-999.00', '-87600', 2],
            ['123.00', '-999', '-87600', 2],
            ['123.00', '-999.0', '-87600', 2],
            ['123.00', '-999.00', '-87600', 2],
            ['-123', '999', '876', 0],
            ['-123', '999.0', '8760', 1],
            ['-123', '999.00', '87600', 2],
            ['-123.0', '999', '8760', 1],
            ['-123.0', '999.0', '8760', 1],
            ['-123.0', '999.00', '87600', 2],
            ['-123.00', '999', '87600', 2],
            ['-123.00', '999.0', '87600', 2],
            ['-123.00', '999.00', '87600', 2],
            ['-123', '-999', '-1122', 0],
            ['-123', '-999.0', '-11220', 1],
            ['-123', '-999.00', '-112200', 2],
            ['-123.0', '-999', '-11220', 1],
            ['-123.0', '-999.0', '-11220', 1],
            ['-123.0', '-999.00', '-112200', 2],
            ['-123.00', '-999', '-112200', 2],
            ['-123.00', '-999.0', '-112200', 2],
            ['-123.00', '-999.00', '-112200', 2],
            ['23487837847837428335.322387091', '309049304233535454687656.2392', '309072792071383292115991561587091', 9],
            ['-234878378478328335.322387091', '309049304233535154687656.232', '309049069355156676359320909612909', 9],
            ['234878378478328335.3227091', '-3090495154687656.231343344452', '231787883323640679091365755548', 12],
            ['-23487837847833435.3231', '-3090495154687656.231343344452', '-26578333002521091554443344452', 12],
            ['1234568798347983.2334899238921', '0', '12345687983479832334899238921', 13],
            ['-0.00223287647368738736428467863784', '0.000', '-223287647368738736428467863784', 32],
        ];
    }

    /**
     * @dataProvider providerMinus
     */
    function it_subtracts_two_decimals($a, $b, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b) {
                return BigDecimal::of($a)->minus($b);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $this->scale()->shouldReturn($scale);
    }

    public static function providerMinus()
    {
        return [
            ['123', '999', '-876', 0],
            ['123', '999.0', '-8760', 1],
            ['123', '999.00', '-87600', 2],
            ['123.0', '999', '-8760', 1],
            ['123.0', '999.0', '-8760', 1],
            ['123.0', '999.00', '-87600', 2],
            ['123.00', '999', '-87600', 2],
            ['123.00', '999.0', '-87600', 2],
            ['123.00', '999.00', '-87600', 2],
            ['123', '-999', '1122', 0],
            ['123', '-999.0', '11220', 1],
            ['123', '-999.00', '112200', 2],
            ['123.0', '-999', '11220', 1],
            ['123.0', '-999.0', '11220', 1],
            ['123.0', '-999.00', '112200', 2],
            ['123.00', '-999', '112200', 2],
            ['123.00', '-999.0', '112200', 2],
            ['123.00', '-999.00', '112200', 2],
            ['-123', '999', '-1122', 0],
            ['-123', '999.0', '-11220', 1],
            ['-123', '999.00', '-112200', 2],
            ['-123.0', '999', '-11220', 1],
            ['-123.0', '999.0', '-11220', 1],
            ['-123.0', '999.00', '-112200', 2],
            ['-123.00', '999', '-112200', 2],
            ['-123.00', '999.0', '-112200', 2],
            ['-123.00', '999.00', '-112200', 2],
            ['-123', '-999', '876', 0],
            ['-123', '-999.0', '8760', 1],
            ['-123', '-999.00', '87600', 2],
            ['-123.0', '-999', '8760', 1],
            ['-123.0', '-999.0', '8760', 1],
            ['-123.0', '-999.00', '87600', 2],
            ['-123.00', '-999', '87600', 2],
            ['-123.00', '-999.0', '87600', 2],
            ['-123.00', '-999.00', '87600', 2],
            ['234878378477428335.3223334343487091', '309049304233536.2392', '2345693291731947990831334343487091', 16],
            ['-2348783784774335.32233343434891', '309049304233536.233392', '-265783308900787155572543434891', 14],
            ['2348783784774335.323232342791', '-309049304233536.556172', '2657833089007871879404342791', 12],
            ['-2348783784774335.3232342791', '-309049304233536.556172', '-20397344805407987670622791', 10],
            ['1234568798347983.2334899238921', '0', '12345687983479832334899238921', 13],
            ['-0.00223287647368738736428467863784', '0.000', '-223287647368738736428467863784', 32],
        ];
    }

    /**
     * @dataProvider providerMultipliedBy
     */
    function it_multiplies_two_decimals($a, $b, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b) {
                return BigDecimal::of($a)->multipliedBy($b);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $this->scale()->shouldReturn($scale);
    }

    public static function providerMultipliedBy()
    {
        return [
            ['123', '999', '122877', 0],
            ['123', '999.0', '1228770', 1],
            ['123', '999.00', '12287700', 2],
            ['123.0', '999', '1228770', 1],
            ['123.0', '999.0', '12287700', 2],
            ['123.0', '999.00', '122877000', 3],
            ['123.00', '999', '12287700', 2],
            ['123.00', '999.0', '122877000', 3],
            ['123.00', '999.00', '1228770000', 4],
            ['123', '-999', '-122877', 0],
            ['123', '-999.0', '-1228770', 1],
            ['123', '-999.00', '-12287700', 2],
            ['123.0', '-999', '-1228770', 1],
            ['123.0', '-999.0', '-12287700', 2],
            ['123.0', '-999.00', '-122877000', 3],
            ['123.00', '-999', '-12287700', 2],
            ['123.00', '-999.0', '-122877000', 3],
            ['123.00', '-999.00', '-1228770000', 4],
            ['-123', '999', '-122877', 0],
            ['-123', '999.0', '-1228770', 1],
            ['-123', '999.00', '-12287700', 2],
            ['-123.0', '999', '-1228770', 1],
            ['-123.0', '999.0', '-12287700', 2],
            ['-123.0', '999.00', '-122877000', 3],
            ['-123.00', '999', '-12287700', 2],
            ['-123.00', '999.0', '-122877000', 3],
            ['-123.00', '999.00', '-1228770000', 4],
            ['-123', '-999', '122877', 0],
            ['-123', '-999.0', '1228770', 1],
            ['-123', '-999.00', '12287700', 2],
            ['-123.0', '-999', '1228770', 1],
            ['-123.0', '-999.0', '12287700', 2],
            ['-123.0', '-999.00', '122877000', 3],
            ['-123.00', '-999', '12287700', 2],
            ['-123.00', '-999.0', '122877000', 3],
            ['-123.00', '-999.00', '1228770000', 4],
            ['589252.156111130', '999.2563989942545241223454', '5888139876152080735720775399923986443020', 31],
            ['-589252.15611130', '999.256398994254524122354', '-58881398761537794715991163083004200020', 29],
            ['589252.1561113', '-99.256398994254524122354', '-584870471152079471599116308300420002', 28],
            ['-58952.156111', '-9.256398994254524122357', '545684678534996098129205129273627', 27],
            ['0.1235437849158495728979344999999999999', '1', '1235437849158495728979344999999999999', 37],
            ['-1.324985980890283098409328999999999999', '1', '-1324985980890283098409328999999999999', 36],
        ];
    }

    /**
     * @dataProvider providerDividedBy
     */
    function it_divides_two_decimals($a, $b, $scale, $roundingMode, $unscaledValue, $expectedScale)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b, $scale, $roundingMode) {
                return BigDecimal::of($a)->dividedBy($b, $scale, $roundingMode);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $this->scale()->shouldReturn($expectedScale);
    }

    public static function providerDividedBy()
    {
        return [
            ['7', '0.2', 0, RoundingMode::UNNECESSARY, '35', 0],
            ['7', '-0.2', 0, RoundingMode::UNNECESSARY, '-35', 0],
            ['-7', '0.2', 0, RoundingMode::UNNECESSARY, '-35', 0],
            ['-7', '-0.2', 0, RoundingMode::UNNECESSARY, '35', 0],
            ['1324794783847839472983.343898', '1', 6, RoundingMode::UNNECESSARY, '1324794783847839472983343898', 6],
            ['-32479478384783947298.3343898', '1', 7, RoundingMode::UNNECESSARY, '-324794783847839472983343898', 7],
            ['1.5', '2', 2, RoundingMode::UNNECESSARY, '75', 2],
            ['1.5', '3', 1, RoundingMode::UNNECESSARY, '5', 1],
            ['0.123456789', '0.00244140625', 10, RoundingMode::UNNECESSARY, '505679007744', 10],
            ['1.234', '123.456', 50, RoundingMode::DOWN, '999546397096941420425090720580611715914981855883', 50],
            ['1', '3', 10, RoundingMode::UP, '3333333334', 10],
            ['0.124', '0.2', 3, RoundingMode::UNNECESSARY, '620', 3],
            ['0.124', '2', 3, RoundingMode::UNNECESSARY, '62', 3],
        ];
    }

    /**
     * @dataProvider zeros_provider
     */
    function it_throws_exception_when_it_divides_by_zero($zero)
    {
        // let
        $this->beConstructedThroughOf(1);
        // then
        $this->shouldThrow('\DivisionByZeroError')->during('dividedBy', [$zero, 0]);
    }

    public static function zeros_provider()
    {
        return [
            [0],
            [0.0],
            ['0'],
            ['0.0'],
            ['0.00'],
        ];
    }

    /**
     * @dataProvider providerExactlyDividedBy
     */
    function it_exactly_divides_decimals($number, $divisor, $expected)
    {
        // let
        $this->beConstructedThrough('of', [$number]);

        // then
        if (self::isException($expected)) {
            $this->shouldThrow($expected)->during('exactlyDividedBy', [$divisor]);

            return;
        }

        $d = $this->exactlyDividedBy($divisor);

        // or
        $d->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $d->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerExactlyDividedBy()
    {
        return [
            [1, 1, '1'],
            ['1.0', '1.00', '1'],
            [1, 2, '0.5'],
            [1, 3, '\ArithmeticError'],
            [1, 4, '0.25'],
            [1, 5, '0.2'],
            [1, 6, '\ArithmeticError'],
            [1, 7, '\ArithmeticError'],
            [1, 8, '0.125'],
            [1, 9, '\ArithmeticError'],
            [1, 10, '0.1'],
            ['1.0', 2, '0.5'],
            ['1.00', 2, '0.5'],
            ['1.0000', 8, '0.125'],
            [1, '4.000', '0.25'],
            ['1', '0.125', '8'],
            ['1.0', '0.125', '8'],
            ['1234.5678', '2', '617.2839'],
            ['1234.5678', '4', '308.64195'],
            ['1234.5678', '8', '154.320975'],
            ['1234.5678', '6.4', '192.90121875'],
            ['7', '3125', '0.00224'],
            [
                '4849709849456546549849846510128399',
                '18014398509481984',
                '269212976880902984.935786476657271160117801400701864622533321380615234375',
            ],
            [
                '4849709849456546549849846510128399',
                '-18014398509481984',
                '-269212976880902984.935786476657271160117801400701864622533321380615234375',
            ],
            [
                '-4849709849456546549849846510128399',
                '18014398509481984',
                '-269212976880902984.935786476657271160117801400701864622533321380615234375',
            ],
            [
                '-4849709849456546549849846510128399',
                '-18014398509481984',
                '269212976880902984.935786476657271160117801400701864622533321380615234375',
            ],
            ['123', '0', '\DivisionByZeroError'],
            [-789, '0.0', '\DivisionByZeroError'],
        ];
    }

    function it_throws_exception_when_it_exactly_divides_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1);
        // then
        $this->shouldThrow('\DivisionByZeroError')->during('exactlyDividedBy', [0]);
    }

    /**
     * @dataProvider providerDividedByWithRoundingNecessaryThrowsException
     */
    function it_throws_exception_when_dividing_with_rounding_necessary($a, $b, $scale)
    {
        // let
        $this->beConstructedThroughOf($a);
        // then
        $this->shouldThrow('\ArithmeticError')->during('dividedBy', [$b, $scale]);
    }

    public static function providerDividedByWithRoundingNecessaryThrowsException()
    {
        return [
            ['1.234', '123.456', 3],
            ['7', '2', 0],
            ['7', '3', 100],
        ];
    }

    function it_throws_exception_when_dividing_with_negative_scale()
    {
        // let
        $this->beConstructedThroughOf(1);
        // then
        $this->shouldThrow('\InvalidArgumentException')->during('dividedBy', [2, -1]);
    }

    function it_throws_exception_when_dividing_with_invalid_rounding_mode()
    {
        // let
        $this->beConstructedThroughOf(1);
        // then
        $this->shouldThrow('\InvalidArgumentException')->during('dividedBy', [2, 0, -1]);
    }

    /**
     * @dataProvider providerRoundingMode
     */
    function it_rounds_the_division_result_based_on_a_rounding_mode($roundingMode, $number, $two, $one, $zero)
    {
        // let
        $this->beConstructedThroughOf($number);

        foreach ([$zero, $one, $two] as $scale => $expected) {
            $negated = $this->negate();

            if ($expected === null || is_object($expected)) {
                // then
                $this->shouldThrow('\ArithmeticError')->during(
                    'dividedBy',
                    ['1', $scale, $roundingMode]
                );

                $negated->shouldThrow('\ArithmeticError')->during(
                    'dividedBy',
                    ['-1', $scale, $roundingMode]
                );

                continue;
            }

            // and let
            $actual = $this->dividedBy('1', $scale, $roundingMode);

            // then
            $actual->shouldBeAnInstanceOf(BigDecimal::class);
            // and
            $actual->unscaledValue()->shouldReturn($expected);
            // and
            $actual->scale()->shouldReturn($scale);

            // and let
            $actual = $negated->dividedBy('-1', $scale, $roundingMode);

            // then
            $actual->shouldBeAnInstanceOf(BigDecimal::class);
            // and
            $actual->unscaledValue()->shouldReturn($expected);
            // and
            $actual->scale()->shouldReturn($scale);
        }
    }

    public static function providerRoundingMode()
    {
        return [
            [RoundingMode::UP, '3.501', '351', '36', '4'],
            [RoundingMode::UP, '3.500', '350', '35', '4'],
            [RoundingMode::UP, '3.499', '350', '35', '4'],
            [RoundingMode::UP, '3.001', '301', '31', '4'],
            [RoundingMode::UP, '3.000', '300', '30', '3'],
            [RoundingMode::UP, '2.999', '300', '30', '3'],
            [RoundingMode::UP, '2.501', '251', '26', '3'],
            [RoundingMode::UP, '2.500', '250', '25', '3'],
            [RoundingMode::UP, '2.499', '250', '25', '3'],
            [RoundingMode::UP, '2.001', '201', '21', '3'],
            [RoundingMode::UP, '2.000', '200', '20', '2'],
            [RoundingMode::UP, '1.999', '200', '20', '2'],
            [RoundingMode::UP, '1.501', '151', '16', '2'],
            [RoundingMode::UP, '1.500', '150', '15', '2'],
            [RoundingMode::UP, '1.499', '150', '15', '2'],
            [RoundingMode::UP, '1.001', '101', '11', '2'],
            [RoundingMode::UP, '1.000', '100', '10', '1'],
            [RoundingMode::UP, '0.999', '100', '10', '1'],
            [RoundingMode::UP, '0.501', '51', '6', '1'],
            [RoundingMode::UP, '0.500', '50', '5', '1'],
            [RoundingMode::UP, '0.499', '50', '5', '1'],
            [RoundingMode::UP, '0.001', '1', '1', '1'],
            [RoundingMode::UP, '0.000', '0', '0', '0'],
            [RoundingMode::UP, '-0.001', '-1', '-1', '-1'],
            [RoundingMode::UP, '-0.499', '-50', '-5', '-1'],
            [RoundingMode::UP, '-0.500', '-50', '-5', '-1'],
            [RoundingMode::UP, '-0.501', '-51', '-6', '-1'],
            [RoundingMode::UP, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::UP, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::UP, '-1.001', '-101', '-11', '-2'],
            [RoundingMode::UP, '-1.499', '-150', '-15', '-2'],
            [RoundingMode::UP, '-1.500', '-150', '-15', '-2'],
            [RoundingMode::UP, '-1.501', '-151', '-16', '-2'],
            [RoundingMode::UP, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::UP, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::UP, '-2.001', '-201', '-21', '-3'],
            [RoundingMode::UP, '-2.499', '-250', '-25', '-3'],
            [RoundingMode::UP, '-2.500', '-250', '-25', '-3'],
            [RoundingMode::UP, '-2.501', '-251', '-26', '-3'],
            [RoundingMode::UP, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::UP, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::UP, '-3.001', '-301', '-31', '-4'],
            [RoundingMode::UP, '-3.499', '-350', '-35', '-4'],
            [RoundingMode::UP, '-3.500', '-350', '-35', '-4'],
            [RoundingMode::UP, '-3.501', '-351', '-36', '-4'],
            [RoundingMode::DOWN, '3.501', '350', '35', '3'],
            [RoundingMode::DOWN, '3.500', '350', '35', '3'],
            [RoundingMode::DOWN, '3.499', '349', '34', '3'],
            [RoundingMode::DOWN, '3.001', '300', '30', '3'],
            [RoundingMode::DOWN, '3.000', '300', '30', '3'],
            [RoundingMode::DOWN, '2.999', '299', '29', '2'],
            [RoundingMode::DOWN, '2.501', '250', '25', '2'],
            [RoundingMode::DOWN, '2.500', '250', '25', '2'],
            [RoundingMode::DOWN, '2.499', '249', '24', '2'],
            [RoundingMode::DOWN, '2.001', '200', '20', '2'],
            [RoundingMode::DOWN, '2.000', '200', '20', '2'],
            [RoundingMode::DOWN, '1.999', '199', '19', '1'],
            [RoundingMode::DOWN, '1.501', '150', '15', '1'],
            [RoundingMode::DOWN, '1.500', '150', '15', '1'],
            [RoundingMode::DOWN, '1.499', '149', '14', '1'],
            [RoundingMode::DOWN, '1.001', '100', '10', '1'],
            [RoundingMode::DOWN, '1.000', '100', '10', '1'],
            [RoundingMode::DOWN, '0.999', '99', '9', '0'],
            [RoundingMode::DOWN, '0.501', '50', '5', '0'],
            [RoundingMode::DOWN, '0.500', '50', '5', '0'],
            [RoundingMode::DOWN, '0.499', '49', '4', '0'],
            [RoundingMode::DOWN, '0.001', '0', '0', '0'],
            [RoundingMode::DOWN, '0.000', '0', '0', '0'],
            [RoundingMode::DOWN, '-0.001', '0', '0', '0'],
            [RoundingMode::DOWN, '-0.499', '-49', '-4', '0'],
            [RoundingMode::DOWN, '-0.500', '-50', '-5', '0'],
            [RoundingMode::DOWN, '-0.501', '-50', '-5', '0'],
            [RoundingMode::DOWN, '-0.999', '-99', '-9', '0'],
            [RoundingMode::DOWN, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::DOWN, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::DOWN, '-1.499', '-149', '-14', '-1'],
            [RoundingMode::DOWN, '-1.500', '-150', '-15', '-1'],
            [RoundingMode::DOWN, '-1.501', '-150', '-15', '-1'],
            [RoundingMode::DOWN, '-1.999', '-199', '-19', '-1'],
            [RoundingMode::DOWN, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::DOWN, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::DOWN, '-2.499', '-249', '-24', '-2'],
            [RoundingMode::DOWN, '-2.500', '-250', '-25', '-2'],
            [RoundingMode::DOWN, '-2.501', '-250', '-25', '-2'],
            [RoundingMode::DOWN, '-2.999', '-299', '-29', '-2'],
            [RoundingMode::DOWN, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::DOWN, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::DOWN, '-3.499', '-349', '-34', '-3'],
            [RoundingMode::DOWN, '-3.500', '-350', '-35', '-3'],
            [RoundingMode::DOWN, '-3.501', '-350', '-35', '-3'],
            [RoundingMode::CEILING, '3.501', '351', '36', '4'],
            [RoundingMode::CEILING, '3.500', '350', '35', '4'],
            [RoundingMode::CEILING, '3.499', '350', '35', '4'],
            [RoundingMode::CEILING, '3.001', '301', '31', '4'],
            [RoundingMode::CEILING, '3.000', '300', '30', '3'],
            [RoundingMode::CEILING, '2.999', '300', '30', '3'],
            [RoundingMode::CEILING, '2.501', '251', '26', '3'],
            [RoundingMode::CEILING, '2.500', '250', '25', '3'],
            [RoundingMode::CEILING, '2.499', '250', '25', '3'],
            [RoundingMode::CEILING, '2.001', '201', '21', '3'],
            [RoundingMode::CEILING, '2.000', '200', '20', '2'],
            [RoundingMode::CEILING, '1.999', '200', '20', '2'],
            [RoundingMode::CEILING, '1.501', '151', '16', '2'],
            [RoundingMode::CEILING, '1.500', '150', '15', '2'],
            [RoundingMode::CEILING, '1.499', '150', '15', '2'],
            [RoundingMode::CEILING, '1.001', '101', '11', '2'],
            [RoundingMode::CEILING, '1.000', '100', '10', '1'],
            [RoundingMode::CEILING, '0.999', '100', '10', '1'],
            [RoundingMode::CEILING, '0.501', '51', '6', '1'],
            [RoundingMode::CEILING, '0.500', '50', '5', '1'],
            [RoundingMode::CEILING, '0.499', '50', '5', '1'],
            [RoundingMode::CEILING, '0.001', '1', '1', '1'],
            [RoundingMode::CEILING, '0.000', '0', '0', '0'],
            [RoundingMode::CEILING, '-0.001', '0', '0', '0'],
            [RoundingMode::CEILING, '-0.499', '-49', '-4', '0'],
            [RoundingMode::CEILING, '-0.500', '-50', '-5', '0'],
            [RoundingMode::CEILING, '-0.501', '-50', '-5', '0'],
            [RoundingMode::CEILING, '-0.999', '-99', '-9', '0'],
            [RoundingMode::CEILING, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::CEILING, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::CEILING, '-1.499', '-149', '-14', '-1'],
            [RoundingMode::CEILING, '-1.500', '-150', '-15', '-1'],
            [RoundingMode::CEILING, '-1.501', '-150', '-15', '-1'],
            [RoundingMode::CEILING, '-1.999', '-199', '-19', '-1'],
            [RoundingMode::CEILING, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::CEILING, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::CEILING, '-2.499', '-249', '-24', '-2'],
            [RoundingMode::CEILING, '-2.500', '-250', '-25', '-2'],
            [RoundingMode::CEILING, '-2.501', '-250', '-25', '-2'],
            [RoundingMode::CEILING, '-2.999', '-299', '-29', '-2'],
            [RoundingMode::CEILING, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::CEILING, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::CEILING, '-3.499', '-349', '-34', '-3'],
            [RoundingMode::CEILING, '-3.500', '-350', '-35', '-3'],
            [RoundingMode::CEILING, '-3.501', '-350', '-35', '-3'],
            [RoundingMode::FLOOR, '3.501', '350', '35', '3'],
            [RoundingMode::FLOOR, '3.500', '350', '35', '3'],
            [RoundingMode::FLOOR, '3.499', '349', '34', '3'],
            [RoundingMode::FLOOR, '3.001', '300', '30', '3'],
            [RoundingMode::FLOOR, '3.000', '300', '30', '3'],
            [RoundingMode::FLOOR, '2.999', '299', '29', '2'],
            [RoundingMode::FLOOR, '2.501', '250', '25', '2'],
            [RoundingMode::FLOOR, '2.500', '250', '25', '2'],
            [RoundingMode::FLOOR, '2.499', '249', '24', '2'],
            [RoundingMode::FLOOR, '2.001', '200', '20', '2'],
            [RoundingMode::FLOOR, '2.000', '200', '20', '2'],
            [RoundingMode::FLOOR, '1.999', '199', '19', '1'],
            [RoundingMode::FLOOR, '1.501', '150', '15', '1'],
            [RoundingMode::FLOOR, '1.500', '150', '15', '1'],
            [RoundingMode::FLOOR, '1.499', '149', '14', '1'],
            [RoundingMode::FLOOR, '1.001', '100', '10', '1'],
            [RoundingMode::FLOOR, '1.000', '100', '10', '1'],
            [RoundingMode::FLOOR, '0.999', '99', '9', '0'],
            [RoundingMode::FLOOR, '0.501', '50', '5', '0'],
            [RoundingMode::FLOOR, '0.500', '50', '5', '0'],
            [RoundingMode::FLOOR, '0.499', '49', '4', '0'],
            [RoundingMode::FLOOR, '0.001', '0', '0', '0'],
            [RoundingMode::FLOOR, '0.000', '0', '0', '0'],
            [RoundingMode::FLOOR, '-0.001', '-1', '-1', '-1'],
            [RoundingMode::FLOOR, '-0.499', '-50', '-5', '-1'],
            [RoundingMode::FLOOR, '-0.500', '-50', '-5', '-1'],
            [RoundingMode::FLOOR, '-0.501', '-51', '-6', '-1'],
            [RoundingMode::FLOOR, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::FLOOR, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::FLOOR, '-1.001', '-101', '-11', '-2'],
            [RoundingMode::FLOOR, '-1.499', '-150', '-15', '-2'],
            [RoundingMode::FLOOR, '-1.500', '-150', '-15', '-2'],
            [RoundingMode::FLOOR, '-1.501', '-151', '-16', '-2'],
            [RoundingMode::FLOOR, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::FLOOR, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::FLOOR, '-2.001', '-201', '-21', '-3'],
            [RoundingMode::FLOOR, '-2.499', '-250', '-25', '-3'],
            [RoundingMode::FLOOR, '-2.500', '-250', '-25', '-3'],
            [RoundingMode::FLOOR, '-2.501', '-251', '-26', '-3'],
            [RoundingMode::FLOOR, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::FLOOR, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::FLOOR, '-3.001', '-301', '-31', '-4'],
            [RoundingMode::FLOOR, '-3.499', '-350', '-35', '-4'],
            [RoundingMode::FLOOR, '-3.500', '-350', '-35', '-4'],
            [RoundingMode::FLOOR, '-3.501', '-351', '-36', '-4'],
            [RoundingMode::HALF_UP, '3.501', '350', '35', '4'],
            [RoundingMode::HALF_UP, '3.500', '350', '35', '4'],
            [RoundingMode::HALF_UP, '3.499', '350', '35', '3'],
            [RoundingMode::HALF_UP, '3.001', '300', '30', '3'],
            [RoundingMode::HALF_UP, '3.000', '300', '30', '3'],
            [RoundingMode::HALF_UP, '2.999', '300', '30', '3'],
            [RoundingMode::HALF_UP, '2.501', '250', '25', '3'],
            [RoundingMode::HALF_UP, '2.500', '250', '25', '3'],
            [RoundingMode::HALF_UP, '2.499', '250', '25', '2'],
            [RoundingMode::HALF_UP, '2.001', '200', '20', '2'],
            [RoundingMode::HALF_UP, '2.000', '200', '20', '2'],
            [RoundingMode::HALF_UP, '1.999', '200', '20', '2'],
            [RoundingMode::HALF_UP, '1.501', '150', '15', '2'],
            [RoundingMode::HALF_UP, '1.500', '150', '15', '2'],
            [RoundingMode::HALF_UP, '1.499', '150', '15', '1'],
            [RoundingMode::HALF_UP, '1.001', '100', '10', '1'],
            [RoundingMode::HALF_UP, '1.000', '100', '10', '1'],
            [RoundingMode::HALF_UP, '0.999', '100', '10', '1'],
            [RoundingMode::HALF_UP, '0.501', '50', '5', '1'],
            [RoundingMode::HALF_UP, '0.500', '50', '5', '1'],
            [RoundingMode::HALF_UP, '0.499', '50', '5', '0'],
            [RoundingMode::HALF_UP, '0.001', '0', '0', '0'],
            [RoundingMode::HALF_UP, '0.000', '0', '0', '0'],
            [RoundingMode::HALF_UP, '-0.001', '0', '0', '0'],
            [RoundingMode::HALF_UP, '-0.499', '-50', '-5', '0'],
            [RoundingMode::HALF_UP, '-0.500', '-50', '-5', '-1'],
            [RoundingMode::HALF_UP, '-0.501', '-50', '-5', '-1'],
            [RoundingMode::HALF_UP, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::HALF_UP, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::HALF_UP, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::HALF_UP, '-1.499', '-150', '-15', '-1'],
            [RoundingMode::HALF_UP, '-1.500', '-150', '-15', '-2'],
            [RoundingMode::HALF_UP, '-1.501', '-150', '-15', '-2'],
            [RoundingMode::HALF_UP, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::HALF_UP, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::HALF_UP, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::HALF_UP, '-2.499', '-250', '-25', '-2'],
            [RoundingMode::HALF_UP, '-2.500', '-250', '-25', '-3'],
            [RoundingMode::HALF_UP, '-2.501', '-250', '-25', '-3'],
            [RoundingMode::HALF_UP, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::HALF_UP, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::HALF_UP, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::HALF_UP, '-3.499', '-350', '-35', '-3'],
            [RoundingMode::HALF_UP, '-3.500', '-350', '-35', '-4'],
            [RoundingMode::HALF_UP, '-3.501', '-350', '-35', '-4'],
            [RoundingMode::HALF_DOWN, '3.501', '350', '35', '4'],
            [RoundingMode::HALF_DOWN, '3.500', '350', '35', '3'],
            [RoundingMode::HALF_DOWN, '3.499', '350', '35', '3'],
            [RoundingMode::HALF_DOWN, '3.001', '300', '30', '3'],
            [RoundingMode::HALF_DOWN, '3.000', '300', '30', '3'],
            [RoundingMode::HALF_DOWN, '2.999', '300', '30', '3'],
            [RoundingMode::HALF_DOWN, '2.501', '250', '25', '3'],
            [RoundingMode::HALF_DOWN, '2.500', '250', '25', '2'],
            [RoundingMode::HALF_DOWN, '2.499', '250', '25', '2'],
            [RoundingMode::HALF_DOWN, '2.001', '200', '20', '2'],
            [RoundingMode::HALF_DOWN, '2.000', '200', '20', '2'],
            [RoundingMode::HALF_DOWN, '1.999', '200', '20', '2'],
            [RoundingMode::HALF_DOWN, '1.501', '150', '15', '2'],
            [RoundingMode::HALF_DOWN, '1.500', '150', '15', '1'],
            [RoundingMode::HALF_DOWN, '1.499', '150', '15', '1'],
            [RoundingMode::HALF_DOWN, '1.001', '100', '10', '1'],
            [RoundingMode::HALF_DOWN, '1.000', '100', '10', '1'],
            [RoundingMode::HALF_DOWN, '0.999', '100', '10', '1'],
            [RoundingMode::HALF_DOWN, '0.501', '50', '5', '1'],
            [RoundingMode::HALF_DOWN, '0.500', '50', '5', '0'],
            [RoundingMode::HALF_DOWN, '0.499', '50', '5', '0'],
            [RoundingMode::HALF_DOWN, '0.001', '0', '0', '0'],
            [RoundingMode::HALF_DOWN, '0.000', '0', '0', '0'],
            [RoundingMode::HALF_DOWN, '-0.001', '0', '0', '0'],
            [RoundingMode::HALF_DOWN, '-0.499', '-50', '-5', '0'],
            [RoundingMode::HALF_DOWN, '-0.500', '-50', '-5', '0'],
            [RoundingMode::HALF_DOWN, '-0.501', '-50', '-5', '-1'],
            [RoundingMode::HALF_DOWN, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::HALF_DOWN, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::HALF_DOWN, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::HALF_DOWN, '-1.499', '-150', '-15', '-1'],
            [RoundingMode::HALF_DOWN, '-1.500', '-150', '-15', '-1'],
            [RoundingMode::HALF_DOWN, '-1.501', '-150', '-15', '-2'],
            [RoundingMode::HALF_DOWN, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::HALF_DOWN, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::HALF_DOWN, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::HALF_DOWN, '-2.499', '-250', '-25', '-2'],
            [RoundingMode::HALF_DOWN, '-2.500', '-250', '-25', '-2'],
            [RoundingMode::HALF_DOWN, '-2.501', '-250', '-25', '-3'],
            [RoundingMode::HALF_DOWN, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::HALF_DOWN, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::HALF_DOWN, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::HALF_DOWN, '-3.499', '-350', '-35', '-3'],
            [RoundingMode::HALF_DOWN, '-3.500', '-350', '-35', '-3'],
            [RoundingMode::HALF_DOWN, '-3.501', '-350', '-35', '-4'],
            [RoundingMode::HALF_CEILING, '3.501', '350', '35', '4'],
            [RoundingMode::HALF_CEILING, '3.500', '350', '35', '4'],
            [RoundingMode::HALF_CEILING, '3.499', '350', '35', '3'],
            [RoundingMode::HALF_CEILING, '3.001', '300', '30', '3'],
            [RoundingMode::HALF_CEILING, '3.000', '300', '30', '3'],
            [RoundingMode::HALF_CEILING, '2.999', '300', '30', '3'],
            [RoundingMode::HALF_CEILING, '2.501', '250', '25', '3'],
            [RoundingMode::HALF_CEILING, '2.500', '250', '25', '3'],
            [RoundingMode::HALF_CEILING, '2.499', '250', '25', '2'],
            [RoundingMode::HALF_CEILING, '2.001', '200', '20', '2'],
            [RoundingMode::HALF_CEILING, '2.000', '200', '20', '2'],
            [RoundingMode::HALF_CEILING, '1.999', '200', '20', '2'],
            [RoundingMode::HALF_CEILING, '1.501', '150', '15', '2'],
            [RoundingMode::HALF_CEILING, '1.500', '150', '15', '2'],
            [RoundingMode::HALF_CEILING, '1.499', '150', '15', '1'],
            [RoundingMode::HALF_CEILING, '1.001', '100', '10', '1'],
            [RoundingMode::HALF_CEILING, '1.000', '100', '10', '1'],
            [RoundingMode::HALF_CEILING, '0.999', '100', '10', '1'],
            [RoundingMode::HALF_CEILING, '0.501', '50', '5', '1'],
            [RoundingMode::HALF_CEILING, '0.500', '50', '5', '1'],
            [RoundingMode::HALF_CEILING, '0.499', '50', '5', '0'],
            [RoundingMode::HALF_CEILING, '0.001', '0', '0', '0'],
            [RoundingMode::HALF_CEILING, '0.000', '0', '0', '0'],
            [RoundingMode::HALF_CEILING, '-0.001', '0', '0', '0'],
            [RoundingMode::HALF_CEILING, '-0.499', '-50', '-5', '0'],
            [RoundingMode::HALF_CEILING, '-0.500', '-50', '-5', '0'],
            [RoundingMode::HALF_CEILING, '-0.501', '-50', '-5', '-1'],
            [RoundingMode::HALF_CEILING, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::HALF_CEILING, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::HALF_CEILING, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::HALF_CEILING, '-1.499', '-150', '-15', '-1'],
            [RoundingMode::HALF_CEILING, '-1.500', '-150', '-15', '-1'],
            [RoundingMode::HALF_CEILING, '-1.501', '-150', '-15', '-2'],
            [RoundingMode::HALF_CEILING, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::HALF_CEILING, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::HALF_CEILING, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::HALF_CEILING, '-2.499', '-250', '-25', '-2'],
            [RoundingMode::HALF_CEILING, '-2.500', '-250', '-25', '-2'],
            [RoundingMode::HALF_CEILING, '-2.501', '-250', '-25', '-3'],
            [RoundingMode::HALF_CEILING, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::HALF_CEILING, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::HALF_CEILING, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::HALF_CEILING, '-3.499', '-350', '-35', '-3'],
            [RoundingMode::HALF_CEILING, '-3.500', '-350', '-35', '-3'],
            [RoundingMode::HALF_CEILING, '-3.501', '-350', '-35', '-4'],
            [RoundingMode::HALF_FLOOR, '3.501', '350', '35', '4'],
            [RoundingMode::HALF_FLOOR, '3.500', '350', '35', '3'],
            [RoundingMode::HALF_FLOOR, '3.499', '350', '35', '3'],
            [RoundingMode::HALF_FLOOR, '3.001', '300', '30', '3'],
            [RoundingMode::HALF_FLOOR, '3.000', '300', '30', '3'],
            [RoundingMode::HALF_FLOOR, '2.999', '300', '30', '3'],
            [RoundingMode::HALF_FLOOR, '2.501', '250', '25', '3'],
            [RoundingMode::HALF_FLOOR, '2.500', '250', '25', '2'],
            [RoundingMode::HALF_FLOOR, '2.499', '250', '25', '2'],
            [RoundingMode::HALF_FLOOR, '2.001', '200', '20', '2'],
            [RoundingMode::HALF_FLOOR, '2.000', '200', '20', '2'],
            [RoundingMode::HALF_FLOOR, '1.999', '200', '20', '2'],
            [RoundingMode::HALF_FLOOR, '1.501', '150', '15', '2'],
            [RoundingMode::HALF_FLOOR, '1.500', '150', '15', '1'],
            [RoundingMode::HALF_FLOOR, '1.499', '150', '15', '1'],
            [RoundingMode::HALF_FLOOR, '1.001', '100', '10', '1'],
            [RoundingMode::HALF_FLOOR, '1.000', '100', '10', '1'],
            [RoundingMode::HALF_FLOOR, '0.999', '100', '10', '1'],
            [RoundingMode::HALF_FLOOR, '0.501', '50', '5', '1'],
            [RoundingMode::HALF_FLOOR, '0.500', '50', '5', '0'],
            [RoundingMode::HALF_FLOOR, '0.499', '50', '5', '0'],
            [RoundingMode::HALF_FLOOR, '0.001', '0', '0', '0'],
            [RoundingMode::HALF_FLOOR, '0.000', '0', '0', '0'],
            [RoundingMode::HALF_FLOOR, '-0.001', '0', '0', '0'],
            [RoundingMode::HALF_FLOOR, '-0.499', '-50', '-5', '0'],
            [RoundingMode::HALF_FLOOR, '-0.500', '-50', '-5', '-1'],
            [RoundingMode::HALF_FLOOR, '-0.501', '-50', '-5', '-1'],
            [RoundingMode::HALF_FLOOR, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::HALF_FLOOR, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::HALF_FLOOR, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::HALF_FLOOR, '-1.499', '-150', '-15', '-1'],
            [RoundingMode::HALF_FLOOR, '-1.500', '-150', '-15', '-2'],
            [RoundingMode::HALF_FLOOR, '-1.501', '-150', '-15', '-2'],
            [RoundingMode::HALF_FLOOR, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::HALF_FLOOR, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::HALF_FLOOR, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::HALF_FLOOR, '-2.499', '-250', '-25', '-2'],
            [RoundingMode::HALF_FLOOR, '-2.500', '-250', '-25', '-3'],
            [RoundingMode::HALF_FLOOR, '-2.501', '-250', '-25', '-3'],
            [RoundingMode::HALF_FLOOR, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::HALF_FLOOR, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::HALF_FLOOR, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::HALF_FLOOR, '-3.499', '-350', '-35', '-3'],
            [RoundingMode::HALF_FLOOR, '-3.500', '-350', '-35', '-4'],
            [RoundingMode::HALF_FLOOR, '-3.501', '-350', '-35', '-4'],
            [RoundingMode::HALF_EVEN, '3.501', '350', '35', '4'],
            [RoundingMode::HALF_EVEN, '3.500', '350', '35', '4'],
            [RoundingMode::HALF_EVEN, '3.499', '350', '35', '3'],
            [RoundingMode::HALF_EVEN, '3.001', '300', '30', '3'],
            [RoundingMode::HALF_EVEN, '3.000', '300', '30', '3'],
            [RoundingMode::HALF_EVEN, '2.999', '300', '30', '3'],
            [RoundingMode::HALF_EVEN, '2.501', '250', '25', '3'],
            [RoundingMode::HALF_EVEN, '2.500', '250', '25', '2'],
            [RoundingMode::HALF_EVEN, '2.499', '250', '25', '2'],
            [RoundingMode::HALF_EVEN, '2.001', '200', '20', '2'],
            [RoundingMode::HALF_EVEN, '2.000', '200', '20', '2'],
            [RoundingMode::HALF_EVEN, '1.999', '200', '20', '2'],
            [RoundingMode::HALF_EVEN, '1.501', '150', '15', '2'],
            [RoundingMode::HALF_EVEN, '1.500', '150', '15', '2'],
            [RoundingMode::HALF_EVEN, '1.499', '150', '15', '1'],
            [RoundingMode::HALF_EVEN, '1.001', '100', '10', '1'],
            [RoundingMode::HALF_EVEN, '1.000', '100', '10', '1'],
            [RoundingMode::HALF_EVEN, '0.999', '100', '10', '1'],
            [RoundingMode::HALF_EVEN, '0.501', '50', '5', '1'],
            [RoundingMode::HALF_EVEN, '0.500', '50', '5', '0'],
            [RoundingMode::HALF_EVEN, '0.499', '50', '5', '0'],
            [RoundingMode::HALF_EVEN, '0.001', '0', '0', '0'],
            [RoundingMode::HALF_EVEN, '0.000', '0', '0', '0'],
            [RoundingMode::HALF_EVEN, '-0.001', '0', '0', '0'],
            [RoundingMode::HALF_EVEN, '-0.499', '-50', '-5', '0'],
            [RoundingMode::HALF_EVEN, '-0.500', '-50', '-5', '0'],
            [RoundingMode::HALF_EVEN, '-0.501', '-50', '-5', '-1'],
            [RoundingMode::HALF_EVEN, '-0.999', '-100', '-10', '-1'],
            [RoundingMode::HALF_EVEN, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::HALF_EVEN, '-1.001', '-100', '-10', '-1'],
            [RoundingMode::HALF_EVEN, '-1.499', '-150', '-15', '-1'],
            [RoundingMode::HALF_EVEN, '-1.500', '-150', '-15', '-2'],
            [RoundingMode::HALF_EVEN, '-1.501', '-150', '-15', '-2'],
            [RoundingMode::HALF_EVEN, '-1.999', '-200', '-20', '-2'],
            [RoundingMode::HALF_EVEN, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::HALF_EVEN, '-2.001', '-200', '-20', '-2'],
            [RoundingMode::HALF_EVEN, '-2.499', '-250', '-25', '-2'],
            [RoundingMode::HALF_EVEN, '-2.500', '-250', '-25', '-2'],
            [RoundingMode::HALF_EVEN, '-2.501', '-250', '-25', '-3'],
            [RoundingMode::HALF_EVEN, '-2.999', '-300', '-30', '-3'],
            [RoundingMode::HALF_EVEN, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::HALF_EVEN, '-3.001', '-300', '-30', '-3'],
            [RoundingMode::HALF_EVEN, '-3.499', '-350', '-35', '-3'],
            [RoundingMode::HALF_EVEN, '-3.500', '-350', '-35', '-4'],
            [RoundingMode::HALF_EVEN, '-3.501', '-350', '-35', '-4'],
            [RoundingMode::UNNECESSARY, '3.501', null, null, null],
            [RoundingMode::UNNECESSARY, '3.500', '350', '35', null],
            [RoundingMode::UNNECESSARY, '3.499', null, null, null],
            [RoundingMode::UNNECESSARY, '3.001', null, null, null],
            [RoundingMode::UNNECESSARY, '3.000', '300', '30', '3'],
            [RoundingMode::UNNECESSARY, '2.999', null, null, null],
            [RoundingMode::UNNECESSARY, '2.501', null, null, null],
            [RoundingMode::UNNECESSARY, '2.500', '250', '25', null],
            [RoundingMode::UNNECESSARY, '2.499', null, null, null],
            [RoundingMode::UNNECESSARY, '2.001', null, null, null],
            [RoundingMode::UNNECESSARY, '2.000', '200', '20', '2'],
            [RoundingMode::UNNECESSARY, '1.999', null, null, null],
            [RoundingMode::UNNECESSARY, '1.501', null, null, null],
            [RoundingMode::UNNECESSARY, '1.500', '150', '15', null],
            [RoundingMode::UNNECESSARY, '1.499', null, null, null],
            [RoundingMode::UNNECESSARY, '1.001', null, null, null],
            [RoundingMode::UNNECESSARY, '1.000', '100', '10', '1'],
            [RoundingMode::UNNECESSARY, '0.999', null, null, null],
            [RoundingMode::UNNECESSARY, '0.501', null, null, null],
            [RoundingMode::UNNECESSARY, '0.500', '50', '5', null],
            [RoundingMode::UNNECESSARY, '0.499', null, null, null],
            [RoundingMode::UNNECESSARY, '0.001', null, null, null],
            [RoundingMode::UNNECESSARY, '0.000', '0', '0', '0'],
            [RoundingMode::UNNECESSARY, '-0.001', null, null, null],
            [RoundingMode::UNNECESSARY, '-0.499', null, null, null],
            [RoundingMode::UNNECESSARY, '-0.500', '-50', '-5', null],
            [RoundingMode::UNNECESSARY, '-0.501', null, null, null],
            [RoundingMode::UNNECESSARY, '-0.999', null, null, null],
            [RoundingMode::UNNECESSARY, '-1.000', '-100', '-10', '-1'],
            [RoundingMode::UNNECESSARY, '-1.001', null, null, null],
            [RoundingMode::UNNECESSARY, '-1.499', null, null, null],
            [RoundingMode::UNNECESSARY, '-1.500', '-150', '-15', null],
            [RoundingMode::UNNECESSARY, '-1.501', null, null, null],
            [RoundingMode::UNNECESSARY, '-1.999', null, null, null],
            [RoundingMode::UNNECESSARY, '-2.000', '-200', '-20', '-2'],
            [RoundingMode::UNNECESSARY, '-2.001', null, null, null],
            [RoundingMode::UNNECESSARY, '-2.499', null, null, null],
            [RoundingMode::UNNECESSARY, '-2.500', '-250', '-25', null],
            [RoundingMode::UNNECESSARY, '-2.501', null, null, null],
            [RoundingMode::UNNECESSARY, '-2.999', null, null, null],
            [RoundingMode::UNNECESSARY, '-3.000', '-300', '-30', '-3'],
            [RoundingMode::UNNECESSARY, '-3.001', null, null, null],
            [RoundingMode::UNNECESSARY, '-3.499', null, null, null],
            [RoundingMode::UNNECESSARY, '-3.500', '-350', '-35', null],
            [RoundingMode::UNNECESSARY, '-3.501', null, null, null],
        ];
    }

    /**
     * @dataProvider providerQuotientAndRemainder
     */
    function it_computes_the_quotient_and_the_remainder_from_a_division($dividend, $divisor, $quotient, $remainder)
    {
        // let
        $this->beConstructedThroughOf($dividend);
        // and
        list ($q, $r) = $this->quotientAndRemainder($divisor);

        // then
        $this->quotient($divisor)->shouldBeAnInstanceOf(BigDecimal::class);
        $this->quotient($divisor)->__toString()->shouldBeEqualTo($quotient);
        $q->shouldBeAnInstanceOf(BigDecimal::class);
        $q->__toString()->shouldBeEqualTo($quotient);

        $this->remainder($divisor)->shouldBeAnInstanceOf(BigDecimal::class);
        $this->remainder($divisor)->__toString()->shouldBeEqualTo($remainder);
        $r->shouldBeAnInstanceOf(BigDecimal::class);
        $r->__toString()->shouldBeEqualTo($remainder);
    }

    public static function providerQuotientAndRemainder()
    {
        return [
            ['1', '123', '0', '1'],
            ['1', '-123', '0', '1'],
            ['-1', '123', '0', '-1'],
            ['-1', '-123', '0', '-1'],
            ['1999999999999999999999999', '2000000000000000000000000', '0', '1999999999999999999999999'],
            ['1999999999999999999999999', '-2000000000000000000000000', '0', '1999999999999999999999999'],
            ['-1999999999999999999999999', '2000000000000000000000000', '0', '-1999999999999999999999999'],
            ['-1999999999999999999999999', '-2000000000000000000000000', '0', '-1999999999999999999999999'],
            ['123', '1', '123', '0'],
            ['123', '-1', '-123', '0'],
            ['-123', '1', '-123', '0'],
            ['-123', '-1', '123', '0'],
            ['123', '2', '61', '1'],
            ['123', '-2', '-61', '1'],
            ['-123', '2', '-61', '-1'],
            ['-123', '-2', '61', '-1'],
            ['123', '123', '1', '0'],
            ['123', '-123', '-1', '0'],
            ['-123', '123', '-1', '0'],
            ['-123', '-123', '1', '0'],
            ['123', '124', '0', '123'],
            ['123', '-124', '0', '123'],
            ['-123', '124', '0', '-123'],
            ['-123', '-124', '0', '-123'],
            ['124', '123', '1', '1'],
            ['124', '-123', '-1', '1'],
            ['-124', '123', '-1', '-1'],
            ['-124', '-123', '1', '-1'],
            ['1000000000000000000000000000000', '3', '333333333333333333333333333333', '1'],
            ['1000000000000000000000000000000', '9', '111111111111111111111111111111', '1'],
            ['1000000000000000000000000000000', '11', '90909090909090909090909090909', '1'],
            ['1000000000000000000000000000000', '13', '76923076923076923076923076923', '1'],
            ['1000000000000000000000000000000', '21', '47619047619047619047619047619', '1'],
            ['123456789123456789123456789', '987654321987654321', '124999998', '850308642973765431'],
            ['123456789123456789123456789', '-87654321987654321', '-1408450676', '65623397056685793'],
            ['-123456789123456789123456789', '7654321987654321', '-16129030020', '-1834176331740369'],
            ['-123456789123456789123456789', '-654321987654321', '188678955396', '-205094497790673'],
            ['10.11', '3.3', '3', '0.21'],
            ['1', '-0.0013', '-769', '0.0003'],
            ['-1.000000000000000000001', '0.0000009298439898981609', '-1075449', '-0.0000002109080127582569'],
            [
                '-1278438782896060000132323.32333',
                '-53.4836775545640521556878910541',
                '23903344746475158719036',
                '-30.0786684482104867175202241524',
            ],
            [
                '23999593472872987498347103908209387429846376',
                '-0.005',
                '-4799918694574597499669420781641877485969275200',
                '0.000',
            ],
            ['1000000000000000000000000000000.0', '3', '333333333333333333333333333333', '1.0'],
            ['1000000000000000000000000000000.0', '9', '111111111111111111111111111111', '1.0'],
            ['1000000000000000000000000000000.0', '11', '90909090909090909090909090909', '1.0'],
            ['1000000000000000000000000000000.0', '13', '76923076923076923076923076923', '1.0'],
            ['0.9999999999999999999999999999999', '0.21', '4', '0.1599999999999999999999999999999'],
            ['1000000000000000000000000000000.0', '3.9', '256410256410256410256410256410', '1.0'],
            ['-1000000000000000000000000000000.0', '9.8', '-102040816326530612244897959183', '-6.6'],
            ['1000000000000000000000000000000.0', '-11.7', '-85470085470085470085470085470', '1.0'],
            ['-1000000000000000000000000000000.0', '-13.7', '72992700729927007299270072992', '-9.6'],
            ['0.99999999999999999999999999999999', '0.215', '4', '0.13999999999999999999999999999999'],
        ];
    }

    function it_throws_exception_when_calculating_the_quotient_of_division_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1.2);

        // then
        $this->shouldThrow('\DivisionByZeroError')->during('quotient', [0]);
    }

    function it_throws_exception_when_calculating_the_remainder_of_division_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1.2);

        // then
        $this->shouldThrow('\DivisionByZeroError')->during('remainder', [0]);
    }

    function it_throws_exception_when_calculating_the_quotient_and_remainder_of_division_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1.2);

        // then
        $this->shouldThrow('\DivisionByZeroError')->during('quotientAndRemainder', [0]);
    }

    /**
     * @dataProvider providerPower
     */
    function it_computes_the_power_of_a_decimal_at_a_given_exponent($number, $exponent, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $power = $this->power($exponent);

        // then
        $power->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $power->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $power->scale()->shouldReturn($scale);
    }

    public static function providerPower()
    {
        return [
            ['-3', 0, '1', 0],
            ['-2', 0, '1', 0],
            ['-1', 0, '1', 0],
            ['0', 0, '1', 0],
            ['1', 0, '1', 0],
            ['2', 0, '1', 0],
            ['3', 0, '1', 0],
            ['-3', 1, '-3', 0],
            ['-2', 1, '-2', 0],
            ['-1', 1, '-1', 0],
            ['0', 1, '0', 0],
            ['1', 1, '1', 0],
            ['2', 1, '2', 0],
            ['3', 1, '3', 0],
            ['-3', 2, '9', 0],
            ['-2', 2, '4', 0],
            ['-1', 2, '1', 0],
            ['0', 2, '0', 0],
            ['1', 2, '1', 0],
            ['2', 2, '4', 0],
            ['3', 2, '9', 0],
            ['-3', 3, '-27', 0],
            ['-2', 3, '-8', 0],
            ['-1', 3, '-1', 0],
            ['0', 3, '0', 0],
            ['1', 3, '1', 0],
            ['2', 3, '8', 0],
            ['3', 3, '27', 0],
            ['0', 1000000, '0', 0],
            ['1', 1000000, '1', 0],
            ['-2', 255, '-57896044618658097711785492504343953926634992332820282019728792003956564819968', 0],
            ['2', 256, '115792089237316195423570985008687907853269984665640564039457584007913129639936', 0],
            ['-1.23', 0, '1', 0],
            ['-1.23', 0, '1', 0],
            ['-1.23', 33, '-926549609804623448265268294182900512918058893428212027689876489708283', 66],
            ['1.23', 34, '113965602005968684136628000184496763088921243891670079405854808234118809', 68],
            ['-123456789', 8, '53965948844821664748141453212125737955899777414752273389058576481', 0],
            ['9876543210', 7, '9167159269868350921847491739460569765344716959834325922131706410000000', 0],
        ];
    }

    /**
     * @dataProvider providerPowerWithInvalidExponentThrowsException
     */
    function it_throws_exception_when_calculating_the_power_with_an_invalid_exponent($power)
    {
        // let
        $this->beConstructedThroughOf(1);

        // then
        $this->shouldThrow('\InvalidArgumentException')->during('power', [$power]);
    }

    public static function providerPowerWithInvalidExponentThrowsException()
    {
        return [
            [-1],
            [1000001],
        ];
    }

    /**
     * @dataProvider withScaleProvider
     */
    function it_changes_the_scale_of_a_decimal($number, $withScale, $roundingMode, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $decimal = $this->toScale($withScale, $roundingMode);

        // then
        $decimal->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $decimal->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $decimal->scale()->shouldReturn($scale);
    }

    public static function withScaleProvider()
    {
        return [
            ['123.45', 0, RoundingMode::DOWN, '123', 0],
            ['123.45', 1, RoundingMode::UP, '1235', 1],
            ['123.45', 2, RoundingMode::UNNECESSARY, '12345', 2],
            ['123.45', 5, RoundingMode::UNNECESSARY, '12345000', 5],
        ];
    }

    /**
     * @dataProvider providerWithPointMovedLeft
     */
    function it_moves_the_pointer_to_left($number, $places, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $decimal = $this->withPointMovedLeft($places);

        // then
        $decimal->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $decimal->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerWithPointMovedLeft()
    {
        return [
            ['0', -2, '0'],
            ['0', -1, '0'],
            ['0', 0, '0'],
            ['0', 1, '0.0'],
            ['0', 2, '0.00'],
            ['0.0', -2, '0'],
            ['0.0', -1, '0'],
            ['0.0', 0, '0.0'],
            ['0.0', 1, '0.00'],
            ['0.0', 2, '0.000'],
            ['1', -2, '100'],
            ['1', -1, '10'],
            ['1', 0, '1'],
            ['1', 1, '0.1'],
            ['1', 2, '0.01'],
            ['12', -2, '1200'],
            ['12', -1, '120'],
            ['12', 0, '12'],
            ['12', 1, '1.2'],
            ['12', 2, '0.12'],
            ['1.1', -2, '110'],
            ['1.1', -1, '11'],
            ['1.1', 0, '1.1'],
            ['1.1', 1, '0.11'],
            ['1.1', 2, '0.011'],
            ['0.1', -2, '10'],
            ['0.1', -1, '1'],
            ['0.1', 0, '0.1'],
            ['0.1', 1, '0.01'],
            ['0.1', 2, '0.001'],
            ['0.01', -2, '1'],
            ['0.01', -1, '0.1'],
            ['0.01', 0, '0.01'],
            ['0.01', 1, '0.001'],
            ['0.01', 2, '0.0001'],
            ['-9', -2, '-900'],
            ['-9', -1, '-90'],
            ['-9', 0, '-9'],
            ['-9', 1, '-0.9'],
            ['-9', 2, '-0.09'],
            ['-0.9', -2, '-90'],
            ['-0.9', -1, '-9'],
            ['-0.9', 0, '-0.9'],
            ['-0.9', 1, '-0.09'],
            ['-0.9', 2, '-0.009'],
            ['-0.09', -2, '-9'],
            ['-0.09', -1, '-0.9'],
            ['-0.09', 0, '-0.09'],
            ['-0.09', 1, '-0.009'],
            ['-0.09', 2, '-0.0009'],
            ['-12.3', -2, '-1230'],
            ['-12.3', -1, '-123'],
            ['-12.3', 0, '-12.3'],
            ['-12.3', 1, '-1.23'],
            ['-12.3', 2, '-0.123'],
        ];
    }

    /**
     * @dataProvider providerWithPointMovedRight
     */
    function it_moves_the_pointer_to_right($number, $places, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $decimal = $this->withPointMovedRight($places);

        // then
        $decimal->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $decimal->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerWithPointMovedRight()
    {
        return [
            ['0', -2, '0.00'],
            ['0', -1, '0.0'],
            ['0', 0, '0'],
            ['0', 1, '0'],
            ['0', 2, '0'],
            ['0.0', -2, '0.000'],
            ['0.0', -1, '0.00'],
            ['0.0', 0, '0.0'],
            ['0.0', 1, '0'],
            ['0.0', 2, '0'],
            ['9', -2, '0.09'],
            ['9', -1, '0.9'],
            ['9', 0, '9'],
            ['9', 1, '90'],
            ['9', 2, '900'],
            ['89', -2, '0.89'],
            ['89', -1, '8.9'],
            ['89', 0, '89'],
            ['89', 1, '890'],
            ['89', 2, '8900'],
            ['8.9', -2, '0.089'],
            ['8.9', -1, '0.89'],
            ['8.9', 0, '8.9'],
            ['8.9', 1, '89'],
            ['8.9', 2, '890'],
            ['0.9', -2, '0.009'],
            ['0.9', -1, '0.09'],
            ['0.9', 0, '0.9'],
            ['0.9', 1, '9'],
            ['0.9', 2, '90'],
            ['0.09', -2, '0.0009'],
            ['0.09', -1, '0.009'],
            ['0.09', 0, '0.09'],
            ['0.09', 1, '0.9'],
            ['0.09', 2, '9'],
            ['-1', -2, '-0.01'],
            ['-1', -1, '-0.1'],
            ['-1', 0, '-1'],
            ['-1', 1, '-10'],
            ['-1', 2, '-100'],
            ['-0.1', -2, '-0.001'],
            ['-0.1', -1, '-0.01'],
            ['-0.1', 0, '-0.1'],
            ['-0.1', 1, '-1'],
            ['-0.1', 2, '-10'],
            ['-0.01', -2, '-0.0001'],
            ['-0.01', -1, '-0.001'],
            ['-0.01', 0, '-0.01'],
            ['-0.01', 1, '-0.1'],
            ['-0.01', 2, '-1'],
            ['-12.3', -2, '-0.123'],
            ['-12.3', -1, '-1.23'],
            ['-12.3', 0, '-12.3'],
            ['-12.3', 1, '-123'],
            ['-12.3', 2, '-1230'],
        ];
    }

    /**
     * @dataProvider providerStripTrailingZeros
     */
    function it_strips_trailing_zeros($number, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $decimal = $this->stripTrailingZeros();

        // then
        $decimal->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $decimal->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerStripTrailingZeros()
    {
        return [
            ['0', '0'],
            ['0.0', '0'],
            ['0.00', '0'],
            ['0.000', '0'],
            ['0.1', '0.1'],
            ['0.01', '0.01'],
            ['0.001', '0.001'],
            ['0.100', '0.1'],
            ['0.0100', '0.01'],
            ['0.00100', '0.001'],
            ['1', '1'],
            ['1.0', '1'],
            ['1.00', '1'],
            ['1.10', '1.1'],
            ['1.123000', '1.123'],
            ['10', '10'],
            ['10.0', '10'],
            ['10.00', '10'],
            ['10.10', '10.1'],
            ['10.01', '10.01'],
            ['10.010', '10.01'],
            ['100', '100'],
            ['100.0', '100'],
            ['100.00', '100'],
            ['100.01', '100.01'],
            ['100.10', '100.1'],
            ['100.010', '100.01'],
            ['100.100', '100.1'],
        ];
    }

    /**
     * @dataProvider providerAbs
     */
    function it_calculates_the_absolute_value_of_a_number($number, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $decimal = $this->abs();

        // then
        $decimal->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $decimal->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $decimal->scale()->shouldReturn($scale);
    }

    public static function providerAbs()
    {
        return [
            ['123', '123', 0],
            ['-123', '123', 0],
            ['123.456', '123456', 3],
            ['-123.456', '123456', 3],
        ];
    }

    /**
     * @dataProvider providerNegated
     */
    function it_calculates_the_negated_value_of_a_number($number, $unscaledValue, $scale)
    {
        // let
        $this->beConstructedThroughOf($number);
        // and
        $decimal = $this->negate();

        // then
        $decimal->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $decimal->unscaledValue()->shouldReturn($unscaledValue);
        // and
        $decimal->scale()->shouldReturn($scale);
    }

    public static function providerNegated()
    {
        return [
            ['123', '-123', 0],
            ['-123', '123', 0],
            ['123.456', '-123456', 3],
            ['-123.456', '123456', 3],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_compares_this_number_to_the_given_one($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->compareTo($b)->shouldBeEqualTo($c);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_a_number_is_equal_to_a_given_one($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isEqualTo($b)->shouldBeEqualTo($c == 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_a_number_is_less_than_a_given_one($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isLessThan($b)->shouldBeEqualTo($c < 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_a_number_is_less_than_or_equal_to_a_given_one($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isLessThanOrEqualTo($b)->shouldBeEqualTo($c <= 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_a_number_is_greater_than_a_given_one($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isGreaterThan($b)->shouldBeEqualTo($c > 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_a_number_is_greater_than_or_equal_to_a_given_one($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isGreaterThanOrEqualTo($b)->shouldBeEqualTo($c >= 0);
    }

    public static function providerCompareTo()
    {
        return [
            ['123', '123', 0],
            ['123', '456', -1],
            ['456', '123', 1],
            ['456', '456', 0],
            ['-123', '-123', 0],
            ['-123', '456', -1],
            ['456', '-123', 1],
            ['456', '456', 0],
            ['123', '123', 0],
            ['123', '-456', 1],
            ['-456', '123', -1],
            ['-456', '456', -1],
            ['-123', '-123', 0],
            ['-123', '-456', 1],
            ['-456', '-123', -1],
            ['-456', '-456', 0],
            ['123.000000000000000000000000000000000000000000000', '123', 0],
            ['123.000000000000000000000000000000000000000000001', '123', 1],
            ['122.999999999999999999999999999999999999999999999', '123', -1],
            ['123.0', '123.000000000000000000000000000000000000000000000', 0],
            ['123.0', '123.000000000000000000000000000000000000000000001', -1],
            ['123.0', '122.999999999999999999999999999999999999999999999', 1],
            ['-0.000000000000000000000000000000000000000000000000001', '0', -1],
            ['0.000000000000000000000000000000000000000000000000001', '0', 1],
            ['0.000000000000000000000000000000000000000000000000000', '0', 0],
            ['0', '-0.000000000000000000000000000000000000000000000000001', 1],
            ['0', '0.000000000000000000000000000000000000000000000000001', -1],
            ['0', '0.000000000000000000000000000000000000000000000000000', 0],
            ['123.9999999999999999999999999999999999999', 124, -1],
            ['124.0000000000000000000000000000000000000', '124', 0],
            ['124.0000000000000000000000000000000000001', 124.0, 1],
            ['123.9999999999999999999999999999999999999', '1508517100733469660019804/12165460489786045645321', -1],
            ['124.0000000000000000000000000000000000000', '1508517100733469660019804/12165460489786045645321', 0],
            ['124.0000000000000000000000000000000000001', '1508517100733469660019804/12165460489786045645321', 1],
        ];
    }

    /**
     * @dataProvider providerSign
     */
    function it_returns_the_sign_of_a_number($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->signum()->shouldBeEqualTo($sign);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_a_given_number_equals_zero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isZero()->shouldBeEqualTo($sign === 0);
    }

    /**
     * @dataProvider providerSign
     */
    function testIsNegative($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isNegative()->shouldBeEqualTo($sign < 0);
    }

    /**
     * @dataProvider providerSign
     */
    function testIsNegativeOrZero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isNegativeOrZero()->shouldBeEqualTo($sign <= 0);
    }

    /**
     * @dataProvider providerSign
     */
    function testIsPositive($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isPositive()->shouldBeEqualTo($sign > 0);
    }

    /**
     * @dataProvider providerSign
     */
    function testIsPositiveOrZero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isPositiveOrZero()->shouldBeEqualTo($sign >= 0);
    }

    public static function providerSign()
    {
        return [
            [0, 0],
            [-0, 0],
            [1, 1],
            [-1, -1],
            [PHP_INT_MAX, 1],
            [~PHP_INT_MAX, -1],
            [1.0, 1],
            [-1.0, -1],
            [0.1, 1],
            [-0.1, -1],
            [0.0, 0],
            [-0.0, 0],
            ['1.00', 1],
            ['-1.00', -1],
            ['0.10', 1],
            ['-0.10', -1],
            ['0.01', 1],
            ['-0.01', -1],
            ['0.00', 0],
            ['-0.00', 0],
            ['0.000000000000000000000000000000000000000000000000000000000000000000000000000001', 1],
            ['0.000000000000000000000000000000000000000000000000000000000000000000000000000000', 0],
            ['-0.000000000000000000000000000000000000000000000000000000000000000000000000000001', -1],
        ];
    }

    /**
     * @dataProvider providerIntegral
     */
    function it_calculates_the_integral_part_of_a_decimal_number($number, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->integral()->shouldBeEqualTo($expected);
    }

    public static function providerIntegral()
    {
        return [
            ['1.23', '1'],
            ['-1.23', '-1'],
            ['0.123', '0'],
            ['0.001', '0'],
            ['123.0', '123'],
            ['12', '12'],
            ['1234.5678', '1234'],
        ];
    }

    /**
     * @dataProvider providerFraction
     */
    function it_calculates_the_fractional_part_of_a_decimal_number($number, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->fraction()->shouldBeEqualTo($expected);
    }

    public static function providerFraction()
    {
        return [
            ['1.23', '23'],
            ['-1.23', '23'],
            ['1', ''],
            ['-1', ''],
            ['0', ''],
            ['0.001', '001'],
        ];
    }

    /**
     * @dataProvider providerToBigInteger
     */
    function it_converts_a_given_number_to_a_BigInteger($decimal, $expected)
    {
        // let
        $this->beConstructedThroughOf($decimal);
        // and
        $integer = $this->toBigInteger();

        // then
        $integer->__toString()->shouldBeEqualTo($expected);
        $integer->shouldBeAnInstanceOf(BigInteger::class);
    }

    public static function providerToBigInteger()
    {
        return [
            ['0', '0'],
            ['1', '1'],
            ['0.0', '0'],
            ['1.0', '1'],
            ['-45646540654984984654165151654557478978940.0000000000000', '-45646540654984984654165151654557478978940'],
        ];
    }

    /**
     * @dataProvider providerToBigIntegerThrowsExceptionWhenRoundingNecessary
     */
    function it_throws_exception_when_converting_to_BigInteger_and_rounding_is_necessary($decimal)
    {
        // let
        $this->beConstructedThroughOf($decimal);

        // then
        $this->shouldThrow('\ArithmeticError')->during('toBigInteger');
    }

    public static function providerToBigIntegerThrowsExceptionWhenRoundingNecessary()
    {
        return [
            ['0.1'],
            ['-0.1'],
            ['0.01'],
            ['-0.01'],
            ['1.002'],
            ['0.001'],
            ['-1.002'],
            ['-0.001'],
            ['-45646540654984984654165151654557478978940.0000000000001'],
        ];
    }

    /**
     * @dataProvider providerToBigRational
     */
    function it_converts_a_given_number_to_a_big_rational($decimal, $expected)
    {
        // let
        $this->beConstructedThroughOf($decimal);
        // and
        $rational = $this->toBigRational();

        // then
        $rational->__toString()->shouldBeEqualTo($expected);
        $rational->shouldBeAnInstanceOf(BigRational::class);
    }

    public static function providerToBigRational()
    {
        return [
            ['0', '0'],
            ['1', '1'],
            ['-1', '-1'],
            ['0.0', '0/10'],
            ['1.0', '10/10'],
            ['-1.0', '-10/10'],
            ['0.00', '0/100'],
            ['1.00', '100/100'],
            ['-1.00', '-100/100'],
            ['0.9', '9/10'],
            ['0.90', '90/100'],
            ['0.900', '900/1000'],
            ['0.10', '10/100'],
            ['0.11', '11/100'],
            ['0.99', '99/100'],
            ['0.990', '990/1000'],
            ['0.9900', '9900/10000'],
            ['1.01', '101/100'],
            ['-1.001', '-1001/1000'],
            ['-1.010', '-1010/1000'],
            [
                '77867087546465423456465427464560454054654.4211684848',
                '778670875464654234564654274645604540546544211684848/10000000000',
            ],
        ];
    }

    /**
     * @dataProvider providerToInteger
     */
    function it_converts_a_given_number_to_native_integer($number)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->toInteger()->shouldBeEqualTo($number);
        $this::of($number.'.0')->toInteger()->shouldBeEqualTo($number);
    }

    public static function providerToInteger()
    {
        return [
            [~PHP_INT_MAX],
            [-123456789],
            [-1],
            [0],
            [1],
            [123456789],
            [PHP_INT_MAX],
        ];
    }

    /**
     * @dataProvider providerToIntegerThrowsException
     */
    function it_throws_exception_when_a_valid_decimal_number_cannot_safely_be_converted_to_a_native_integer($number)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->shouldThrow('\ArithmeticError')->during('toInteger');
    }

    public static function providerToIntegerThrowsException()
    {
        return [
            ['-999999999999999999999999999999'],
            ['9999999999999999999999999999999'],
            ['1.2'],
            ['-1.2'],
        ];
    }

    /**
     * @dataProvider providerToFloat
     */
    function it_converts_a_given_number_to_native_float($number, $float)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->toFloat()->shouldBeEqualTo($float);
    }

    public static function providerToFloat()
    {
        return [
            ['0', 0.0],
            ['1.6', 1.6],
            ['-1.6', -1.6],
            ['9.999999999999999999999999999999999999999999999999999999999999', 9.999999999999999999999999999999],
            ['-9.999999999999999999999999999999999999999999999999999999999999', -9.999999999999999999999999999999],
            ['9.9e3000', INF],
            ['-9.9e3000', -INF],
        ];
    }

    /**
     * @dataProvider providerToString
     */
    function it_creates_a_bigdecimal_from_an_unscaled_value_and_a_scale($unscaledValue, $scale, $expected)
    {
        // let
        $this->beConstructedThroughOfUnscaledValue($unscaledValue, $scale);

        // then
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerToString()
    {
        return [
            ['0', 0, '0'],
            ['0', 1, '0.0'],
            ['1', 1, '0.1'],
            ['0', 2, '0.00'],
            ['1', 2, '0.01'],
            ['10', 2, '0.10'],
            ['11', 2, '0.11'],
            ['11', 3, '0.011'],
            ['1', 0, '1'],
            ['10', 1, '1.0'],
            ['11', 1, '1.1'],
            ['100', 2, '1.00'],
            ['101', 2, '1.01'],
            ['110', 2, '1.10'],
            ['111', 2, '1.11'],
            ['111', 3, '0.111'],
            ['111', 4, '0.0111'],
            ['-1', 1, '-0.1'],
            ['-1', 2, '-0.01'],
            ['-10', 2, '-0.10'],
            ['-11', 2, '-0.11'],
            ['-12', 3, '-0.012'],
            ['-12', 4, '-0.0012'],
            ['-1', 0, '-1'],
            ['-10', 1, '-1.0'],
            ['-12', 1, '-1.2'],
            ['-100', 2, '-1.00'],
            ['-101', 2, '-1.01'],
            ['-120', 2, '-1.20'],
            ['-123', 2, '-1.23'],
            ['-123', 3, '-0.123'],
            ['-123', 4, '-0.0123'],
        ];
    }

    function it_throws_exception_on_a_direct_call_to_unserialize()
    {
        // let
        $this->beConstructedThroughZero();

        // then
        $this->shouldThrow('\LogicException')->during('unserialize', ['123:0']);
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

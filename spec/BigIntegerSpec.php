<?php

namespace spec\Arki\Math;

use Arki\Math\BigDecimal;
use Arki\Math\BigInteger;
use Arki\Math\Exception\NumberFormatException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Arki\Math\RoundingMode;

/**
 * @method void shouldReturn($value)
 * @method void shouldBeAnInstanceOf($class)
 * @method void shouldBeEqualTo($value)
 */
class BigIntegerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Arki\Math\BigInteger');
    }

    function it_is_a_number()
    {
        $this->shouldHaveType('Arki\Math\Number');
    }

    function it_is_serializable()
    {
        $value = '-1234567890987654321012345678909876543210123456789';
        // let
        $this->beConstructedThroughOf($value);
        // and
        $number = unserialize(serialize($this));

        // then
        $this->shouldHaveType('Serializable');
        // and
        $number->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $number->__toString()->shouldReturn($value);
    }

    function it_throws_exception_on_a_direct_call_to_unserialize()
    {
        // let
        $this->beConstructedThroughZero();

        // then
        $this->shouldThrow('\LogicException')->duringUnserialize('123');
    }

    function it_can_not_be_instantiated()
    {
        $this->shouldThrow('\Exception')->duringInstantiation();
    }

    /**
     * @dataProvider validValuesProvider
     */
    public function it_creates_a_BigInteger_of_a_given_value($value, $expected)
    {
        // let
        $this->beConstructedThroughOf($value);

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function validValuesProvider()
    {
        return [
            [0, '0'],
            [1, '1'],
            [-1, '-1'],
            [123456789, '123456789'],
            [-123456789, '-123456789'],
            [PHP_INT_MAX, (string)PHP_INT_MAX],
            [~PHP_INT_MAX, (string)~PHP_INT_MAX],
            [0.0, '0'],
            [1.0, '1'],
            [-0.0, '0'],
            [-1.0, '-1'],
            ['0.0', '0'],
            ['1.0', '1'],
            ['-1.00', '-1'],
            [1.2e5, '120000'],
            [-1.2e5, '-120000'],
            ['1e20', '100000000000000000000'],
            ['-3e+50', '-300000000000000000000000000000000000000000000000000'],
            ['0', '0'],
            ['+0', '0'],
            ['-0', '0'],
            ['1', '1'],
            ['+1', '1'],
            ['-1', '-1'],
            ['00', '0'],
            ['+00', '0'],
            ['-00', '0'],
            ['01', '1'],
            ['+01', '1'],
            ['-01', '-1'],
            ['123456789012345678901234567890', '123456789012345678901234567890'],
            ['+123456789012345678901234567890', '123456789012345678901234567890'],
            ['-123456789012345678901234567890', '-123456789012345678901234567890'],
            ['0123456789012345678901234567890', '123456789012345678901234567890'],
            ['+0123456789012345678901234567890', '123456789012345678901234567890'],
            ['-0123456789012345678901234567890', '-123456789012345678901234567890'],
            ['00123456789012345678901234567890', '123456789012345678901234567890'],
            ['+00123456789012345678901234567890', '123456789012345678901234567890'],
            ['-00123456789012345678901234567890', '-123456789012345678901234567890'],
        ];
    }

    function it_returns_this_if_it_is_created_of_a_BigInteger()
    {
        $integer = BigInteger::of(123);
        $this->beConstructedThroughOf($integer);

        $this->shouldBeEqualTo($integer);
    }

    /**
     * @dataProvider valuesWithInvalidFormatProvider
     */
    function it_throws_exception_when_creating_decimals_of_value_with_invalid_format($value)
    {
        // let
        $this->beConstructedThrough('of', [$value]);
        // then
        $this->shouldThrow(NumberFormatException::class)->duringInstantiation();
    }

    public static function valuesWithInvalidFormatProvider()
    {
        return [
            [''],
            ['a'],
            [' 1'],
            ['1 '],
            ['1.'],
            ['+'],
            ['-'],
            ['+a'],
            ['-a'],
            ['a0'],
            ['0a'],
            ['1.a'],
            ['a.1'],
        ];
    }

    /**
     * @dataProvider nonConvertibleValuesProvider
     */
    function it_throws_exception_when_creating_decimals_of_nonconvertible_value($value)
    {
        // let
        $this->beConstructedThrough('of', [$value]);
        // then
        $this->shouldThrow('\ArithmeticError')->duringInstantiation();
    }

    public static function nonConvertibleValuesProvider()
    {
        return [
            [1.1],
            ['1e-1'],
            ['7/9'],
        ];
    }

    /**
     * @dataProvider valuesToParseProvider
     */
    function it_is_created_by_parsing_numerical_strings($number, $base, $expected)
    {
        // let
        $this->beConstructedThroughParse($number, $base);

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function valuesToParseProvider()
    {
        return [
            ['0', 10, '0'],
            ['-0', 10, '0'],
            ['+0', 10, '0'],
            ['00', 16, '0'],
            ['-00', 16, '0'],
            ['+00', 16, '0'],
            ['1', 10, '1'],
            ['-1', 10, '-1'],
            ['+1', 10, '1'],
            ['01', 8, '1'],
            ['-01', 8, '-1'],
            ['+01', 8, '1'],
            ['123', 10, '123'],
            ['+456', 10, '456'],
            ['-789', 10, '-789'],
            ['0123', 10, '123'],
            ['+0456', 10, '456'],
            ['-0789', 10, '-789'],
            ['110011001100110011001111', 36, '640998479760579495168036691627608949'],
            ['110011001100110011001111', 35, '335582856048758779730579523833856636'],
            ['110011001100110011001111', 34, '172426711023004493064981145981549295'],
            ['110011001100110011001111', 33, '86853227285668653965326574185738990'],
            ['110011001100110011001111', 32, '42836489934972583913564073319498785'],
            ['110011001100110011001111', 31, '20658924711984480538771889603666144'],
            ['110011001100110011001111', 30, '9728140488839986222205212599027931'],
            ['110011001100110011001111', 29, '4465579470019956787945275674107410'],
            ['110011001100110011001111', 28, '1994689924537781753408144504465645'],
            ['110011001100110011001111', 27, '865289950909412968716094193925700'],
            ['110011001100110011001111', 26, '363729369583879309352831568000039'],
            ['110011001100110011001111', 25, '147793267388865354156500488297526'],
            ['110011001100110011001111', 24, '57888012016107577099138793486425'],
            ['110011001100110011001111', 23, '21788392294523974761749372677800'],
            ['110011001100110011001111', 22, '7852874701996329566765721637715'],
            ['110011001100110011001111', 21, '2699289081943123258094476428634'],
            ['110011001100110011001111', 20, '880809345058406615041344008421'],
            ['110011001100110011001111', 19, '271401690926468032718781859340'],
            ['110011001100110011001111', 18, '78478889737009209699633503455'],
            ['110011001100110011001111', 17, '21142384915931646646976872830'],
            ['110011001100110011001111', 16, '5261325448418072742917574929'],
            ['110011001100110011001111', 15, '1197116069565850925807253616'],
            ['110011001100110011001111', 14, '245991074299834917455374155'],
            ['110011001100110011001111', 13, '44967318722190498361960610'],
            ['110011001100110011001111', 12, '7177144825886069940574045'],
            ['110011001100110011001111', 11, '976899716207148313491924'],
            ['110011001100110011001111', 10, '110011001100110011001111'],
            ['110011001100110011001111', 9, '9849210196991880028870'],
            ['110011001100110011001111', 8, '664244955832213832265'],
            ['110011001100110011001111', 7, '31291601125492514360'],
            ['110011001100110011001111', 6, '922063395565287619'],
            ['110011001100110011001111', 5, '14328039609468906'],
            ['110011001100110011001111', 4, '88305875046485'],
            ['110011001100110011001111', 3, '127093291420'],
            ['110011001100110011001111', 2, '13421775'],
            ['ZyXwVuTsRqPoNmLkJiHgFeDcBa9876543210', 36, '106300512100105327644605138221229898724869759421181854980'],
            ['YxWvUtSrQpOnMlKjIhGfEdCbA9876543210', 35, '1101553773143634726491620528194292510495517905608180485'],
            ['XwVuTsRqPoNmLkJiHgFeDcBa9876543210', 34, '11745843093701610854378775891116314824081102660800418'],
            ['WvUtSrQpOnMlKjIhGfEdCbA9876543210', 33, '128983956064237823710866404905431464703849549412368'],
            ['VuTsRqPoNmLkJiHgFeDcBa9876543210', 32, '1459980823972598128486511383358617792788444579872'],
            ['UtSrQpOnMlKjIhGfEdCbA9876543210', 31, '17050208381689099029767742314582582184093573615'],
            ['TsRqPoNmLkJiHgFeDcBa9876543210', 30, '205646315052919334126040428061831153388822830'],
            ['SrQpOnMlKjIhGfEdCbA9876543210', 29, '2564411043271974895869785066497940850811934'],
            ['RqPoNmLkJiHgFeDcBa9876543210', 28, '33100056003358651440264672384704297711484'],
            ['QpOnMlKjIhGfEdCbA9876543210', 27, '442770531899482980347734468443677777577'],
            ['PoNmLkJiHgFeDcBa9876543210', 26, '6146269788878825859099399609538763450'],
            ['OnMlKjIhGfEdCbA9876543210', 25, '88663644327703473714387251271141900'],
            ['NmLkJiHgFeDcBa9876543210', 24, '1331214537196502869015340298036888'],
            ['MlKjIhGfEdCbA9876543210', 23, '20837326537038308910317109288851'],
            ['LkJiHgFeDcBa9876543210', 22, '340653664490377789692799452102'],
            ['KjIhGfEdCbA9876543210', 21, '5827980550840017565077671610'],
            ['JiHgFeDcBa9876543210', 20, '104567135734072022160664820'],
            ['IhGfEdCbA9876543210', 19, '1972313422155189164466189'],
            ['HgFeDcBa9876543210', 18, '39210261334551566857170'],
            ['GfEdCbA9876543210', 17, '824008854613343261192'],
            ['FeDcBa9876543210', 16, '18364758544493064720'],
            ['EdCbA9876543210', 15, '435659737878916215'],
            ['DcBa9876543210', 14, '11046255305880158'],
            ['CbA9876543210', 13, '300771807240918'],
            ['Ba9876543210', 12, '8842413667692'],
            ['A9876543210', 11, '282458553905'],
            ['9876543210', 10, '9876543210'],
            ['876543210', 9, '381367044'],
            ['76543210', 8, '16434824'],
            ['6543210', 7, '800667'],
            ['543210', 6, '44790'],
            ['43210', 5, '2930'],
            ['3210', 4, '228'],
            ['210', 3, '21'],
            ['10', 2, '2'],
        ];
    }

    /**
     * @dataProvider invalidValuesToBeParsedProvider
     */
    function it_throws_exception_when_parses_invalid_values($value, $base)
    {
        // let
        $this->beConstructedThrough('parse', [$value, $base]);
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public static function invalidValuesToBeParsedProvider()
    {
        return [
            ['', 10],
            [' ', 10],
            ['+', 10],
            ['-', 10],
            ['1 ', 10],
            [' 1', 10],
            ['Z', 35],
            ['y', 34],
            ['X', 33],
            ['w', 32],
            ['V', 31],
            ['u', 30],
            ['T', 29],
            ['s', 28],
            ['R', 27],
            ['q', 26],
            ['P', 25],
            ['o', 24],
            ['N', 23],
            ['m', 22],
            ['L', 21],
            ['k', 20],
            ['J', 19],
            ['i', 18],
            ['H', 17],
            ['g', 16],
            ['F', 15],
            ['e', 14],
            ['D', 13],
            ['c', 12],
            ['B', 11],
            ['a', 10],
            ['9', 9],
            ['8', 8],
            ['7', 7],
            ['6', 6],
            ['5', 5],
            ['4', 4],
            ['3', 3],
            ['2', 2],
        ];
    }

    /**
     * @dataProvider invalidBasesProvider
     */
    function it_throws_exception_when_parsing_with_invalid_base($base)
    {
        // let
        $this->beConstructedThrough('parse', ['0', $base]);
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    public static function invalidBasesProvider()
    {
        return [
            [-2],
            [-1],
            [0],
            [1],
            [37],
        ];
    }

    function it_creates_the_integer_zero()
    {
        // let
        $this->beConstructedThrough('zero');

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo('0');
        // and
        $this->shouldBeEqualTo(BigInteger::zero());
    }

    function it_creates_the_integer_one()
    {
        // let
        $this->beConstructedThrough('one');

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo('1');
        // and
        $this->shouldBeEqualTo(BigInteger::one());
    }

    function it_creates_the_integer_ten()
    {
        // let
        $this->beConstructedThrough('ten');

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo('10');
        // and
        $this->shouldBeEqualTo(BigInteger::ten());
    }

    /**
     * @dataProvider minimumProvider
     */
    function it_returns_the_minimum_of_the_given_values($values, $min)
    {
        // let
        $this->beConstructedThroughMin(... $values);

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($min);
    }

    public static function minimumProvider()
    {
        return [
            [[0, 1, -1], '-1'],
            [[0, '10', '5989'], '0'],
            [[0, '10', '5989', '-1.00'], '-1'],
            [['-2/2', '1'], '-1'],
            [['-1.0', '1', '2', '-300/4', '-100'], '-100'],
            [['999999999999999999999999999', '1000000000000000000000000000'], '999999999999999999999999999'],
            [['-999999999999999999999999999', '-1000000000000000000000000000'], '-1000000000000000000000000000'],
        ];
    }

    function it_throws_exception_when_getting_minimum_of_no_values()
    {
        // let
        $this->beConstructedThroughMin();
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_exception_when_getting_minimum_of_non_integer_values()
    {
        // let
        $this->beConstructedThroughMin(1, 1.2);
        // then
        $this->shouldThrow('\ArithmeticError')->duringInstantiation();
    }

    /**
     * @dataProvider maximumProvider
     */
    function it_returns_the_maximum_of_the_given_values($values, $max)
    {
        // let
        $this->beConstructedThroughMax(... $values);

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($max);
    }

    public static function maximumProvider()
    {
        return [
            [[0, 1, -1], '1'],
            [[0, '10', '5989.0'], '5989'],
            [[0, '10', '5989', '-1'], '5989'],
            [[0, '10', '5989', '-1', 6000.0], '6000'],
            [['-1', '0'], '0'],
            [['-1', '1', '2', '27/9', '-100'], '3'],
            [['999999999999999999999999999', '1000000000000000000000000000'], '1000000000000000000000000000'],
            [['-999999999999999999999999999', '-1000000000000000000000000000'], '-999999999999999999999999999'],
        ];
    }

    function it_throws_exception_when_getting_maximum_of_no_values()
    {
        // let
        $this->beConstructedThroughMax();
        // then
        $this->shouldThrow('\InvalidArgumentException')->duringInstantiation();
    }

    function it_throws_exception_when_getting_maximum_of_non_integer_values()
    {
        // let
        $this->beConstructedThroughMax(1, 1.2);
        // then
        $this->shouldThrow('\ArithmeticError')->duringInstantiation();
    }

    /**
     * @dataProvider providerPlus
     */
    function it_calculates_the_sum_with_a_given_number($a, $b, $r)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b) {
                return BigInteger::of($a)->plus($b);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($r);
    }

    public static function providerPlus()
    {
        return [
            ['5165450198704521651351654564564089798441', '0', '5165450198704521651351654564564089798441'],
            ['-5165450198704521651351654564564089798441', '0', '-5165450198704521651351654564564089798441'],
            ['5165450198704521651351654564564089798441', '-5165450198704521651351654564564089798441', '0'],
            ['-5165450198704521651351654564564089798441', '5165450198704521651351654564564089798441', '0'],
            ['3493049309220392055810', '9918493493849898938928310121', '9918496986899208159320365931'],
            ['546254089287665464650654', '-4654654565726542654005465', '-4108400476438877189354811'],
            ['-54654654625426504062224', '406546504670332465465435004', '406491850015707038961372780'],
            ['-78706406576549688403246', '-3064672987984605465406546', '-3143379394561155153809792'],
        ];
    }

    /**
     * @dataProvider providerMinus
     */
    function it_calculates_the_difference_with_a_given_number($a, $b, $r)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b) {
                return BigInteger::of($a)->minus($b);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($r);
    }

    public static function providerMinus()
    {
        return [
            ['5165450198704521651351654564564089798441', '0', '5165450198704521651351654564564089798441'],
            ['-5165450198704521651351654564564089798441', '0', '-5165450198704521651351654564564089798441'],
            ['0', '5165450198704521651351654564564089798441', '-5165450198704521651351654564564089798441'],
            ['0', '-5165450198704521651351654564564089798441', '5165450198704521651351654564564089798441'],
            ['879798276565798787646', '2345178709879804654605406456', '-2345177830081528088806618810'],
            ['99465465545004066406868767', '-79870987954654608076067608768', '79970453420199612142474477535'],
            ['-46465465478979879230745664', '21316504468760001807687078994', '-21362969934238981686917824658'],
            ['-2154799048440940949896046', '-9000454956465465424345404846624', '9000452801666416983404454950578'],
        ];
    }

    /**
     * @dataProvider providerMultipliedBy
     */
    function it_multiplies_this_by_a_given_number($a, $b, $r)
    {
        // let
        $this->beConstructedThrough(
            function () use ($a, $b) {
                return BigInteger::of($a)->multipliedBy($b);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($r);
    }

    public static function providerMultipliedBy()
    {
        return [
            ['123456789098765432101234567890987654321', '1', '123456789098765432101234567890987654321'],
            ['123456789098765432101234567890987654321', '-1', '-123456789098765432101234567890987654321'],
            ['15892588375910581333', '2485910409339228962451', '39507550875019745254366764864945838527183'],
            ['341581435989834012309', '-91050393818389238433', '-31101124267925302088072082300643257871797'],
            ['-1204902920503999920003', '1984389583950290232332', '-2390996805119422027350037939263960284136996'],
            ['-991230349304902390122', '-3483910549230593053437', '3453357870660875087266990729629471366949314'],
            ['1274837942798479387498237897498734984', 30, '38245138283954381624947136924962049520'],
            ['1274837942798479387498237897498734984', 30.0, '38245138283954381624947136924962049520'],
            ['1274837942798479387498237897498734984', '30', '38245138283954381624947136924962049520'],
            ['1274837942798479387498237897498734984', '30.0', '38245138283954381624947136924962049520'],
            ['1274837942798479387498237897498734984', '90/3', '38245138283954381624947136924962049520'],
        ];
    }

    /**
     * @dataProvider providerDividedBy
     */
    function it_can_be_divided_by_a_given_number($number, $divisor, $expected)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        if (self::isException($expected)) {
            $this->shouldThrow($expected)->during('dividedBy', [$divisor]);
        } else {
            $d = $this->dividedBy($divisor);

            $d->shouldBeAnInstanceOf(BigInteger::class);
            // and
            $d->__toString()->shouldBeEqualTo($expected);
        }
    }

    public static function providerDividedBy()
    {
        return [
            ['123456789098765432101234567890987654321', 1, '123456789098765432101234567890987654321'],
            ['123456789098765432101234567890987654321', 2, '\ArithmeticError'],
            ['123456789098765432101234567890987654321', 0, '\DivisionByZeroError'],
            ['123456789098765432101234567890987654321', 0.0, '\DivisionByZeroError'],
            ['123456789098765432101234567890987654321', 0.1, '\ArithmeticError'],
            ['123456789098765432101234567890987654322', 2, '61728394549382716050617283945493827161'],
            ['123456789098765432101234567890987654322', 2.0, '61728394549382716050617283945493827161'],
            ['123456789098765432101234567890987654322', '2', '61728394549382716050617283945493827161'],
            ['123456789098765432101234567890987654322', '2.0', '61728394549382716050617283945493827161'],
            ['123456789098765432101234567890987654322', '14/7', '61728394549382716050617283945493827161'],
            ['61728394549382716050617283945493827161', '0.5', '\ArithmeticError'],
            ['61728394549382716050617283945493827161', '1/2', '\ArithmeticError'],
        ];
    }

    function it_throws_exception_when_divided_with_an_invalid_rounding_mode()
    {
        // let
        $this->beConstructedThroughOf(1);

        // then
        $this->shouldThrow('\InvalidArgumentException')->duringDividedBy(2, -1);
    }

    /**
     * @dataProvider providerDividedByWithRoundingMode
     */
    function it_can_be_divided_by_a_given_number_and_with_a_given_rounding_mode(
        $roundingMode,
        $number,
        $ten,
        $hundred,
        $thousand
    ) {
        // let
        $this->beConstructedThroughOf($number);

        $divisor = '1';
        foreach ([$ten, $hundred, $thousand] as $expected) {
            $divisor .= '0';
            $negated = $this->negate();

            if ($expected === null) {
                $this->shouldThrow('\ArithmeticError')->during('dividedBy', [$divisor, $roundingMode]);
                $negated->shouldThrow('\ArithmeticError')->during('dividedBy', ['-'.$divisor, $roundingMode]);

                continue;
            }

            $actual = $this->dividedBy($divisor, $roundingMode);
            $actual->shouldBeAnInstanceOf(BigInteger::class);
            $actual->__toString()->shouldBeEqualTo($expected);

            $negatedActual = $this->negate()->dividedBy('-'.$divisor, $roundingMode);
            $negatedActual->shouldBeAnInstanceOf(BigInteger::class);
            $negatedActual->__toString()->shouldBeEqualTo($expected);
        }
    }

    public static function providerDividedByWithRoundingMode()
    {
        return [
            [RoundingMode::UP, '3501', '351', '36', '4'],
            [RoundingMode::UP, '3500', '350', '35', '4'],
            [RoundingMode::UP, '3499', '350', '35', '4'],
            [RoundingMode::UP, '3001', '301', '31', '4'],
            [RoundingMode::UP, '3000', '300', '30', '3'],
            [RoundingMode::UP, '2999', '300', '30', '3'],
            [RoundingMode::UP, '2501', '251', '26', '3'],
            [RoundingMode::UP, '2500', '250', '25', '3'],
            [RoundingMode::UP, '2499', '250', '25', '3'],
            [RoundingMode::UP, '2001', '201', '21', '3'],
            [RoundingMode::UP, '2000', '200', '20', '2'],
            [RoundingMode::UP, '1999', '200', '20', '2'],
            [RoundingMode::UP, '1501', '151', '16', '2'],
            [RoundingMode::UP, '1500', '150', '15', '2'],
            [RoundingMode::UP, '1499', '150', '15', '2'],
            [RoundingMode::UP, '1001', '101', '11', '2'],
            [RoundingMode::UP, '1000', '100', '10', '1'],
            [RoundingMode::UP, '999', '100', '10', '1'],
            [RoundingMode::UP, '501', '51', '6', '1'],
            [RoundingMode::UP, '500', '50', '5', '1'],
            [RoundingMode::UP, '499', '50', '5', '1'],
            [RoundingMode::UP, '1', '1', '1', '1'],
            [RoundingMode::UP, '0', '0', '0', '0'],
            [RoundingMode::UP, '-1', '-1', '-1', '-1'],
            [RoundingMode::UP, '-499', '-50', '-5', '-1'],
            [RoundingMode::UP, '-500', '-50', '-5', '-1'],
            [RoundingMode::UP, '-501', '-51', '-6', '-1'],
            [RoundingMode::UP, '-999', '-100', '-10', '-1'],
            [RoundingMode::UP, '-1000', '-100', '-10', '-1'],
            [RoundingMode::UP, '-1001', '-101', '-11', '-2'],
            [RoundingMode::UP, '-1499', '-150', '-15', '-2'],
            [RoundingMode::UP, '-1500', '-150', '-15', '-2'],
            [RoundingMode::UP, '-1501', '-151', '-16', '-2'],
            [RoundingMode::UP, '-1999', '-200', '-20', '-2'],
            [RoundingMode::UP, '-2000', '-200', '-20', '-2'],
            [RoundingMode::UP, '-2001', '-201', '-21', '-3'],
            [RoundingMode::UP, '-2499', '-250', '-25', '-3'],
            [RoundingMode::UP, '-2500', '-250', '-25', '-3'],
            [RoundingMode::UP, '-2501', '-251', '-26', '-3'],
            [RoundingMode::UP, '-2999', '-300', '-30', '-3'],
            [RoundingMode::UP, '-3000', '-300', '-30', '-3'],
            [RoundingMode::UP, '-3001', '-301', '-31', '-4'],
            [RoundingMode::UP, '-3499', '-350', '-35', '-4'],
            [RoundingMode::UP, '-3500', '-350', '-35', '-4'],
            [RoundingMode::UP, '-3501', '-351', '-36', '-4'],
            [RoundingMode::DOWN, '3501', '350', '35', '3'],
            [RoundingMode::DOWN, '3500', '350', '35', '3'],
            [RoundingMode::DOWN, '3499', '349', '34', '3'],
            [RoundingMode::DOWN, '3001', '300', '30', '3'],
            [RoundingMode::DOWN, '3000', '300', '30', '3'],
            [RoundingMode::DOWN, '2999', '299', '29', '2'],
            [RoundingMode::DOWN, '2501', '250', '25', '2'],
            [RoundingMode::DOWN, '2500', '250', '25', '2'],
            [RoundingMode::DOWN, '2499', '249', '24', '2'],
            [RoundingMode::DOWN, '2001', '200', '20', '2'],
            [RoundingMode::DOWN, '2000', '200', '20', '2'],
            [RoundingMode::DOWN, '1999', '199', '19', '1'],
            [RoundingMode::DOWN, '1501', '150', '15', '1'],
            [RoundingMode::DOWN, '1500', '150', '15', '1'],
            [RoundingMode::DOWN, '1499', '149', '14', '1'],
            [RoundingMode::DOWN, '1001', '100', '10', '1'],
            [RoundingMode::DOWN, '1000', '100', '10', '1'],
            [RoundingMode::DOWN, '999', '99', '9', '0'],
            [RoundingMode::DOWN, '501', '50', '5', '0'],
            [RoundingMode::DOWN, '500', '50', '5', '0'],
            [RoundingMode::DOWN, '499', '49', '4', '0'],
            [RoundingMode::DOWN, '1', '0', '0', '0'],
            [RoundingMode::DOWN, '0', '0', '0', '0'],
            [RoundingMode::DOWN, '-1', '0', '0', '0'],
            [RoundingMode::DOWN, '-499', '-49', '-4', '0'],
            [RoundingMode::DOWN, '-500', '-50', '-5', '0'],
            [RoundingMode::DOWN, '-501', '-50', '-5', '0'],
            [RoundingMode::DOWN, '-999', '-99', '-9', '0'],
            [RoundingMode::DOWN, '-1000', '-100', '-10', '-1'],
            [RoundingMode::DOWN, '-1001', '-100', '-10', '-1'],
            [RoundingMode::DOWN, '-1499', '-149', '-14', '-1'],
            [RoundingMode::DOWN, '-1500', '-150', '-15', '-1'],
            [RoundingMode::DOWN, '-1501', '-150', '-15', '-1'],
            [RoundingMode::DOWN, '-1999', '-199', '-19', '-1'],
            [RoundingMode::DOWN, '-2000', '-200', '-20', '-2'],
            [RoundingMode::DOWN, '-2001', '-200', '-20', '-2'],
            [RoundingMode::DOWN, '-2499', '-249', '-24', '-2'],
            [RoundingMode::DOWN, '-2500', '-250', '-25', '-2'],
            [RoundingMode::DOWN, '-2501', '-250', '-25', '-2'],
            [RoundingMode::DOWN, '-2999', '-299', '-29', '-2'],
            [RoundingMode::DOWN, '-3000', '-300', '-30', '-3'],
            [RoundingMode::DOWN, '-3001', '-300', '-30', '-3'],
            [RoundingMode::DOWN, '-3499', '-349', '-34', '-3'],
            [RoundingMode::DOWN, '-3500', '-350', '-35', '-3'],
            [RoundingMode::DOWN, '-3501', '-350', '-35', '-3'],
            [RoundingMode::CEILING, '3501', '351', '36', '4'],
            [RoundingMode::CEILING, '3500', '350', '35', '4'],
            [RoundingMode::CEILING, '3499', '350', '35', '4'],
            [RoundingMode::CEILING, '3001', '301', '31', '4'],
            [RoundingMode::CEILING, '3000', '300', '30', '3'],
            [RoundingMode::CEILING, '2999', '300', '30', '3'],
            [RoundingMode::CEILING, '2501', '251', '26', '3'],
            [RoundingMode::CEILING, '2500', '250', '25', '3'],
            [RoundingMode::CEILING, '2499', '250', '25', '3'],
            [RoundingMode::CEILING, '2001', '201', '21', '3'],
            [RoundingMode::CEILING, '2000', '200', '20', '2'],
            [RoundingMode::CEILING, '1999', '200', '20', '2'],
            [RoundingMode::CEILING, '1501', '151', '16', '2'],
            [RoundingMode::CEILING, '1500', '150', '15', '2'],
            [RoundingMode::CEILING, '1499', '150', '15', '2'],
            [RoundingMode::CEILING, '1001', '101', '11', '2'],
            [RoundingMode::CEILING, '1000', '100', '10', '1'],
            [RoundingMode::CEILING, '999', '100', '10', '1'],
            [RoundingMode::CEILING, '501', '51', '6', '1'],
            [RoundingMode::CEILING, '500', '50', '5', '1'],
            [RoundingMode::CEILING, '499', '50', '5', '1'],
            [RoundingMode::CEILING, '1', '1', '1', '1'],
            [RoundingMode::CEILING, '0', '0', '0', '0'],
            [RoundingMode::CEILING, '-1', '0', '0', '0'],
            [RoundingMode::CEILING, '-499', '-49', '-4', '0'],
            [RoundingMode::CEILING, '-500', '-50', '-5', '0'],
            [RoundingMode::CEILING, '-501', '-50', '-5', '0'],
            [RoundingMode::CEILING, '-999', '-99', '-9', '0'],
            [RoundingMode::CEILING, '-1000', '-100', '-10', '-1'],
            [RoundingMode::CEILING, '-1001', '-100', '-10', '-1'],
            [RoundingMode::CEILING, '-1499', '-149', '-14', '-1'],
            [RoundingMode::CEILING, '-1500', '-150', '-15', '-1'],
            [RoundingMode::CEILING, '-1501', '-150', '-15', '-1'],
            [RoundingMode::CEILING, '-1999', '-199', '-19', '-1'],
            [RoundingMode::CEILING, '-2000', '-200', '-20', '-2'],
            [RoundingMode::CEILING, '-2001', '-200', '-20', '-2'],
            [RoundingMode::CEILING, '-2499', '-249', '-24', '-2'],
            [RoundingMode::CEILING, '-2500', '-250', '-25', '-2'],
            [RoundingMode::CEILING, '-2501', '-250', '-25', '-2'],
            [RoundingMode::CEILING, '-2999', '-299', '-29', '-2'],
            [RoundingMode::CEILING, '-3000', '-300', '-30', '-3'],
            [RoundingMode::CEILING, '-3001', '-300', '-30', '-3'],
            [RoundingMode::CEILING, '-3499', '-349', '-34', '-3'],
            [RoundingMode::CEILING, '-3500', '-350', '-35', '-3'],
            [RoundingMode::CEILING, '-3501', '-350', '-35', '-3'],
            [RoundingMode::FLOOR, '3501', '350', '35', '3'],
            [RoundingMode::FLOOR, '3500', '350', '35', '3'],
            [RoundingMode::FLOOR, '3499', '349', '34', '3'],
            [RoundingMode::FLOOR, '3001', '300', '30', '3'],
            [RoundingMode::FLOOR, '3000', '300', '30', '3'],
            [RoundingMode::FLOOR, '2999', '299', '29', '2'],
            [RoundingMode::FLOOR, '2501', '250', '25', '2'],
            [RoundingMode::FLOOR, '2500', '250', '25', '2'],
            [RoundingMode::FLOOR, '2499', '249', '24', '2'],
            [RoundingMode::FLOOR, '2001', '200', '20', '2'],
            [RoundingMode::FLOOR, '2000', '200', '20', '2'],
            [RoundingMode::FLOOR, '1999', '199', '19', '1'],
            [RoundingMode::FLOOR, '1501', '150', '15', '1'],
            [RoundingMode::FLOOR, '1500', '150', '15', '1'],
            [RoundingMode::FLOOR, '1499', '149', '14', '1'],
            [RoundingMode::FLOOR, '1001', '100', '10', '1'],
            [RoundingMode::FLOOR, '1000', '100', '10', '1'],
            [RoundingMode::FLOOR, '999', '99', '9', '0'],
            [RoundingMode::FLOOR, '501', '50', '5', '0'],
            [RoundingMode::FLOOR, '500', '50', '5', '0'],
            [RoundingMode::FLOOR, '499', '49', '4', '0'],
            [RoundingMode::FLOOR, '1', '0', '0', '0'],
            [RoundingMode::FLOOR, '0', '0', '0', '0'],
            [RoundingMode::FLOOR, '-1', '-1', '-1', '-1'],
            [RoundingMode::FLOOR, '-499', '-50', '-5', '-1'],
            [RoundingMode::FLOOR, '-500', '-50', '-5', '-1'],
            [RoundingMode::FLOOR, '-501', '-51', '-6', '-1'],
            [RoundingMode::FLOOR, '-999', '-100', '-10', '-1'],
            [RoundingMode::FLOOR, '-1000', '-100', '-10', '-1'],
            [RoundingMode::FLOOR, '-1001', '-101', '-11', '-2'],
            [RoundingMode::FLOOR, '-1499', '-150', '-15', '-2'],
            [RoundingMode::FLOOR, '-1500', '-150', '-15', '-2'],
            [RoundingMode::FLOOR, '-1501', '-151', '-16', '-2'],
            [RoundingMode::FLOOR, '-1999', '-200', '-20', '-2'],
            [RoundingMode::FLOOR, '-2000', '-200', '-20', '-2'],
            [RoundingMode::FLOOR, '-2001', '-201', '-21', '-3'],
            [RoundingMode::FLOOR, '-2499', '-250', '-25', '-3'],
            [RoundingMode::FLOOR, '-2500', '-250', '-25', '-3'],
            [RoundingMode::FLOOR, '-2501', '-251', '-26', '-3'],
            [RoundingMode::FLOOR, '-2999', '-300', '-30', '-3'],
            [RoundingMode::FLOOR, '-3000', '-300', '-30', '-3'],
            [RoundingMode::FLOOR, '-3001', '-301', '-31', '-4'],
            [RoundingMode::FLOOR, '-3499', '-350', '-35', '-4'],
            [RoundingMode::FLOOR, '-3500', '-350', '-35', '-4'],
            [RoundingMode::FLOOR, '-3501', '-351', '-36', '-4'],
            [RoundingMode::HALF_UP, '3501', '350', '35', '4'],
            [RoundingMode::HALF_UP, '3500', '350', '35', '4'],
            [RoundingMode::HALF_UP, '3499', '350', '35', '3'],
            [RoundingMode::HALF_UP, '3001', '300', '30', '3'],
            [RoundingMode::HALF_UP, '3000', '300', '30', '3'],
            [RoundingMode::HALF_UP, '2999', '300', '30', '3'],
            [RoundingMode::HALF_UP, '2501', '250', '25', '3'],
            [RoundingMode::HALF_UP, '2500', '250', '25', '3'],
            [RoundingMode::HALF_UP, '2499', '250', '25', '2'],
            [RoundingMode::HALF_UP, '2001', '200', '20', '2'],
            [RoundingMode::HALF_UP, '2000', '200', '20', '2'],
            [RoundingMode::HALF_UP, '1999', '200', '20', '2'],
            [RoundingMode::HALF_UP, '1501', '150', '15', '2'],
            [RoundingMode::HALF_UP, '1500', '150', '15', '2'],
            [RoundingMode::HALF_UP, '1499', '150', '15', '1'],
            [RoundingMode::HALF_UP, '1001', '100', '10', '1'],
            [RoundingMode::HALF_UP, '1000', '100', '10', '1'],
            [RoundingMode::HALF_UP, '999', '100', '10', '1'],
            [RoundingMode::HALF_UP, '501', '50', '5', '1'],
            [RoundingMode::HALF_UP, '500', '50', '5', '1'],
            [RoundingMode::HALF_UP, '499', '50', '5', '0'],
            [RoundingMode::HALF_UP, '1', '0', '0', '0'],
            [RoundingMode::HALF_UP, '0', '0', '0', '0'],
            [RoundingMode::HALF_UP, '-1', '0', '0', '0'],
            [RoundingMode::HALF_UP, '-499', '-50', '-5', '0'],
            [RoundingMode::HALF_UP, '-500', '-50', '-5', '-1'],
            [RoundingMode::HALF_UP, '-501', '-50', '-5', '-1'],
            [RoundingMode::HALF_UP, '-999', '-100', '-10', '-1'],
            [RoundingMode::HALF_UP, '-1000', '-100', '-10', '-1'],
            [RoundingMode::HALF_UP, '-1001', '-100', '-10', '-1'],
            [RoundingMode::HALF_UP, '-1499', '-150', '-15', '-1'],
            [RoundingMode::HALF_UP, '-1500', '-150', '-15', '-2'],
            [RoundingMode::HALF_UP, '-1501', '-150', '-15', '-2'],
            [RoundingMode::HALF_UP, '-1999', '-200', '-20', '-2'],
            [RoundingMode::HALF_UP, '-2000', '-200', '-20', '-2'],
            [RoundingMode::HALF_UP, '-2001', '-200', '-20', '-2'],
            [RoundingMode::HALF_UP, '-2499', '-250', '-25', '-2'],
            [RoundingMode::HALF_UP, '-2500', '-250', '-25', '-3'],
            [RoundingMode::HALF_UP, '-2501', '-250', '-25', '-3'],
            [RoundingMode::HALF_UP, '-2999', '-300', '-30', '-3'],
            [RoundingMode::HALF_UP, '-3000', '-300', '-30', '-3'],
            [RoundingMode::HALF_UP, '-3001', '-300', '-30', '-3'],
            [RoundingMode::HALF_UP, '-3499', '-350', '-35', '-3'],
            [RoundingMode::HALF_UP, '-3500', '-350', '-35', '-4'],
            [RoundingMode::HALF_UP, '-3501', '-350', '-35', '-4'],
            [RoundingMode::HALF_DOWN, '3501', '350', '35', '4'],
            [RoundingMode::HALF_DOWN, '3500', '350', '35', '3'],
            [RoundingMode::HALF_DOWN, '3499', '350', '35', '3'],
            [RoundingMode::HALF_DOWN, '3001', '300', '30', '3'],
            [RoundingMode::HALF_DOWN, '3000', '300', '30', '3'],
            [RoundingMode::HALF_DOWN, '2999', '300', '30', '3'],
            [RoundingMode::HALF_DOWN, '2501', '250', '25', '3'],
            [RoundingMode::HALF_DOWN, '2500', '250', '25', '2'],
            [RoundingMode::HALF_DOWN, '2499', '250', '25', '2'],
            [RoundingMode::HALF_DOWN, '2001', '200', '20', '2'],
            [RoundingMode::HALF_DOWN, '2000', '200', '20', '2'],
            [RoundingMode::HALF_DOWN, '1999', '200', '20', '2'],
            [RoundingMode::HALF_DOWN, '1501', '150', '15', '2'],
            [RoundingMode::HALF_DOWN, '1500', '150', '15', '1'],
            [RoundingMode::HALF_DOWN, '1499', '150', '15', '1'],
            [RoundingMode::HALF_DOWN, '1001', '100', '10', '1'],
            [RoundingMode::HALF_DOWN, '1000', '100', '10', '1'],
            [RoundingMode::HALF_DOWN, '999', '100', '10', '1'],
            [RoundingMode::HALF_DOWN, '501', '50', '5', '1'],
            [RoundingMode::HALF_DOWN, '500', '50', '5', '0'],
            [RoundingMode::HALF_DOWN, '499', '50', '5', '0'],
            [RoundingMode::HALF_DOWN, '1', '0', '0', '0'],
            [RoundingMode::HALF_DOWN, '0', '0', '0', '0'],
            [RoundingMode::HALF_DOWN, '-1', '0', '0', '0'],
            [RoundingMode::HALF_DOWN, '-499', '-50', '-5', '0'],
            [RoundingMode::HALF_DOWN, '-500', '-50', '-5', '0'],
            [RoundingMode::HALF_DOWN, '-501', '-50', '-5', '-1'],
            [RoundingMode::HALF_DOWN, '-999', '-100', '-10', '-1'],
            [RoundingMode::HALF_DOWN, '-1000', '-100', '-10', '-1'],
            [RoundingMode::HALF_DOWN, '-1001', '-100', '-10', '-1'],
            [RoundingMode::HALF_DOWN, '-1499', '-150', '-15', '-1'],
            [RoundingMode::HALF_DOWN, '-1500', '-150', '-15', '-1'],
            [RoundingMode::HALF_DOWN, '-1501', '-150', '-15', '-2'],
            [RoundingMode::HALF_DOWN, '-1999', '-200', '-20', '-2'],
            [RoundingMode::HALF_DOWN, '-2000', '-200', '-20', '-2'],
            [RoundingMode::HALF_DOWN, '-2001', '-200', '-20', '-2'],
            [RoundingMode::HALF_DOWN, '-2499', '-250', '-25', '-2'],
            [RoundingMode::HALF_DOWN, '-2500', '-250', '-25', '-2'],
            [RoundingMode::HALF_DOWN, '-2501', '-250', '-25', '-3'],
            [RoundingMode::HALF_DOWN, '-2999', '-300', '-30', '-3'],
            [RoundingMode::HALF_DOWN, '-3000', '-300', '-30', '-3'],
            [RoundingMode::HALF_DOWN, '-3001', '-300', '-30', '-3'],
            [RoundingMode::HALF_DOWN, '-3499', '-350', '-35', '-3'],
            [RoundingMode::HALF_DOWN, '-3500', '-350', '-35', '-3'],
            [RoundingMode::HALF_DOWN, '-3501', '-350', '-35', '-4'],
            [RoundingMode::HALF_CEILING, '3501', '350', '35', '4'],
            [RoundingMode::HALF_CEILING, '3500', '350', '35', '4'],
            [RoundingMode::HALF_CEILING, '3499', '350', '35', '3'],
            [RoundingMode::HALF_CEILING, '3001', '300', '30', '3'],
            [RoundingMode::HALF_CEILING, '3000', '300', '30', '3'],
            [RoundingMode::HALF_CEILING, '2999', '300', '30', '3'],
            [RoundingMode::HALF_CEILING, '2501', '250', '25', '3'],
            [RoundingMode::HALF_CEILING, '2500', '250', '25', '3'],
            [RoundingMode::HALF_CEILING, '2499', '250', '25', '2'],
            [RoundingMode::HALF_CEILING, '2001', '200', '20', '2'],
            [RoundingMode::HALF_CEILING, '2000', '200', '20', '2'],
            [RoundingMode::HALF_CEILING, '1999', '200', '20', '2'],
            [RoundingMode::HALF_CEILING, '1501', '150', '15', '2'],
            [RoundingMode::HALF_CEILING, '1500', '150', '15', '2'],
            [RoundingMode::HALF_CEILING, '1499', '150', '15', '1'],
            [RoundingMode::HALF_CEILING, '1001', '100', '10', '1'],
            [RoundingMode::HALF_CEILING, '1000', '100', '10', '1'],
            [RoundingMode::HALF_CEILING, '999', '100', '10', '1'],
            [RoundingMode::HALF_CEILING, '501', '50', '5', '1'],
            [RoundingMode::HALF_CEILING, '500', '50', '5', '1'],
            [RoundingMode::HALF_CEILING, '499', '50', '5', '0'],
            [RoundingMode::HALF_CEILING, '1', '0', '0', '0'],
            [RoundingMode::HALF_CEILING, '0', '0', '0', '0'],
            [RoundingMode::HALF_CEILING, '-1', '0', '0', '0'],
            [RoundingMode::HALF_CEILING, '-499', '-50', '-5', '0'],
            [RoundingMode::HALF_CEILING, '-500', '-50', '-5', '0'],
            [RoundingMode::HALF_CEILING, '-501', '-50', '-5', '-1'],
            [RoundingMode::HALF_CEILING, '-999', '-100', '-10', '-1'],
            [RoundingMode::HALF_CEILING, '-1000', '-100', '-10', '-1'],
            [RoundingMode::HALF_CEILING, '-1001', '-100', '-10', '-1'],
            [RoundingMode::HALF_CEILING, '-1499', '-150', '-15', '-1'],
            [RoundingMode::HALF_CEILING, '-1500', '-150', '-15', '-1'],
            [RoundingMode::HALF_CEILING, '-1501', '-150', '-15', '-2'],
            [RoundingMode::HALF_CEILING, '-1999', '-200', '-20', '-2'],
            [RoundingMode::HALF_CEILING, '-2000', '-200', '-20', '-2'],
            [RoundingMode::HALF_CEILING, '-2001', '-200', '-20', '-2'],
            [RoundingMode::HALF_CEILING, '-2499', '-250', '-25', '-2'],
            [RoundingMode::HALF_CEILING, '-2500', '-250', '-25', '-2'],
            [RoundingMode::HALF_CEILING, '-2501', '-250', '-25', '-3'],
            [RoundingMode::HALF_CEILING, '-2999', '-300', '-30', '-3'],
            [RoundingMode::HALF_CEILING, '-3000', '-300', '-30', '-3'],
            [RoundingMode::HALF_CEILING, '-3001', '-300', '-30', '-3'],
            [RoundingMode::HALF_CEILING, '-3499', '-350', '-35', '-3'],
            [RoundingMode::HALF_CEILING, '-3500', '-350', '-35', '-3'],
            [RoundingMode::HALF_CEILING, '-3501', '-350', '-35', '-4'],
            [RoundingMode::HALF_FLOOR, '3501', '350', '35', '4'],
            [RoundingMode::HALF_FLOOR, '3500', '350', '35', '3'],
            [RoundingMode::HALF_FLOOR, '3499', '350', '35', '3'],
            [RoundingMode::HALF_FLOOR, '3001', '300', '30', '3'],
            [RoundingMode::HALF_FLOOR, '3000', '300', '30', '3'],
            [RoundingMode::HALF_FLOOR, '2999', '300', '30', '3'],
            [RoundingMode::HALF_FLOOR, '2501', '250', '25', '3'],
            [RoundingMode::HALF_FLOOR, '2500', '250', '25', '2'],
            [RoundingMode::HALF_FLOOR, '2499', '250', '25', '2'],
            [RoundingMode::HALF_FLOOR, '2001', '200', '20', '2'],
            [RoundingMode::HALF_FLOOR, '2000', '200', '20', '2'],
            [RoundingMode::HALF_FLOOR, '1999', '200', '20', '2'],
            [RoundingMode::HALF_FLOOR, '1501', '150', '15', '2'],
            [RoundingMode::HALF_FLOOR, '1500', '150', '15', '1'],
            [RoundingMode::HALF_FLOOR, '1499', '150', '15', '1'],
            [RoundingMode::HALF_FLOOR, '1001', '100', '10', '1'],
            [RoundingMode::HALF_FLOOR, '1000', '100', '10', '1'],
            [RoundingMode::HALF_FLOOR, '999', '100', '10', '1'],
            [RoundingMode::HALF_FLOOR, '501', '50', '5', '1'],
            [RoundingMode::HALF_FLOOR, '500', '50', '5', '0'],
            [RoundingMode::HALF_FLOOR, '499', '50', '5', '0'],
            [RoundingMode::HALF_FLOOR, '1', '0', '0', '0'],
            [RoundingMode::HALF_FLOOR, '0', '0', '0', '0'],
            [RoundingMode::HALF_FLOOR, '-1', '0', '0', '0'],
            [RoundingMode::HALF_FLOOR, '-499', '-50', '-5', '0'],
            [RoundingMode::HALF_FLOOR, '-500', '-50', '-5', '-1'],
            [RoundingMode::HALF_FLOOR, '-501', '-50', '-5', '-1'],
            [RoundingMode::HALF_FLOOR, '-999', '-100', '-10', '-1'],
            [RoundingMode::HALF_FLOOR, '-1000', '-100', '-10', '-1'],
            [RoundingMode::HALF_FLOOR, '-1001', '-100', '-10', '-1'],
            [RoundingMode::HALF_FLOOR, '-1499', '-150', '-15', '-1'],
            [RoundingMode::HALF_FLOOR, '-1500', '-150', '-15', '-2'],
            [RoundingMode::HALF_FLOOR, '-1501', '-150', '-15', '-2'],
            [RoundingMode::HALF_FLOOR, '-1999', '-200', '-20', '-2'],
            [RoundingMode::HALF_FLOOR, '-2000', '-200', '-20', '-2'],
            [RoundingMode::HALF_FLOOR, '-2001', '-200', '-20', '-2'],
            [RoundingMode::HALF_FLOOR, '-2499', '-250', '-25', '-2'],
            [RoundingMode::HALF_FLOOR, '-2500', '-250', '-25', '-3'],
            [RoundingMode::HALF_FLOOR, '-2501', '-250', '-25', '-3'],
            [RoundingMode::HALF_FLOOR, '-2999', '-300', '-30', '-3'],
            [RoundingMode::HALF_FLOOR, '-3000', '-300', '-30', '-3'],
            [RoundingMode::HALF_FLOOR, '-3001', '-300', '-30', '-3'],
            [RoundingMode::HALF_FLOOR, '-3499', '-350', '-35', '-3'],
            [RoundingMode::HALF_FLOOR, '-3500', '-350', '-35', '-4'],
            [RoundingMode::HALF_FLOOR, '-3501', '-350', '-35', '-4'],
            [RoundingMode::HALF_EVEN, '3501', '350', '35', '4'],
            [RoundingMode::HALF_EVEN, '3500', '350', '35', '4'],
            [RoundingMode::HALF_EVEN, '3499', '350', '35', '3'],
            [RoundingMode::HALF_EVEN, '3001', '300', '30', '3'],
            [RoundingMode::HALF_EVEN, '3000', '300', '30', '3'],
            [RoundingMode::HALF_EVEN, '2999', '300', '30', '3'],
            [RoundingMode::HALF_EVEN, '2501', '250', '25', '3'],
            [RoundingMode::HALF_EVEN, '2500', '250', '25', '2'],
            [RoundingMode::HALF_EVEN, '2499', '250', '25', '2'],
            [RoundingMode::HALF_EVEN, '2001', '200', '20', '2'],
            [RoundingMode::HALF_EVEN, '2000', '200', '20', '2'],
            [RoundingMode::HALF_EVEN, '1999', '200', '20', '2'],
            [RoundingMode::HALF_EVEN, '1501', '150', '15', '2'],
            [RoundingMode::HALF_EVEN, '1500', '150', '15', '2'],
            [RoundingMode::HALF_EVEN, '1499', '150', '15', '1'],
            [RoundingMode::HALF_EVEN, '1001', '100', '10', '1'],
            [RoundingMode::HALF_EVEN, '1000', '100', '10', '1'],
            [RoundingMode::HALF_EVEN, '999', '100', '10', '1'],
            [RoundingMode::HALF_EVEN, '501', '50', '5', '1'],
            [RoundingMode::HALF_EVEN, '500', '50', '5', '0'],
            [RoundingMode::HALF_EVEN, '499', '50', '5', '0'],
            [RoundingMode::HALF_EVEN, '1', '0', '0', '0'],
            [RoundingMode::HALF_EVEN, '0', '0', '0', '0'],
            [RoundingMode::HALF_EVEN, '-1', '0', '0', '0'],
            [RoundingMode::HALF_EVEN, '-499', '-50', '-5', '0'],
            [RoundingMode::HALF_EVEN, '-500', '-50', '-5', '0'],
            [RoundingMode::HALF_EVEN, '-501', '-50', '-5', '-1'],
            [RoundingMode::HALF_EVEN, '-999', '-100', '-10', '-1'],
            [RoundingMode::HALF_EVEN, '-1000', '-100', '-10', '-1'],
            [RoundingMode::HALF_EVEN, '-1001', '-100', '-10', '-1'],
            [RoundingMode::HALF_EVEN, '-1499', '-150', '-15', '-1'],
            [RoundingMode::HALF_EVEN, '-1500', '-150', '-15', '-2'],
            [RoundingMode::HALF_EVEN, '-1501', '-150', '-15', '-2'],
            [RoundingMode::HALF_EVEN, '-1999', '-200', '-20', '-2'],
            [RoundingMode::HALF_EVEN, '-2000', '-200', '-20', '-2'],
            [RoundingMode::HALF_EVEN, '-2001', '-200', '-20', '-2'],
            [RoundingMode::HALF_EVEN, '-2499', '-250', '-25', '-2'],
            [RoundingMode::HALF_EVEN, '-2500', '-250', '-25', '-2'],
            [RoundingMode::HALF_EVEN, '-2501', '-250', '-25', '-3'],
            [RoundingMode::HALF_EVEN, '-2999', '-300', '-30', '-3'],
            [RoundingMode::HALF_EVEN, '-3000', '-300', '-30', '-3'],
            [RoundingMode::HALF_EVEN, '-3001', '-300', '-30', '-3'],
            [RoundingMode::HALF_EVEN, '-3499', '-350', '-35', '-3'],
            [RoundingMode::HALF_EVEN, '-3500', '-350', '-35', '-4'],
            [RoundingMode::HALF_EVEN, '-3501', '-350', '-35', '-4'],
            [RoundingMode::UNNECESSARY, '3501', null, null, null],
            [RoundingMode::UNNECESSARY, '3500', '350', '35', null],
            [RoundingMode::UNNECESSARY, '3499', null, null, null],
            [RoundingMode::UNNECESSARY, '3001', null, null, null],
            [RoundingMode::UNNECESSARY, '3000', '300', '30', '3'],
            [RoundingMode::UNNECESSARY, '2999', null, null, null],
            [RoundingMode::UNNECESSARY, '2501', null, null, null],
            [RoundingMode::UNNECESSARY, '2500', '250', '25', null],
            [RoundingMode::UNNECESSARY, '2499', null, null, null],
            [RoundingMode::UNNECESSARY, '2001', null, null, null],
            [RoundingMode::UNNECESSARY, '2000', '200', '20', '2'],
            [RoundingMode::UNNECESSARY, '1999', null, null, null],
            [RoundingMode::UNNECESSARY, '1501', null, null, null],
            [RoundingMode::UNNECESSARY, '1500', '150', '15', null],
            [RoundingMode::UNNECESSARY, '1499', null, null, null],
            [RoundingMode::UNNECESSARY, '1001', null, null, null],
            [RoundingMode::UNNECESSARY, '1000', '100', '10', '1'],
            [RoundingMode::UNNECESSARY, '999', null, null, null],
            [RoundingMode::UNNECESSARY, '501', null, null, null],
            [RoundingMode::UNNECESSARY, '500', '50', '5', null],
            [RoundingMode::UNNECESSARY, '499', null, null, null],
            [RoundingMode::UNNECESSARY, '1', null, null, null],
            [RoundingMode::UNNECESSARY, '0', '0', '0', '0'],
            [RoundingMode::UNNECESSARY, '-1', null, null, null],
            [RoundingMode::UNNECESSARY, '-499', null, null, null],
            [RoundingMode::UNNECESSARY, '-500', '-50', '-5', null],
            [RoundingMode::UNNECESSARY, '-501', null, null, null],
            [RoundingMode::UNNECESSARY, '-999', null, null, null],
            [RoundingMode::UNNECESSARY, '-1000', '-100', '-10', '-1'],
            [RoundingMode::UNNECESSARY, '-1001', null, null, null],
            [RoundingMode::UNNECESSARY, '-1499', null, null, null],
            [RoundingMode::UNNECESSARY, '-1500', '-150', '-15', null],
            [RoundingMode::UNNECESSARY, '-1501', null, null, null],
            [RoundingMode::UNNECESSARY, '-1999', null, null, null],
            [RoundingMode::UNNECESSARY, '-2000', '-200', '-20', '-2'],
            [RoundingMode::UNNECESSARY, '-2001', null, null, null],
            [RoundingMode::UNNECESSARY, '-2499', null, null, null],
            [RoundingMode::UNNECESSARY, '-2500', '-250', '-25', null],
            [RoundingMode::UNNECESSARY, '-2501', null, null, null],
            [RoundingMode::UNNECESSARY, '-2999', null, null, null],
            [RoundingMode::UNNECESSARY, '-3000', '-300', '-30', '-3'],
            [RoundingMode::UNNECESSARY, '-3001', null, null, null],
            [RoundingMode::UNNECESSARY, '-3499', null, null, null],
            [RoundingMode::UNNECESSARY, '-3500', '-350', '-35', null],
            [RoundingMode::UNNECESSARY, '-3501', null, null, null],
        ];
    }

    /**
     * @dataProvider providerQuotientAndRemainder
     */
    function it_returns_the_quotient_of_the_division_with_a_given_number($dividend, $divisor, $quotient)
    {
        // let
        $this->beConstructedThrough(
            function () use ($dividend, $divisor) {
                return BigInteger::of($dividend)->quotient($divisor);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($quotient);
    }

    function it_throws_exception_when_computing_the_quotient_of_the_division_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1);

        // then
        $this->shouldThrow('\DivisionByZeroError')->during('quotient', [0]);
    }

    /**
     * @dataProvider providerQuotientAndRemainder
     */
    function it_returns_the_remainder_of_the_division_with_a_given_number(
        $dividend,
        $divisor,
        $quotient,
        $remainder
    ) {
        // let
        $this->beConstructedThrough(
            function () use ($dividend, $divisor) {
                return BigInteger::of($dividend)->remainder($divisor);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($remainder);
    }

    function it_throws_exception_when_computing_the_remainder_of_the_division_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1);

        // then
        $this->shouldThrow('\DivisionByZeroError')->during('remainder', [0]);
    }

    /**
     * @dataProvider providerQuotientAndRemainder
     */
    function it_returns_the_quotient_and_remainder_of_the_division_with_a_given_number(
        $dividend,
        $divisor,
        $quotient,
        $remainder
    ) {
        // let
        $this->beConstructedThroughOf($dividend);
        // and
        list ($q, $r) = $this->quotientAndRemainder($divisor);

        // then
        $q->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $q->__toString()->shouldBeEqualTo($quotient);
        // and
        $r->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $r->__toString()->shouldBeEqualTo($remainder);
    }

    function it_throws_exception_when_computing_the_quotient_and_remainder_of_the_division_by_zero()
    {
        // let
        $this->beConstructedThroughOf(1);

        // then
        $this->shouldThrow('\DivisionByZeroError')->during('quotientAndRemainder', [0]);
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
            ['123456789098765432101234567890987654321', '1', '123456789098765432101234567890987654321', '0'],
            ['123456789098765432101234567890987654321', '-1', '-123456789098765432101234567890987654321', '0'],
            [
                '1282493059039502950823948435791053205342314',
                '24342491090593053',
                '52685366270303839158198740',
                '4167539367989094',
            ],
            [
                '1000000000000000000000000000000000000000000000',
                '7777777777777777',
                '128571428571428584285714285714',
                '2232222222222222',
            ],
            [
                '999999999999999999999999999999999999999999999',
                '22221222222',
                '45002025091579141312274843781092897',
                '13737242865',
            ],
            [
                '49283205308081983923480483094304390249024223',
                '-23981985358744892239240813',
                '-2055009398548863185',
                '20719258837232321643854818',
            ],
            [
                '-8378278174814983902084304176539029302438924',
                '384758527893793829309012129991',
                '-21775419041855',
                '-367584271343844173835372665619',
            ],
            [
                '-444444444444444444444444444444444444411111',
                '-33333333333333',
                '13333333333333466666666666667',
                '-33333333300000',
            ],
        ];
    }

    /**
     * @dataProvider providerPower
     */
    function it_computes_the_power_to_a_given_exponent($number, $exponent, $expected)
    {
        // let
        $this->beConstructedThrough(
            function () use ($number, $exponent) {
                return BigInteger::of($number)->power($exponent);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerPower()
    {
        return [
            ['-3', 0, '1'],
            ['-2', 0, '1'],
            ['-1', 0, '1'],
            ['0', 0, '1'],
            ['1', 0, '1'],
            ['2', 0, '1'],
            ['3', 0, '1'],
            ['-3', 1, '-3'],
            ['-2', 1, '-2'],
            ['-1', 1, '-1'],
            ['0', 1, '0'],
            ['1', 1, '1'],
            ['2', 1, '2'],
            ['3', 1, '3'],
            ['-3', 2, '9'],
            ['-2', 2, '4'],
            ['-1', 2, '1'],
            ['0', 2, '0'],
            ['1', 2, '1'],
            ['2', 2, '4'],
            ['3', 2, '9'],
            ['-3', 3, '-27'],
            ['-2', 3, '-8'],
            ['-1', 3, '-1'],
            ['0', 3, '0'],
            ['1', 3, '1'],
            ['2', 3, '8'],
            ['3', 3, '27'],
            ['0', 1000000, '0'],
            ['1', 1000000, '1'],
            ['-2', 255, '-57896044618658097711785492504343953926634992332820282019728792003956564819968'],
            ['2', 256, '115792089237316195423570985008687907853269984665640564039457584007913129639936'],
            ['-123', 33, '-926549609804623448265268294182900512918058893428212027689876489708283'],
            ['123', 34, '113965602005968684136628000184496763088921243891670079405854808234118809'],
            ['-123456789', 8, '53965948844821664748141453212125737955899777414752273389058576481'],
            ['9876543210', 7, '9167159269868350921847491739460569765344716959834325922131706410000000'],
        ];
    }

    /**
     * @dataProvider providerPowerWithInvalidExponentThrowsException
     */
    function it_throws_exception_when_computes_power_with_invalid_exponent($power)
    {
        // let
        $this->beConstructedThroughOf(1);

        // then
        $this->shouldThrow('\InvalidArgumentException')->duringPower($power);
    }

    public static function providerPowerWithInvalidExponentThrowsException()
    {
        return [
            [-1],
            [1000001],
        ];
    }

    /**
     * @dataProvider providerGcd
     */
    function it_computes_the_gcd_of_this_number_and_a_second_one($a, $b, $expectedGcd)
    {
        // let
        $this->beConstructedThroughOf($a);
        // and
        $gcd = $this->gcd($b);

        // then
        $gcd->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $gcd->__toString()->shouldBeEqualTo($expectedGcd);
    }

    public static function providerGcd()
    {
        return iterator_to_array(self::providerGcdIterator());
    }

    private static function providerGcdIterator()
    {
        $tests = [
            ['0', '0', '0'],
            ['123456789123456789123456789123456789', '0', '123456789123456789123456789123456789'],
            ['123456789123456789123456789123456789', '1', '1'],
            ['123456789123456789123456789123456789', '2', '1'],
            ['123456789123456789123456789123456789', '3', '3'],
            ['123456789123456789123456789123456789', '4', '1'],
            ['123456789123456789123456789123456789', '5', '1'],
            ['123456789123456789123456789123456789', '6', '3'],
            ['123456789123456789123456789123456789', '7', '7'],
            ['123456789123456789123456789123456789', '8', '1'],
            ['123456789123456789123456789123456789', '9', '9'],
            ['123456789123456789123456789123456789', '10', '1'],
            ['123456789123456789123456789123456789', '11', '11'],
            ['123456789123456789123456789123456789', '12', '3'],
            ['123456789123456789123456789123456789', '13', '13'],
            ['123456789123456789123456789123456789', '14', '7'],
            ['123456789123456789123456789123456789', '15', '3'],
            ['123456789123456789123456789123456789', '16', '1'],
            ['123456789123456789123456789123456789', '17', '1'],
            ['123456789123456789123456789123456789', '18', '9'],
            ['123456789123456789123456789123456789', '19', '19'],
            ['123456789123456789123456789123456789', '20', '1'],
            ['123456789123456789123456789123456789', '100', '1'],
            ['123456789123456789123456789123456789', '101', '101'],
            ['123456789123456789123456789123456789', '102', '3'],
            ['123456789123456789123456789123456789', '103', '1'],
            ['123456789123456789123456789123456789', '104', '13'],
            ['123456789123456789123456789123456789', '105', '21'],
            ['123456789123456789123456789123456789', '985', '1'],
            ['123456789123456789123456789123456789', '986', '1'],
            ['123456789123456789123456789123456789', '987', '21'],
            ['123456789123456789123456789123456789', '988', '247'],
            ['123456789123456789123456789123456789', '989', '1'],
            ['123456789123456789123456789123456789', '990', '99'],
            ['123456789123456789123456789123456789', '10010', '1001'],
            ['123456789123456789123456789123456789', '10017', '63'],
            ['123456789123456789123456789123456789', '10089', '171'],
            ['123456789123456789123456789123456789', '10098', '99'],
            ['123456789123456789123456789123456789', '100035', '2223'],
            ['123456789123456789123456789123456789', '1000065', '627'],
            ['123456789123456789123456789123456789', '10000068', '39'],
            ['123456789123456789123456789123456789', '10001222', '7777'],
            ['123456789123456789123456789123456789', '10001277', '24453'],
            ['123456789123456789123456789123456789', '100005258', '157737'],
            ['123456789123456789123456789123456789', '100010001', '2702973'],
            ['123456789123456789123456789123456789', '100148202', '50074101'],
            ['123456789123456789123456789123456789', '100478469', '14354067'],
            ['123456789123456789123456789123456789', '123456789140121129', '8249681517'],
            ['123456789123456789123456789123456789', '123456789150891631', '1385459521'],
            ['123456789123456789123456789123456789', '123456789192058322', '6928754833'],
            ['123456789123456789123456789123456789', '123456789202361433', '1992342261'],
            ['123456789123456789123456789123456789', '999456789162772941', '20786264499'],
            ['123456789123456789123456789123456789', '999456789176548074', '2345678991'],
            ['123456789123456789123456789123456789', '999456789188938248', '2372170689'],
            ['88888777776666655555444443333322222111110000099999', '100000000011886128', '8252579097'],
            ['88888777776666655555444443333322222111110000099999', '100000000013330403', '1162443303'],
            ['88888777776666655555444443333322222111110000099999', '100000000020221920', '2470053077'],
            ['88888777776666655555444443333322222111110000099999', '100000000031937250', '3970893353'],
            ['88888777776666655555444443333322222111110000099999', '100000000043341848', '1102420413'],
            ['88888777776666655555444443333322222111110000099999', '100000000047565681', '1212240071'],
            ['88888777776666655555444443333322222111110000099999', '100000000065586124', '1172302873'],
            ['88888777776666655555444443333322222111110000099999', '100000000068684846', '1051734417'],
            ['88888777776666655555444443333322222111110000099999', '100000000068736887', '2423071539'],
        ];

        foreach ($tests as list ($a, $b, $gcd)) {
            yield [$a, $b, $gcd];
            yield [$b, $a, $gcd];
            yield [$a, "-$b", $gcd];
            yield [$b, "-$a", $gcd];
            yield ["-$a", $b, $gcd];
            yield ["-$b", $a, $gcd];
            yield ["-$a", "-$b", $gcd];
            yield ["-$b", "-$a", $gcd];
        }
    }

    /**
     * @dataProvider providerAbs
     */
    function it_returns_the_absolute_value_of_the_given_number($number, $expected)
    {
        // let
        $this->beConstructedThrough(
            function () use ($number) {
                return BigInteger::of($number)->abs();
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerAbs()
    {
        return [
            ['0', '0'],
            ['123456789012345678901234567890', '123456789012345678901234567890'],
            ['-123456789012345678901234567890', '123456789012345678901234567890'],
        ];
    }

    /**
     * @dataProvider providerNegated
     */
    function it_returns_the_negated_value_of_the_given_number($number, $expected)
    {
        // let
        $this->beConstructedThrough(
            function () use ($number) {
                return BigInteger::of($number)->negate();
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigInteger::class);
        // and
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerNegated()
    {
        return [
            ['0', '0'],
            ['123456789012345678901234567890', '-123456789012345678901234567890'],
            ['-123456789012345678901234567890', '123456789012345678901234567890'],
        ];
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_compares_with_another_number($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->compareTo($b)->shouldBeEqualTo($c);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_is_equal_to_a_given_number($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isEqualTo($b)->shouldBeEqualTo($c === 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_is_less_than_a_given_number($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isLessThan($b)->shouldBeEqualTo($c < 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_is_less_than_or_equal_to_a_given_number($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isLessThanOrEqualTo($b)->shouldBeEqualTo($c <= 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_is_greater_than_a_given_number($a, $b, $c)
    {
        // let
        $this->beConstructedThroughOf($a);

        // then
        $this->isGreaterThan($b)->shouldBeEqualTo($c > 0);
    }

    /**
     * @dataProvider providerCompareTo
     */
    function it_checks_if_this_is_greater_than_or_equal_to_a_given_number($a, $b, $c)
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
            ['9999999999999999999999999', '11111111111111111111111111111111111111111111', -1],
            ['9999999999999999999999999', '-11111111111111111111111111111111111111111111', 1],
            ['-9999999999999999999999999', '11111111111111111111111111111111111111111111', -1],
            ['-9999999999999999999999999', '-11111111111111111111111111111111111111111111', 1],
            ['11111111111111111111111111111111111111111111', '9999999999999999999999999', 1],
            ['11111111111111111111111111111111111111111111', '-9999999999999999999999999', 1],
            ['-11111111111111111111111111111111111111111111', '9999999999999999999999999', -1],
            ['-11111111111111111111111111111111111111111111', '-9999999999999999999999999', -1],
            ['123', '123.000000000000000000000000000000000000000000000000000000001', -1],
            ['123', '123.000000000000000000000000000000000000000000000000000000000', 0],
            ['123', '122.999999999999999999999999999999999999999999999999999999999', 1],
            ['123', '246/2', 0],
            ['123', '245/2', 1],
            ['123', '247/2', -1],
        ];
    }

    /**
     * @dataProvider providerSign
     */
    function it_returns_the_sign_of_the_given_number($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->signum()->shouldBeEqualTo($sign);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_is_equal_to_zero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isZero()->shouldBeEqualTo($sign === 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_is_negative($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isNegative()->shouldBeEqualTo($sign < 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_is_negative_or_zero($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isNegativeOrZero()->shouldBeEqualTo($sign <= 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_is_positive($number, $sign)
    {
        // let
        $this->beConstructedThroughOf($number);

        // then
        $this->isPositive()->shouldBeEqualTo($sign > 0);
    }

    /**
     * @dataProvider providerSign
     */
    function it_checks_if_this_is_positive_or_zero($number, $sign)
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
            ['1000000000000000000000000000000000000000000000000000000000000000000000000000000000', 1],
            ['-1000000000000000000000000000000000000000000000000000000000000000000000000000000000', -1],
        ];
    }

    /**
     * @dataProvider providerToScale
     */
    function it_converts_this_number_to_a_BigDecimal_with_the_given_scale($number, $scale, $expected)
    {
        // let
        $this->beConstructedThrough(
            function () use ($number, $scale) {
                return BigInteger::of($number)->toScale($scale);
            }
        );

        // then
        $this->shouldBeAnInstanceOf(BigDecimal::class);
        // and
        $this->__toString()->shouldBeEqualTo($expected);
    }

    public static function providerToScale()
    {
        return [
            ['12345678901234567890123456789', 0, '12345678901234567890123456789'],
            ['12345678901234567890123456789', 1, '12345678901234567890123456789.0'],
            ['12345678901234567890123456789', 2, '12345678901234567890123456789.00'],
        ];
    }

    /**
     * @dataProvider providerToInteger
     */
    function it_converts_this_number_to_a_native_integer($number)
    {
        // let
        $this->beConstructedThroughOf((string)$number);

        // then
        $this->toInteger()->shouldBeEqualTo($number);
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

    function it_throws_exception_when_converting_to_native_integer_the_negative_overflow()
    {
        // let
        $this->beConstructedThrough(
            function () {
                return BigInteger::of(~PHP_INT_MAX)->minus(1);
            }
        );

        // then
        $this->shouldThrow('\ArithmeticError')->duringToInteger();
    }

    function it_throws_exception_when_converting_to_native_integer_the_positive_overflow()
    {
        // let
        $this->beConstructedThrough(
            function () {
                return BigInteger::of(PHP_INT_MAX)->plus(1);
            }
        );

        // then
        $this->shouldThrow('\ArithmeticError')->duringToInteger();
    }

    /**
     * @dataProvider providerToFloat
     */
    function it_ToFloat($value, $float)
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
            ['1', 1.0],
            ['-1', -1.0],
            ['100000000000000000000000000000000000000000000000000', 1e50],
            ['-100000000000000000000000000000000000000000000000000', -1e50],
            ['1e3000', INF],
            ['-1e3000', -INF],
        ];
    }

    /**
     * @dataProvider providerToBase
     */
    function it_returns_a_string_representation_of_a_positive_number_in_the_given_base($number, $base, $expected)
    {
        // let
        $this->beConstructedThroughParse($number);

        // then
        $this->toBase($base)->shouldBeEqualTo($expected);
    }

    /**
     * @dataProvider providerToBase
     */
    function it_returns_a_string_representation_of_a_negative_number_in_the_given_base($number, $base, $expected)
    {
        // let
        $this->beConstructedThroughParse('-'.$number);

        // then
        $this->toBase($base)->shouldBeEqualTo('-'.$expected);
    }

    public static function providerToBase()
    {
        return [
            ['640998479760579495168036691627608949', 36, '110011001100110011001111'],
            ['335582856048758779730579523833856636', 35, '110011001100110011001111'],
            ['172426711023004493064981145981549295', 34, '110011001100110011001111'],
            ['86853227285668653965326574185738990', 33, '110011001100110011001111'],
            ['42836489934972583913564073319498785', 32, '110011001100110011001111'],
            ['20658924711984480538771889603666144', 31, '110011001100110011001111'],
            ['9728140488839986222205212599027931', 30, '110011001100110011001111'],
            ['4465579470019956787945275674107410', 29, '110011001100110011001111'],
            ['1994689924537781753408144504465645', 28, '110011001100110011001111'],
            ['865289950909412968716094193925700', 27, '110011001100110011001111'],
            ['363729369583879309352831568000039', 26, '110011001100110011001111'],
            ['147793267388865354156500488297526', 25, '110011001100110011001111'],
            ['57888012016107577099138793486425', 24, '110011001100110011001111'],
            ['21788392294523974761749372677800', 23, '110011001100110011001111'],
            ['7852874701996329566765721637715', 22, '110011001100110011001111'],
            ['2699289081943123258094476428634', 21, '110011001100110011001111'],
            ['880809345058406615041344008421', 20, '110011001100110011001111'],
            ['271401690926468032718781859340', 19, '110011001100110011001111'],
            ['78478889737009209699633503455', 18, '110011001100110011001111'],
            ['21142384915931646646976872830', 17, '110011001100110011001111'],
            ['5261325448418072742917574929', 16, '110011001100110011001111'],
            ['1197116069565850925807253616', 15, '110011001100110011001111'],
            ['245991074299834917455374155', 14, '110011001100110011001111'],
            ['44967318722190498361960610', 13, '110011001100110011001111'],
            ['7177144825886069940574045', 12, '110011001100110011001111'],
            ['976899716207148313491924', 11, '110011001100110011001111'],
            ['110011001100110011001111', 10, '110011001100110011001111'],
            ['9849210196991880028870', 9, '110011001100110011001111'],
            ['664244955832213832265', 8, '110011001100110011001111'],
            ['31291601125492514360', 7, '110011001100110011001111'],
            ['922063395565287619', 6, '110011001100110011001111'],
            ['14328039609468906', 5, '110011001100110011001111'],
            ['88305875046485', 4, '110011001100110011001111'],
            ['127093291420', 3, '110011001100110011001111'],
            ['13421775', 2, '110011001100110011001111'],
            ['106300512100105327644605138221229898724869759421181854980', 36, 'zyxwvutsrqponmlkjihgfedcba9876543210'],
            ['1101553773143634726491620528194292510495517905608180485', 35, 'yxwvutsrqponmlkjihgfedcba9876543210'],
            ['11745843093701610854378775891116314824081102660800418', 34, 'xwvutsrqponmlkjihgfedcba9876543210'],
            ['128983956064237823710866404905431464703849549412368', 33, 'wvutsrqponmlkjihgfedcba9876543210'],
            ['1459980823972598128486511383358617792788444579872', 32, 'vutsrqponmlkjihgfedcba9876543210'],
            ['17050208381689099029767742314582582184093573615', 31, 'utsrqponmlkjihgfedcba9876543210'],
            ['205646315052919334126040428061831153388822830', 30, 'tsrqponmlkjihgfedcba9876543210'],
            ['2564411043271974895869785066497940850811934', 29, 'srqponmlkjihgfedcba9876543210'],
            ['33100056003358651440264672384704297711484', 28, 'rqponmlkjihgfedcba9876543210'],
            ['442770531899482980347734468443677777577', 27, 'qponmlkjihgfedcba9876543210'],
            ['6146269788878825859099399609538763450', 26, 'ponmlkjihgfedcba9876543210'],
            ['88663644327703473714387251271141900', 25, 'onmlkjihgfedcba9876543210'],
            ['1331214537196502869015340298036888', 24, 'nmlkjihgfedcba9876543210'],
            ['20837326537038308910317109288851', 23, 'mlkjihgfedcba9876543210'],
            ['340653664490377789692799452102', 22, 'lkjihgfedcba9876543210'],
            ['5827980550840017565077671610', 21, 'kjihgfedcba9876543210'],
            ['104567135734072022160664820', 20, 'jihgfedcba9876543210'],
            ['1972313422155189164466189', 19, 'ihgfedcba9876543210'],
            ['39210261334551566857170', 18, 'hgfedcba9876543210'],
            ['824008854613343261192', 17, 'gfedcba9876543210'],
            ['18364758544493064720', 16, 'fedcba9876543210'],
            ['435659737878916215', 15, 'edcba9876543210'],
            ['11046255305880158', 14, 'dcba9876543210'],
            ['300771807240918', 13, 'cba9876543210'],
            ['8842413667692', 12, 'ba9876543210'],
            ['282458553905', 11, 'a9876543210'],
            ['9876543210', 10, '9876543210'],
            ['381367044', 9, '876543210'],
            ['16434824', 8, '76543210'],
            ['800667', 7, '6543210'],
            ['44790', 6, '543210'],
            ['2930', 5, '43210'],
            ['228', 4, '3210'],
            ['21', 3, '210'],
            ['2', 2, '10'],
        ];
    }

    /**
     * @dataProvider providerToInvalidBaseThrowsException
     */
    function it_throws_exception_when_converting_to_invalid_base($base)
    {
        // let
        $this->beConstructedThroughOf(0);

        // then
        $this->shouldThrow('\InvalidArgumentException')->duringToBase($base);
    }

    public static function providerToInvalidBaseThrowsException()
    {
        return [
            [-2],
            [-1],
            [0],
            [1],
            [37],
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

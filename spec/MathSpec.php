<?php

namespace spec\Arki\Math;

use Arki\Math\Math;
use Arki\Math\RoundingMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin Math
 */
class MathSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Arki\Math\Math');
    }

    function it_can_not_be_instantiated()
    {
        $this->shouldThrow(\Exception::class)->duringInstantiation();
    }

    function it_computes_the_absolute_value_of_a_given_number()
    {
        expect(Math::abs('-1'))->toBe('1');
        expect(Math::abs('1'))->toBe('1');
        expect(Math::abs('+1'))->toBe('+1');
    }

    function it_negates_a_number()
    {
        expect(Math::neg('-1'))->toBe('1');
        expect(Math::neg('1'))->toBe('-1');
    }

    function it_compares_two_numbers()
    {
        expect(Math::cmp('1', '1'))->toBe(0);
        expect(Math::cmp('1', '0'))->toBe(1);
        expect(Math::cmp('0', '1'))->toBe(-1);
    }

    function it_adds_two_numbers()
    {
        expect(Math::add('1', '1'))->toBe('2');
        expect(Math::add('-1', '0'))->toBe('-1');
    }

    function it_subtracts_one_number_from_another()
    {
        expect(Math::sub('1', '1'))->toBe('0');
        expect(Math::sub('-1', '0'))->toBe('-1');
    }

    function it_multiplies_two_numbers()
    {
        expect(Math::mul('1', '1'))->toBe('1');
        expect(Math::mul('2', '3'))->toBe('6');
        expect(Math::mul('0', '3'))->toBe('0');
    }

    function it_computes_the_quotient_of_the_division_of_two_numbers()
    {
        expect(Math::divQ('10', '2'))->toBe('5');
        expect(Math::divQ('9', '2'))->toBe('4');
        expect(Math::divQ('4', '3'))->toBe('1');
    }

    function it_computes_the_remainder_of_the_division_of_two_numbers()
    {
        expect(Math::divR('10', '2'))->toBe('0');
        expect(Math::divR('9', '2'))->toBe('1');
        expect(Math::divR('4', '3'))->toBe('1');
    }

    function it_computes_the_quotient_and_remainder_of_the_division_of_two_numbers()
    {
        expect(Math::divQR('10', '2'))->toBe(['5', '0']);
        expect(Math::divQR('9', '2'))->toBe(['4', '1']);
        expect(Math::divQR('4', '3'))->toBe(['1', '1']);
    }

    function it_exponentiates_a_number()
    {
        expect(Math::pow('-2', '2'))->toBe('4');
        expect(Math::pow('3', '3'))->toBe('27');
    }

    function it_computes_the_greatest_common_divisor_of_two_numbers()
    {
        expect(Math::gcd('6', '9'))->toBe('3');
        expect(Math::gcd('25', '20'))->toBe('5');
    }

    function it_rounds_a_division()
    {
        expect(Math::divRound('101', '9', RoundingMode::CEILING))->toBe('12');
        expect(Math::divRound('101', '9', RoundingMode::DOWN))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::FLOOR))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::HALF_CEILING))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::HALF_DOWN))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::HALF_EVEN))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::HALF_FLOOR))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::HALF_UP))->toBe('11');
        expect(Math::divRound('101', '9', RoundingMode::UP))->toBe('12');
    }
}

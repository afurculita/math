<?php

namespace spec\Arki\Math;

use Arki\Math\RoundingMode;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @mixin RoundingMode
 */
class RoundingModeSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Arki\Math\RoundingMode');
    }

    function it_is_not_instantiable()
    {
        $this->shouldThrow('\Exception')->duringInstantiation();
    }
}

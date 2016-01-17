<?php

namespace spec\Arki\Math;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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

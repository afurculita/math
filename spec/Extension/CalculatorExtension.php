<?php

namespace spec\Arki\Math\Extension;

use Arki\Math\Calculator\BcMathCalculator;
use Arki\Math\Calculator\Calculator;
use Arki\Math\Calculator\GmpCalculator;
use Arki\Math\Calculator\NativeCalculator;
use Arki\Math\Math;
use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\ServiceContainer;

class CalculatorExtension implements ExtensionInterface
{
    /**
     * @param ServiceContainer $container
     */
    public function load(ServiceContainer $container)
    {
        Math::with($this->getCalculator());
    }

    /**
     * @return Calculator
     */
    private function getCalculator()
    {
        switch ($calculator = getenv('CALCULATOR')) {
            case 'GMP':
                $calculator = new GmpCalculator();
                break;
            case 'BCMath':
                $calculator = new BcMathCalculator();
                break;
            case 'Native':
            default:
                $calculator = new NativeCalculator();
                break;
        }

        echo 'Using ', get_class($calculator), PHP_EOL;

        return $calculator;
    }
}

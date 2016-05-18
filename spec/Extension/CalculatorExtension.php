<?php

namespace spec\Arki\Math\Extension;

use Arki\Math\Calculator\BcMathCalculator;
use Arki\Math\Calculator\GmpCalculator;
use Arki\Math\Calculator\NativeCalculator;
use Arki\Math\Math;
use PhpSpec\Extension\ExtensionInterface;
use PhpSpec\ServiceContainer;

class CalculatorExtension implements ExtensionInterface
{
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
                $calculator = new NativeCalculator();
                break;
            default:
                if ($calculator === false) {
                    echo 'CALCULATOR environment variable not set!'.PHP_EOL;
                } else {
                    echo 'Unknown calculator: '.$calculator.PHP_EOL;
                }
                echo 'Example usage: CALCULATOR={calculator} vendor/bin/phpspec'.PHP_EOL;
                echo 'Available calculators: GMP, BCMath, Native'.PHP_EOL;
                exit(1);
        }
        echo 'Using ', get_class($calculator), PHP_EOL;

        return $calculator;
    }
}

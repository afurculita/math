# Arkitekto / Math [![Build status][travis-image]][travis-url] [![Version][version-image]][version-url] [![PHP Version][php-version-image]][php-version-url]

> `PHP` library for arbitrary precision arithmetic, operating on signed integers, rational numbers, and floating-point numbers. 
> Very useful when you need easier handling of large numbers inside financial application without precision loss.

### Installation

You can install this library via [Composer](https://getcomposer.org/). Run:

```bash
$ composer require arkitekto/math
```

### Requirements

This library requires `PHP 5.6`, `PHP 7` or `HHVM`.

Although the library can work seamlessly on any PHP installation, it is highly recommended that you install the
[GMP](http://php.net/manual/en/book.gmp.php) or [BCMath](http://php.net/manual/en/book.bc.php) extension
to speed up calculations. The fastest available calculator implementation will be automatically selected at runtime.

### The issue with Floating point numbers

Apart from solving the problem of working with numbers bigger than the allowed limit in PHP, this library tries to solve
also the problem with loosing precision when using floating point numbers. 

As the [PHP documentation for float numbers](http://php.net/manual/en/language.types.float.php) states:

```
Floating point numbers have limited precision. Although it depends on the system, PHP typically uses the IEEE 754 
double precision format, which will give a maximum relative error due to rounding in the order of 1.11e-16. 
Non elementary arithmetic operations may give larger errors, and, of course, error propagation must be considered 
when several operations are compounded.

Additionally, rational numbers that are exactly representable as floating point numbers in base 10, like 0.1 or 0.7, 
do not have an exact representation as floating point numbers in base 2, which is used internally, no matter the size 
of the mantissa. Hence, they cannot be converted into their internal binary counterparts without a small loss of precision. 
This can lead to confusing results: for example, floor((0.1+0.7)*10) will usually return 7 instead of the expected 8, 
since the internal representation will be something like 7.9999999999999991118....

So never trust floating number results to the last digit, and do not compare floating point numbers directly for equality. 
If higher precision is necessary, the arbitrary precision math functions and gmp functions are available.

For a "simple" explanation, see the » floating point guide that's also titled "Why don’t my numbers add up?"
```

### Overview

#### Instantiation

The constructors of the classes are not public, you must use a factory method to obtain an instance.

All classes provide an `of()` factory method that accepts any of the following types:

- `Arki\Math\Number` instances
- `int` numbers
- `float` numbers
- `string` representations of integer, decimal and rational numbers

Example:

```php
BigInteger::of(123546);
BigInteger::of('9999999999999999999999999999999999999999999');

BigDecimal::of(1.2);
BigDecimal::of('9.99999999999999999999999999999999999999999999');

BigRational::of('2/3');
BigRational::of('1.1'); // 11/10
```

Note that all `of()` methods accept all of the representations above, *as long as it can be safely converted to
the current type*:

```php
BigInteger::of('1.00'); // 1
BigInteger::of('1.01'); // ArithmeticError

BigDecimal::of('1/8'); // 0.125
BigDecimal::of('1/3'); // ArithmeticError
```

Note about native integers: instantiating from an `int` is safe *as long as you don't exceed the maximum
value for your platform* (`PHP_INT_MAX`), in which case it would be transparently converted to `float` by PHP without
notice, and could result in a loss of information. In doubt, prefer instantiating from a `string`, which supports
an unlimited numbers of digits:

```php
echo BigInteger::of(999999999999999999999); // 1000000000000000000000
echo BigInteger::of('999999999999999999999'); // 999999999999999999999
```

Note about floating-point values: instantiating from a `float` might be unsafe, as floating-point values are
imprecise by design, and could result in a loss of information. Always prefer instantiating from a `string`, which
supports an unlimited number of digits:

```php
echo BigDecimal::of(1.99999999999999999999); // 2
echo BigDecimal::of('1.99999999999999999999'); // 1.99999999999999999999
```

#### Immutability & chaining

The `BigInteger`, `BigDecimal` and `BigRational` classes are immutable: their value never changes,
so that they can be safely passed around. All methods that return a `BigInteger`, `BigDecimal` or `BigRational`
return a new object, leaving the original object unaffected:

```php
$ten = BigInteger::of(10);

echo $ten->plus(5); // 15
echo $ten->multipliedBy(3); // 30
```

The methods can be chained for better readability:

```php
echo BigInteger::of(10)->plus(5)->multipliedBy(3); // 45
```

#### Parameter types

All methods that accept a number: `plus()`, `minus()`, `multipliedBy()`, etc. accept the same types as `of()`.
For example, given the following number:

```php
$integer = BigInteger::of(123);
```

The following lines are equivalent:

```php
$integer->multipliedBy(123);
$integer->multipliedBy('123');
$integer->multipliedBy($integer);
```

Just like `of()`, other types of `Number` are acceptable, as long as they can be safely converted to the current type:

```php
echo BigInteger::of(2)->multipliedBy(BigDecimal::of('2.0')); // 4
echo BigInteger::of(2)->multipliedBy(BigDecimal::of('2.5')); // ArithmeticError
echo BigDecimal::of(2.5)->multipliedBy(BigInteger::of(2)); // 5.0
```

#### Division & rounding

##### BigInteger

By default, dividing a `BigInteger` returns the exact result of the division, or throws an exception if the remainder
of the division is not zero:

```php
echo BigInteger::of(999)->dividedBy(3); // 333
echo BigInteger::of(1000)->dividedBy(3); // RoundingNecessaryException
```

You can pass an optional [rounding mode](./src/RoundingMode.php) to round the result, if necessary:

```php
echo BigInteger::of(1000)->dividedBy(3, RoundingMode::DOWN); // 333
echo BigInteger::of(1000)->dividedBy(3, RoundingMode::UP); // 334
```

If you're into quotients and remainders, there are methods for this, too:

```php
echo BigInteger::of(1000)->quotient(3); // 333
echo BigInteger::of(1000)->remainder(3); // 1
```

You can even get both at the same time:

```php
list ($quotient, $remainder) = BigInteger::of(1000)->quotientAndRemainder(3);
```

##### BigDecimal

Dividing a `BigDecimal` always requires a scale to be specified. If the exact result of the division does not fit in
the given scale, a [rounding mode](./src/RoundingMode.php) must be provided.

```php
echo BigDecimal::of(1)->dividedBy('8', 3); // 0.125
echo BigDecimal::of(1)->dividedBy('8', 2); // RoundingNecessaryException
echo BigDecimal::of(1)->dividedBy('8', 2, RoundingMode::HALF_DOWN); // 0.12
echo BigDecimal::of(1)->dividedBy('8', 2, RoundingMode::HALF_UP); // 0.13
```

If you know that the division yields a finite number of decimals places, you can use `exactlyDividedBy()`, which will
automatically compute the required scale to fit the result, or throw an exception if the division yields an infinite
repeating decimal:

```php
echo BigDecimal::of(1)->exactlyDividedBy(256); // 0.00390625
echo BigDecimal::of(1)->exactlyDividedBy(11); // RoundingNecessaryException
```

##### BigRational

The result of the division of a `BigRational` can always be represented exactly:

```php
echo BigRational::of('123/456')->dividedBy('7'); // 123/3192
echo BigRational::of('123/456')->dividedBy('9/8'); // 984/4104
```

#### Serialization

`BigInteger`, `BigDecimal` and `BigRational` can be safely serialized on a machine and unserialized on another,
even if these machines do not share the same set of PHP extensions.

For example, serializing on a machine with GMP support and unserializing on a machine that does not have this extension
installed will still work as expected.

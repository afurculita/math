<?php

/*
 * This file is part of the Arkitekto\Math library.
 *
 * (c) Alexandru Furculita <alex@furculita.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Arki\Math\Exception;

/**
 * Thrown to indicate that the application has attempted to convert a string to one of the numeric types,
 * but that the string does not have the appropriate format.
 */
final class NumberFormatException extends \InvalidArgumentException
{
}

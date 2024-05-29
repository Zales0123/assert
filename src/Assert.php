<?php

/*
 * This file is part of the webmozart/assert package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Assert;

use ArrayAccess;
use BadMethodCallException;
use Closure;
use Countable;
use DateTime;
use DateTimeImmutable;
use Exception;
use ResourceBundle;
use SimpleXMLElement;
use Throwable;
use Traversable;

/**
 * Efficient assertions to validate the input/output of your methods.
 *
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Assert
{
    use Mixin;

    /**
     * @psalm-pure
     * @psalm-assert string $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function string($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_string($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a string. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert non-empty-string $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function stringNotEmpty($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($value, $message, $exceptionClass);
        static::notEq($value, '', $message, $exceptionClass);
    }

    /**
     * @psalm-pure
     * @psalm-assert int $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function integer($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_int($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an integer. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert numeric $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function integerish($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_numeric($value) || $value != (int) $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected an integerish value. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert positive-int $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function positiveInteger($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!(\is_int($value) && $value > 0)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a positive integer. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert float $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function float($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_float($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a float. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert numeric $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function numeric($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_numeric($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a numeric. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert positive-int|0 $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function natural($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_int($value) || $value < 0) {
            static::reportException(\sprintf(
                $message ?: 'Expected a non-negative integer. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert bool $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function boolean($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_bool($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a boolean. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert scalar $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function scalar($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_scalar($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a scalar. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert object $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function object($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_object($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an object. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert resource $value
     *
     * @param mixed       $value
     * @param string|null $type    type of resource this should be. @see https://www.php.net/manual/en/function.get-resource-type.php
     * @param string      $message
     *
     * @throws InvalidArgumentException
     */
    public static function resource($value, $type = null, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_resource($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a resource. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }

        if ($type && $type !== \get_resource_type($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a resource of type %2$s. Got: %s',
                static::typeToString($value),
                $type
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert callable $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isCallable($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_callable($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a callable. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert array $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isArray($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_array($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an array. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert iterable $value
     *
     * @deprecated use "isIterable" or "isInstanceOf" instead
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isTraversable($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        @\trigger_error(
            \sprintf(
                'The "%s" assertion is deprecated. You should stop using it, as it will soon be removed in 2.0 version. Use "isIterable" or "isInstanceOf" instead.',
                __METHOD__
            ),
            \E_USER_DEPRECATED
        );

        if (!\is_array($value) && !($value instanceof Traversable)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a traversable. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert array|ArrayAccess $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isArrayAccessible($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_array($value) && !($value instanceof ArrayAccess)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an array accessible. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert countable $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isCountable($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (
            !\is_array($value)
            && !($value instanceof Countable)
            && !($value instanceof ResourceBundle)
            && !($value instanceof SimpleXMLElement)
        ) {
            static::reportException(\sprintf(
                $message ?: 'Expected a countable. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert iterable $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isIterable($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_array($value) && !($value instanceof Traversable)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an iterable. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $class
     * @psalm-assert ExpectedType $value
     *
     * @param mixed         $value
     * @param string|object $class
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function isInstanceOf($value, $class, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!($value instanceof $class)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an instance of %2$s. Got: %s',
                static::typeToString($value),
                $class
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $class
     * @psalm-assert !ExpectedType $value
     *
     * @param mixed         $value
     * @param string|object $class
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function notInstanceOf($value, $class, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($value instanceof $class) {
            static::reportException(\sprintf(
                $message ?: 'Expected an instance other than %2$s. Got: %s',
                static::typeToString($value),
                $class
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-param array<class-string> $classes
     *
     * @param mixed                $value
     * @param array<object|string> $classes
     * @param string               $message
     *
     * @throws InvalidArgumentException
     */
    public static function isInstanceOfAny($value, array $classes, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        foreach ($classes as $class) {
            if ($value instanceof $class) {
                return;
            }
        }

        static::reportException(\sprintf(
            $message ?: 'Expected an instance of any of %2$s. Got: %s',
            static::typeToString($value),
            \implode(', ', \array_map(array(static::class, 'valueToString'), $classes))
        ), $exceptionClass);
    }

    /**
     * @psalm-pure
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $class
     * @psalm-assert ExpectedType|class-string<ExpectedType> $value
     *
     * @param object|string $value
     * @param string        $class
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function isAOf($value, $class, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($class, 'Expected class as a string. Got: %s');

        if (!\is_a($value, $class, \is_string($value))) {
            static::reportException(sprintf(
                $message ?: 'Expected an instance of this class or to this class among its parents "%2$s". Got: %s',
                static::valueToString($value),
                $class
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-template UnexpectedType of object
     * @psalm-param class-string<UnexpectedType> $class
     * @psalm-assert !UnexpectedType $value
     * @psalm-assert !class-string<UnexpectedType> $value
     *
     * @param object|string $value
     * @param string        $class
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function isNotA($value, $class, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($class, 'Expected class as a string. Got: %s');

        if (\is_a($value, $class, \is_string($value))) {
            static::reportException(sprintf(
                $message ?: 'Expected an instance of this class or to this class among its parents other than "%2$s". Got: %s',
                static::valueToString($value),
                $class
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-param array<class-string> $classes
     *
     * @param object|string $value
     * @param string[]      $classes
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function isAnyOf($value, array $classes, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        foreach ($classes as $class) {
            static::string($class, 'Expected class as a string. Got: %s');

            if (\is_a($value, $class, \is_string($value))) {
                return;
            }
        }

        static::reportException(sprintf(
            $message ?: 'Expected an instance of any of this classes or any of those classes among their parents "%2$s". Got: %s',
            static::valueToString($value),
            \implode(', ', $classes)
        ), $exceptionClass);
    }

    /**
     * @psalm-pure
     * @psalm-assert empty $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isEmpty($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!empty($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an empty value. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert !empty $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notEmpty($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (empty($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a non-empty value. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert null $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function null($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (null !== $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected null. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert !null $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notNull($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (null === $value) {
            static::reportException($message ?: 'Expected a value other than null.', $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert true $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function true($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (true !== $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to be true. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert false $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function false($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false !== $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to be false. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert !false $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notFalse($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false === $value) {
            static::reportException($message ?: 'Expected a value other than false.', $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function ip($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false === \filter_var($value, \FILTER_VALIDATE_IP)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to be an IP. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function ipv4($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to be an IPv4. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function ipv6($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false === \filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to be an IPv6. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function email($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false === \filter_var($value, FILTER_VALIDATE_EMAIL)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to be a valid e-mail address. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * Does non strict comparisons on the items, so ['3', 3] will not pass the assertion.
     *
     * @param array  $values
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function uniqueValues(array $values, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $allValues = \count($values);
        $uniqueValues = \count(\array_unique($values));

        if ($allValues !== $uniqueValues) {
            $difference = $allValues - $uniqueValues;

            static::reportException(\sprintf(
                $message ?: 'Expected an array of unique values, but %s of them %s duplicated',
                $difference,
                (1 === $difference ? 'is' : 'are')
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param mixed  $expect
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function eq($value, $expect, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($expect != $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($expect)
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param mixed  $expect
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notEq($value, $expect, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($expect == $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected a different value than %s.',
                static::valueToString($expect)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $expect
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function same($value, $expect, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($expect !== $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value identical to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($expect)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $expect
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notSame($value, $expect, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($expect === $value) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value not identical to %s.',
                static::valueToString($expect)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $limit
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function greaterThan($value, $limit, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($value <= $limit) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value greater than %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $limit
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function greaterThanEq($value, $limit, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($value < $limit) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value greater than or equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $limit
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function lessThan($value, $limit, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($value >= $limit) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value less than %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $limit
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function lessThanEq($value, $limit, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($value > $limit) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value less than or equal to %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($limit)
            ), $exceptionClass);
        }
    }

    /**
     * Inclusive range, so Assert::(3, 3, 5) passes.
     *
     * @psalm-pure
     *
     * @param mixed  $value
     * @param mixed  $min
     * @param mixed  $max
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function range($value, $min, $max, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($value < $min || $value > $max) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value between %2$s and %3$s. Got: %s',
                static::valueToString($value),
                static::valueToString($min),
                static::valueToString($max)
            ), $exceptionClass);
        }
    }

    /**
     * A more human-readable alias of Assert::inArray().
     *
     * @psalm-pure
     *
     * @param mixed  $value
     * @param array  $values
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function oneOf($value, array $values, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::inArray($value, $values, $message, $exceptionClass);
    }

    /**
     * Does strict comparison, so Assert::inArray(3, ['3']) does not pass the assertion.
     *
     * @psalm-pure
     *
     * @param mixed  $value
     * @param array  $values
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function inArray($value, array $values, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\in_array($value, $values, true)) {
            static::reportException(\sprintf(
                $message ?: 'Expected one of: %2$s. Got: %s',
                static::valueToString($value),
                \implode(', ', \array_map(array(static::class, 'valueToString'), $values))
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $subString
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function contains($value, $subString, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false === \strpos($value, $subString)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($subString)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $subString
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notContains($value, $subString, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (false !== \strpos($value, $subString)) {
            static::reportException(\sprintf(
                $message ?: '%2$s was not expected to be contained in a value. Got: %s',
                static::valueToString($value),
                static::valueToString($subString)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notWhitespaceOnly($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (\preg_match('/^\s*$/', $value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a non-whitespace string. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $prefix
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function startsWith($value, $prefix, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (0 !== \strpos($value, $prefix)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to start with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($prefix)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $prefix
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notStartsWith($value, $prefix, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (0 === \strpos($value, $prefix)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value not to start with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($prefix)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function startsWithLetter($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($value);

        $valid = isset($value[0]);

        if ($valid) {
            $locale = \setlocale(LC_CTYPE, 0);
            \setlocale(LC_CTYPE, 'C');
            $valid = \ctype_alpha($value[0]);
            \setlocale(LC_CTYPE, $locale);
        }

        if (!$valid) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to start with a letter. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $suffix
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function endsWith($value, $suffix, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($suffix !== \substr($value, -\strlen($suffix))) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to end with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($suffix)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $suffix
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notEndsWith($value, $suffix, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($suffix === \substr($value, -\strlen($suffix))) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value not to end with %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($suffix)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $pattern
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function regex($value, $pattern, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\preg_match($pattern, $value)) {
            static::reportException(\sprintf(
                $message ?: 'The value %s does not match the expected pattern.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $pattern
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function notRegex($value, $pattern, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (\preg_match($pattern, $value, $matches, PREG_OFFSET_CAPTURE)) {
            static::reportException(\sprintf(
                $message ?: 'The value %s matches the pattern %s (at offset %d).',
                static::valueToString($value),
                static::valueToString($pattern),
                $matches[0][1]
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function unicodeLetters($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($value);

        if (!\preg_match('/^\p{L}+$/u', $value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain only Unicode letters. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function alpha($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($value);

        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_alpha($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain only letters. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function digits($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_digit($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain digits only. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function alnum($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_alnum($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain letters and digits only. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert lowercase-string $value
     *
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function lower($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_lower($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain lowercase characters only. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert !lowercase-string $value
     *
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function upper($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $locale = \setlocale(LC_CTYPE, 0);
        \setlocale(LC_CTYPE, 'C');
        $valid = !\ctype_upper($value);
        \setlocale(LC_CTYPE, $locale);

        if ($valid) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain uppercase characters only. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param int    $length
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function length($value, $length, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ($length !== static::strlen($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain %2$s characters. Got: %s',
                static::valueToString($value),
                $length
            ), $exceptionClass);
        }
    }

    /**
     * Inclusive min.
     *
     * @psalm-pure
     *
     * @param string    $value
     * @param int|float $min
     * @param string    $message
     *
     * @throws InvalidArgumentException
     */
    public static function minLength($value, $min, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (static::strlen($value) < $min) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain at least %2$s characters. Got: %s',
                static::valueToString($value),
                $min
            ), $exceptionClass);
        }
    }

    /**
     * Inclusive max.
     *
     * @psalm-pure
     *
     * @param string    $value
     * @param int|float $max
     * @param string    $message
     *
     * @throws InvalidArgumentException
     */
    public static function maxLength($value, $max, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (static::strlen($value) > $max) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain at most %2$s characters. Got: %s',
                static::valueToString($value),
                $max
            ), $exceptionClass);
        }
    }

    /**
     * Inclusive , so Assert::lengthBetween('asd', 3, 5); passes the assertion.
     *
     * @psalm-pure
     *
     * @param string    $value
     * @param int|float $min
     * @param int|float $max
     * @param string    $message
     *
     * @throws InvalidArgumentException
     */
    public static function lengthBetween($value, $min, $max, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $length = static::strlen($value);

        if ($length < $min || $length > $max) {
            static::reportException(\sprintf(
                $message ?: 'Expected a value to contain between %2$s and %3$s characters. Got: %s',
                static::valueToString($value),
                $min,
                $max
            ), $exceptionClass);
        }
    }

    /**
     * Will also pass if $value is a directory, use Assert::file() instead if you need to be sure it is a file.
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function fileExists($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::string($value);

        if (!\file_exists($value)) {
            static::reportException(\sprintf(
                $message ?: 'The file %s does not exist.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function file($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::fileExists($value, $message);

        if (!\is_file($value)) {
            static::reportException(\sprintf(
                $message ?: 'The path %s is not a file.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function directory($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::fileExists($value, $message, $exceptionClass);

        if (!\is_dir($value)) {
            static::reportException(\sprintf(
                $message ?: 'The path %s is no directory.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function readable($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_readable($value)) {
            static::reportException(\sprintf(
                $message ?: 'The path %s is not readable.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function writable($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_writable($value)) {
            static::reportException(\sprintf(
                $message ?: 'The path %s is not writable.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-assert class-string $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function classExists($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\class_exists($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an existing class name. Got: %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $class
     * @psalm-assert class-string<ExpectedType>|ExpectedType $value
     *
     * @param mixed         $value
     * @param string|object $class
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function subclassOf($value, $class, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_subclass_of($value, $class)) {
            static::reportException(\sprintf(
                $message ?: 'Expected a sub-class of %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($class)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-assert class-string $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function interfaceExists($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\interface_exists($value)) {
            static::reportException(\sprintf(
                $message ?: 'Expected an existing interface name. got %s',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-template ExpectedType of object
     * @psalm-param class-string<ExpectedType> $interface
     * @psalm-assert class-string<ExpectedType> $value
     *
     * @param mixed  $value
     * @param mixed  $interface
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function implementsInterface($value, $interface, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\in_array($interface, \class_implements($value))) {
            static::reportException(\sprintf(
                $message ?: 'Expected an implementation of %2$s. Got: %s',
                static::valueToString($value),
                static::valueToString($interface)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-param class-string|object $classOrObject
     *
     * @param string|object $classOrObject
     * @param mixed         $property
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function propertyExists($classOrObject, $property, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\property_exists($classOrObject, $property)) {
            static::reportException(\sprintf(
                $message ?: 'Expected the property %s to exist.',
                static::valueToString($property)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-param class-string|object $classOrObject
     *
     * @param string|object $classOrObject
     * @param mixed         $property
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function propertyNotExists($classOrObject, $property, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (\property_exists($classOrObject, $property)) {
            static::reportException(\sprintf(
                $message ?: 'Expected the property %s to not exist.',
                static::valueToString($property)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-param class-string|object $classOrObject
     *
     * @param string|object $classOrObject
     * @param mixed         $method
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function methodExists($classOrObject, $method, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!(\is_string($classOrObject) || \is_object($classOrObject)) || !\method_exists($classOrObject, $method)) {
            static::reportException(\sprintf(
                $message ?: 'Expected the method %s to exist.',
                static::valueToString($method)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-param class-string|object $classOrObject
     *
     * @param string|object $classOrObject
     * @param mixed         $method
     * @param string        $message
     *
     * @throws InvalidArgumentException
     */
    public static function methodNotExists($classOrObject, $method, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if ((\is_string($classOrObject) || \is_object($classOrObject)) && \method_exists($classOrObject, $method)) {
            static::reportException(\sprintf(
                $message ?: 'Expected the method %s to not exist.',
                static::valueToString($method)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param array      $array
     * @param string|int $key
     * @param string     $message
     *
     * @throws InvalidArgumentException
     */
    public static function keyExists($array, $key, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!(isset($array[$key]) || \array_key_exists($key, $array))) {
            static::reportException(\sprintf(
                $message ?: 'Expected the key %s to exist.',
                static::valueToString($key)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     *
     * @param array      $array
     * @param string|int $key
     * @param string     $message
     *
     * @throws InvalidArgumentException
     */
    public static function keyNotExists($array, $key, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (isset($array[$key]) || \array_key_exists($key, $array)) {
            static::reportException(\sprintf(
                $message ?: 'Expected the key %s to not exist.',
                static::valueToString($key)
            ), $exceptionClass);
        }
    }

    /**
     * Checks if a value is a valid array key (int or string).
     *
     * @psalm-pure
     * @psalm-assert array-key $value
     *
     * @param mixed  $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function validArrayKey($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!(\is_int($value) || \is_string($value))) {
            static::reportException(\sprintf(
                $message ?: 'Expected string or integer. Got: %s',
                static::typeToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int             $number
     * @param string          $message
     *
     * @throws InvalidArgumentException
     */
    public static function count($array, $number, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::eq(
            \count($array),
            $number,
            \sprintf(
                $message ?: 'Expected an array to contain %d elements. Got: %d.',
                $number,
                \count($array)
            ),
            $exceptionClass
        );
    }

    /**
     * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int|float       $min
     * @param string          $message
     *
     * @throws InvalidArgumentException
     */
    public static function minCount($array, $min, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (\count($array) < $min) {
            static::reportException(\sprintf(
                $message ?: 'Expected an array to contain at least %2$d elements. Got: %d',
                \count($array),
                $min
            ), $exceptionClass);
        }
    }

    /**
     * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int|float       $max
     * @param string          $message
     *
     * @throws InvalidArgumentException
     */
    public static function maxCount($array, $max, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (\count($array) > $max) {
            static::reportException(\sprintf(
                $message ?: 'Expected an array to contain at most %2$d elements. Got: %d',
                \count($array),
                $max
            ), $exceptionClass);
        }
    }

    /**
     * Does not check if $array is countable, this can generate a warning on php versions after 7.2.
     *
     * @param Countable|array $array
     * @param int|float       $min
     * @param int|float       $max
     * @param string          $message
     *
     * @throws InvalidArgumentException
     */
    public static function countBetween($array, $min, $max, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $count = \count($array);

        if ($count < $min || $count > $max) {
            static::reportException(\sprintf(
                $message ?: 'Expected an array to contain between %2$d and %3$d elements. Got: %d',
                $count,
                $min,
                $max
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert list $array
     *
     * @param mixed  $array
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isList($array, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (!\is_array($array)) {
            static::reportException($message ?: 'Expected list - non-associative array.', $exceptionClass);
        }

        if ($array === \array_values($array)) {
            return;
        }

        $nextKey = -1;
        foreach ($array as $k => $v) {
            if ($k !== ++$nextKey) {
                static::reportException($message ?: 'Expected list - non-associative array.', $exceptionClass);
            }
        }
    }

    /**
     * @psalm-pure
     * @psalm-assert non-empty-list $array
     *
     * @param mixed  $array
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isNonEmptyList($array, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::isList($array, $message, $exceptionClass);
        static::notEmpty($array, $message, $exceptionClass);
    }

    /**
     * @psalm-pure
     * @psalm-template T
     * @psalm-param mixed|array<T> $array
     * @psalm-assert array<string, T> $array
     *
     * @param mixed  $array
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isMap($array, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        if (
            !\is_array($array) ||
            \array_keys($array) !== \array_filter(\array_keys($array), '\is_string')
        ) {
            static::reportException(
                $message ?: 'Expected map - associative array with string keys.', $exceptionClass
            );
        }
    }

    /**
     * @psalm-pure
     * @psalm-template T
     * @psalm-param mixed|array<T> $array
     * @psalm-assert array<string, T> $array
     * @psalm-assert !empty $array
     *
     * @param mixed  $array
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function isNonEmptyMap($array, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        static::isMap($array, $message, $exceptionClass);
        static::notEmpty($array, $message, $exceptionClass);
    }

    /**
     * @psalm-pure
     *
     * @param string $value
     * @param string $message
     *
     * @throws InvalidArgumentException
     */
    public static function uuid($value, $message = '', string $exceptionClass = InvalidArgumentException::class)
    {
        $value = \str_replace(array('urn:', 'uuid:', '{', '}'), '', $value);

        // The nil UUID is special form of UUID that is specified to have all
        // 128 bits set to zero.
        if ('00000000-0000-0000-0000-000000000000' === $value) {
            return;
        }

        if (!\preg_match('/^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/', $value)) {
            static::reportException(\sprintf(
                $message ?: 'Value %s is not a valid UUID.',
                static::valueToString($value)
            ), $exceptionClass);
        }
    }

    /**
     * @psalm-param class-string<Throwable> $class
     *
     * @param Closure $expression
     * @param string  $class
     * @param string  $message
     *
     * @throws InvalidArgumentException
     */
    public static function throws(
        Closure $expression,
        $class = 'Exception',
        $message = '',
        string $exceptionClass = InvalidArgumentException::class
    ) {
        static::string($class);

        $actual = 'none';

        try {
            $expression();
        } catch (Exception $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        } catch (Throwable $e) {
            $actual = \get_class($e);
            if ($e instanceof $class) {
                return;
            }
        }

        static::reportException($message ?: \sprintf(
            'Expected to throw "%s", got "%s"',
            $class,
            $actual
        ), $exceptionClass);
    }

    /**
     * @throws BadMethodCallException
     */
    public static function __callStatic($name, $arguments)
    {
        if ('nullOr' === \substr($name, 0, 6)) {
            if (null !== $arguments[0]) {
                $method = \lcfirst(\substr($name, 6));
                \call_user_func_array(array(static::class, $method), $arguments);
            }

            return;
        }

        if ('all' === \substr($name, 0, 3)) {
            static::isIterable($arguments[0]);

            $method = \lcfirst(\substr($name, 3));
            $args = $arguments;

            foreach ($arguments[0] as $entry) {
                $args[0] = $entry;

                \call_user_func_array(array(static::class, $method), $args);
            }

            return;
        }

        throw new BadMethodCallException('No such method: '.$name);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected static function valueToString($value)
    {
        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (\is_array($value)) {
            return 'array';
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value).': '.self::valueToString($value->__toString());
            }

            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return \get_class($value).': '.self::valueToString($value->format('c'));
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (\is_string($value)) {
            return '"'.$value.'"';
        }

        return (string) $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    protected static function typeToString($value)
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }

    protected static function strlen($value)
    {
        if (!\function_exists('mb_detect_encoding')) {
            return \strlen($value);
        }

        if (false === $encoding = \mb_detect_encoding($value)) {
            return \strlen($value);
        }

        return \mb_strlen($value, $encoding);
    }

    /**
     * @throws \Exception
     *
     * @psalm-pure this method is not supposed to perform side-effects
     * @psalm-return never
     */
    protected static function reportException(string $message, string $exceptionClass = InvalidArgumentException::class)
    {
        self::isInstanceof(new $exceptionClass, \Exception::class);

        throw new $exceptionClass($message);
    }

    private function __construct()
    {
    }
}

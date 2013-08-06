<?php

namespace jafar;

/**
 * Thrown when a test assertion fails. Use an instanceof check to distinguish test errors
 * (unexpected exceptions) from failures (AssertionErrors that were thrown by some expectation).
 */
class AssertionError extends \LogicException {}

/**
 * The type of object returned by expect(). Use its public methods to make assertions about the
 * value.
 *
 * All public assertion methods are chainable.
 */
class ExpectationVerifier {
    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * Assert that the ExpectationVerifier's value is strictly equal (===) to $expected.
     */
    public function toBe($expected, $message=null) {
        return $this->checkBinary($this->value === $expected, '===', $expected, $message);
    }

    /**
     * Assert that the ExpectationVerifier's value is strictly unequal (!==) to $expected.
     */
    public function toNotBe($expected, $message=null) {
        return $this->checkBinary($this->value !== $expected, '!==', $expected, $message);
    }

    /**
     * Assert that the ExpectationVerifier's value is loosely equal (==) to $expected.
     */
    public function toEqual($expected, $message=null) {
        return $this->checkBinary($this->value == $expected, '==', $expected, $message);
    }

    /**
     * Assert that the ExpectationVerifier's value is loosely unequal (!=) to $expected.
     */
    public function toNotEqual($expected, $message=null) {
        return $this->checkBinary($this->value != $expected, '!=', $expected, $message);
    }

    /**
     * Assert that the ExpectationVerifier's value evaluates to true in a boolean context.
     *
     * This method is named after the English idiom "to ring true":
     * http://en.wiktionary.org/wiki/ring_true
     */
    public function toRingTrue($message=null) {
        return $this->check($this->value, $message ?: "Expected {actual} to evaluate to true");
    }

    /**
     * Assert that the ExpectationVerifier's value evaluates to false in a boolean context.
     */
    public function toRingFalse($message=null) {
        return $this->check(!$this->value, $message ?: "Expected {actual} to evaluate to false");
    }

    /**
     * Assert that the given function throws an exception which is an instance of the class
     * named in the ExpectationVerifier's value.
     *
     * Example:
     *
     *   expect('ReflectionException')->toBeThrownFrom(function() {
     *       new \ReflectionFunction('some_nonexistent_function');
     *   });
     */
    public function toBeThrownFrom(callable $fn) {
        if (!$this->value || !class_exists($this->value)) {
            throw new \LogicException('No such class: ' . static::inspect($this->value) . '.');
        }

        $e = null;

        try {
            $fn();
        } catch (\Exception $e) {
            // drop down
        }

        if ($e && !is_a($e, $this->value)) {
            throw new AssertionError(
                'Block threw an unexpected ' . get_class($e) . ' exception.',
                0,
                $e
            );
        } else if (!$e) {
            throw new AssertionError("Expected block to throw a {$this->value} exception.");
        }

        return $this;
    }

    /**
     * Assert that the given function does not throw an exception which is an instance of the class
     * named in the ExpectationVerifier's value.
     *
     * Example:
     *
     *   expect('ReflectionException')->toNotBeThrownFrom(function() {
     *       new \ReflectionFunction('function_exists');
     *   });
     */
    public function toNotBeThrownFrom(callable $fn) {
        if (!$this->value || !class_exists($this->value)) {
            throw new \LogicException('No such class: ' . static::inspect($this->value) . '.');
        }

        try {
            $fn();
        } catch (\Exception $e) {
            if (is_a($e, $this->value)) {
                throw new AssertionError(
                    "Block should not have thrown a {$this->value} exception, but it did."
                );
            } else {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Assert that the ExpectationVerifier's value is an object and an instance of the given class.
     */
    public function toBeA($class) {
        if (!is_object($this->value)) {
            $this->raise('Expected {actual} to be an object.', null);
        } else if (!is_a($this->value, $class)) {
            $this->raise('Expected {actual} to be a(n) {expected} object.', $class);
        }

        return $this;
    }

    /**
     * Exactly the same as toBeA, but permits correct grammar in the face of a class name which
     * begins with a vowel.
     */
    public function toBeAn($class) {
        return $this->toBeA($class);
    }

    /**
     * Asserts that the ExpectationVerifier's value is an array or an Iterator or IteratorAggregate
     * object.
     */
    public function toBeIterable() {
        $is_traversable = (
            is_array($this->value) ||
            (is_object($this->value) && $this->value instanceof \Traversable)
        );

        if (!$is_traversable) {
            $this->raise(
                'Expected {actual} to be an array or an Iterator or IteratorAggregate object.',
                null
            );
        }

        return true;
    }

    /**
     * Internal: Helper for implementing assertions based on a binary comparison.
     */
    private function checkBinary($condition, $operator, $expected) {
        return $this->check($condition, "Expected {actual} $operator {expected}", $expected);
    }

    /**
     * Internal: Helper for implementing assertions that deal with an expected value.
     */
    private function check($condition, $message, $expected=null) {
        if (!$condition) {
            $this->raise($message, $expected);
        }
        return $this;
    }

    /**
     * Internal: Helper for generating error messages that deal with an expected value.
     */
    private function raise($message, $expected) {
        $message = str_replace(
            ['{actual}', '{expected}'],
            [static::inspect($this->value), static::inspect($expected)],
            $message
        );

        throw new AssertionError($message);
    }

    /**
     * Internal: Builds a nice string representation of a value.
     */
    public static function inspect($value) {
        if (is_object($value)) {
            if (is_callable([$value, '__toString'])) {
                return '(' . get_class($value) . " $value)";
            } else if ($value instanceof DateTime) {
                return '(' . get_class($value) . ' ' . $value->format(DateTime::ATOM) . ')';
            } else {
                return '(' . get_class($value) . ' object)';
            }
        } else if (is_array($value)) {
            if (static::isAssociativeArray($value)) {
                $parts = [];

                foreach ($value as $key => $element) {
                    $parts[] = static::inspect($key) . ': ' . static::inspect($value);
                }

                return '{' . implode(', ', $parts) . '}';
            } else {
                return (
                    '[' .
                    implode(', ', array_map(get_called_class() . '::inspect', $value)) .
                    ']'
                );
            }
        } else if (is_string($value)) {
            return json_encode($value);
        } else {
            return var_export($value, true);
        }
    }

    private static function isAssociativeArray(array $array) {
        $i = 0;

        foreach ($array as $key => $value) {
            if ($key !== $i) {
                return true;
            }

            $i++;
        }

        return false;
    }

    public function __toString() {
        return get_class($this) . '(' . static::inspect($this->value) . ')';
    }
}

function get_current_suite() {
    $stack = $GLOBALS['_jafar_suite_stack'];
    return $stack ? $stack[count($stack) - 1] : null;
}

function require_current_suite() {
    $suite = get_current_suite();
    if (!$suite) {
        throw new \LogicException('This function is only valid inside a describe().');
    }
    return $suite;
}

function define_suite($name, callable $body) {
    $top = get_current_suite();
    $new = new Suite($name);

    $GLOBALS['_jafar_suite_stack'][] = $new;

    if ($top) {
        $top->contents[] = $new;
    }

    $body();

    array_pop($GLOBALS['_jafar_suite_stack']);

    if (!$GLOBALS['_jafar_suite_stack']) {
        $GLOBALS['_jafar_complete_suites'][] = $new;
    }
}

function get_suites_from_file($test_path) {
    include $test_path;

    $suites = $GLOBALS['_jafar_complete_suites'];
    $GLOBALS['_jafar_complete_suites'] = [];

    return $suites;
}

interface Listener {
    public function before(array $stack);
    public function after(array $stack, \Exception $error=null);
}

interface Runnable {
    public function run(array $stack, array $context, Listener $listener);
}

class Suite implements Runnable {
    public $name;
    public $before = [];
    public $contents = [];
    public $after = [];

    public function __construct($name) {
        $this->name = $name;
    }

    public function run(array $stack, array $context, Listener $listener) {
        $stack = array_merge($stack, [$this]);
        $err = null;

        $listener->before($stack);

        try {
            foreach ($this->contents as $content) {
                $test_context = $context;

                foreach ($this->before as $before) {
                    $test_context = array_merge(
                        $test_context,
                        apply_by_name($before, $test_context)
                    );
                }

                $content->run($stack, $test_context, $listener);

                foreach ($this->after as $after) {
                    apply_by_name($after, $test_context);
                }
            }
        } catch (\Exception $err) {
            // fall through
        }

        $listener->after($stack, $err);
    }
}

class Test implements Runnable {
    public $name;
    public $implementation;

    public function __construct($name, callable $implementation) {
        $this->name = $name;
        $this->implementation = $implementation;
    }

    public function run(array $stack, array $context, Listener $listener) {
        $stack = array_merge($stack, [$this]);
        $err = null;

        $listener->before($stack);
        try {
            apply_by_name($this->implementation, $context);
        } catch (\Exception $err) {
            // fall through
        }
        $listener->after($stack, $err);
    }
}

function apply_by_name(callable $fn, array $context) {
    $reflector = new \ReflectionFunction($fn);
    $args = [];

    foreach ($reflector->getParameters() as $param) {
        $name = $param->getName();
        if (!array_key_exists($name, $context)) {
            throw new \LogicException("{$this->name} needs a $name parameter.");
        }
        $args[] = $context[$name];
    }

    return call_user_func_array($fn, $args);
}

<?php

/**
 * Jafar: Behavior testing for PHP which is quite similar to Jasmine.
 */

require_once 'jafar/core.php';

// A dash of magic: the stack of suites created by nested describe() calls is stored here.
$GLOBALS['_jafar_suite_stack'] = [];

// Another dash of magic: completed suites are stored here.
$GLOBALS['_jafar_complete_suites'] = [];

/**
 * Open a test suite by declaring what you will be describing ($name) and how to test it 
 * ($definition).
 *
 * Calls to describe may be nested to describe individual parts of an overall thing.
 */
function describe($name, callable $definition) {
    jafar\define_suite($name, $definition);
}

/**
 * Open a test within a suite, by declaring the behavior being tested ($name) and how to test it
 * ($definition).
 *
 * The $definition function may have parameters. If it does, the function will be passed the values
 * returned from before() functions whose array key matches the parameter's name.
 *
 * Only valid within a describe() definition.
 */
function it($name, callable $definition) {
    $suite = jafar\require_current_suite();
    $suite->contents[] = new jafar\Test($name, $definition);
}

/**
 * Returns an object (a jafar\ExpectationVerifier) which can be used to verify aspects of the given 
 * value.
 */
function expect($value) {
    return new jafar\ExpectationVerifier($value);
}

/**
 * Shorthand for verifying that a condition is true.
 *
 * Use check() where you would be otherwise tempted to use assert().
 */
function check($condition, $message=null) {
    expect($condition)->toRingTrue($message);
}

/**
 * Define a function which will be called prior to each test (it) in the suite (describe).
 *
 * In order to conveniently pass values to test functions, before() functions may return an
 * associative array. Test (it) functions which have parameters will be called with the matching
 * values from the merger (array_merge) of all arrays returned from the suite's before()s, or
 * from before()s in any parent or ancestor suites (if the current describe is nested within
 * another).
 *
 * Only valid within a describe() definition.
 */
function before(callable $implementation) {
    jafar\require_current_suite()->beforeEach[] = $implementation;
}

/**
 * Define a function which will be called after each test (it) in the suite (describe).
 *
 * after() functions may also have parameters in the same way that it() functions can, and
 * they will be populated with arguments in the same way.
 */
function after(callable $implementation) {
    jafar\require_current_suite()->afterEach[] = $implementation;
}

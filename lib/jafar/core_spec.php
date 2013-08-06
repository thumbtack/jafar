<?php

use jafar\AssertionError;

describe('Expectations', function() {
    it('can assert that an exception is thrown', function() {
        $thrown = false;

        try {
            expect('RuntimeException')->toBeThrownFrom(function() {
                return;
            });
        } catch (AssertionError $e) {
            $thrown = true;
        }

        if (!$thrown) {
            throw new AssertionError('expect() should have thrown an AssertionError.');
        }

        expect('RuntimeException')->toBeThrownFrom(function() {
            throw new \RuntimeException('should be caught by Jafar');
        });
    });

    it('can assert that an exception is not thrown', function() {
        $thrown = false;

        try {
            expect('RuntimeException')->toNotBeThrownFrom(function() {
                throw new \RuntimeException('should be caught by Jafar');
            });
        } catch (AssertionError $e) {
            $thrown = true;
        }

        if (!$thrown) {
            throw new AssertionError('expect() should have thrown an AssertionError.');
        }

        expect('RuntimeException')->toNotBeThrownFrom(function() {
            return;
        });
    });

    it('can assert that actual === expected', function() {
        expect(5)->toBe(5);
        expect('a')->toBe('a');
        expect([1, 2, 3])->toBe([1, 2, 3]);
        expect(false)->toBe(false);
        expect(null)->toBe(null);

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(5)->toBe('5');
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(0)->toBe(false);
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect('a')->toBe('e');
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect([1, 2, 3])->toBe(['1', '2', '3']);
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(false)->toBe(null);
        });
    });

    it('can assert that actual !== expected', function() {
        expect('1')->toNotBe('a');
        expect('1')->toNotBe(1);

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(1)->toNotBe(1);
        });
    });

    it('can assert that actual == expected', function() {
        expect('1')->toEqual('1');
        expect('1')->toEqual(1);
        expect(false)->toEqual(null);

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect('a')->toEqual('b');
        });
    });

    it('can assert that actual != expected', function() {
        expect('a')->toNotEqual('z');
        expect(true)->toNotEqual(false);

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(1)->toNotEqual('1');
        });
    });

    it('can assert truthiness', function() {
        expect(true)->toRingTrue();
        expect(1)->toRingTrue();
        expect('a')->toRingTrue();
        expect([false])->toRingTrue();
        expect(new \stdClass)->toRingTrue();

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(false)->toRingTrue();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(null)->toRingTrue();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(0)->toRingTrue();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect('')->toRingTrue();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect([])->toRingTrue();
        });
    });

    it('can assert falsehood', function() {
        expect(false)->toRingFalse();
        expect(null)->toRingFalse();
        expect(0)->toRingFalse();
        expect('')->toRingFalse();
        expect([])->toRingFalse();

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(true)->toRingFalse();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect('a')->toRingFalse();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(1)->toRingFalse();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect([0])->toRingFalse();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(new \stdClass)->toRingFalse();
        });
    });

    it('can assert class membership', function() {
        expect(new \stdClass)->toBeA('stdClass');

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(new \stdClass)->toBeAn('Exception');
        });
    });

    it('can assert iterability', function() {
        expect([])->toBeIterable();
        expect(new \ArrayObject)->toBeIterable();

        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect(12)->toBeIterable();
        });
        expect('jafar\AssertionError')->toBeThrownFrom(function() {
            expect('foo')->toBeIterable();
        });
    });
});

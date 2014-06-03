Jafar
=====

Jafar is a behavior testing toolkit for PHP, like [Jasmine][jasmine] is for
JavaScript.

[jasmine]: http://jasmine.github.io/

Usage
-----

```php
<?php

require_once 'jafar.php';

describe('Exception', function() {
    it('has a message', function() {
        $message = 'Oops!';
        expect(new \Exception($message)->getMessage())->toBe($message);
    });

    it('can be thrown', function() {
        expect('RuntimeException')->toBeThrownFrom(function() {
            throw new RuntimeException();
        });
    });
});
```

License
-------

Jafar is distributed under the terms of the 3-clause BSD license.
See [COPYING](COPYING).

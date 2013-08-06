<?php

namespace jafar;

/**
 * Reports test results to the terminal.
 */
class TerminalReporter implements Listener {
    private $charset;
    private $color;

    public function __construct($charset, $color) {
        $this->charset = $charset;
        $this->color = $color;
    }

    public function before(array $stack) {
        $length = count($stack);
        $top = $stack[$length - 1];

        echo $this->indent($length - 1) . $stack[$length - 1]->name;

        if ($top instanceof Suite) {
            echo "\n";
        }
    }

    public function after(array $stack, \Exception $error=null) {
        $length = count($stack);
        $top = $stack[$length - 1];

        if ($top instanceof Test) {
            if ($error === null) {
                echo $this->color('green', $this->marker('pass')) . "\n";
            } else {
                if ($error instanceof AssertionError) {
                    echo $this->color('yellow', $this->marker('fail')) . "\n";
                    echo $this->indent($length) .
                        $this->color('yellow', $error->getMessage())
                        . "\n";
                } else {
                    echo $this->color('red', $this->marker('error')) . "\n";
                    echo $this->indent($length) .
                        $this->color(
                            'red',
                            '[' . get_class($error) . ']: ' .  $error->getMessage()
                        ) .
                        "\n";

                    echo implode(
                        "\n",
                        array_map(
                            function($line) use ($length) {
                                return $this->indent($length) . $line;
                            },
                            explode("\n", $error->getTraceAsString())
                        )
                    );
                    echo "\n"; // stack trace does not end in a newline
                }
            }
        }
    }

    private function indent($count) {
        return str_repeat('  ', $count);
    }

    private function color($color, $text) {
        return $this->color
            ? static::$colors[$color] . $text . static::$colors['reset']
            : $text;
    }

    private function marker($key) {
        return static::$success_markers[$this->charset][$key];
    }

    private static $success_markers = [
        'utf-8' => [
            'pass' => " \xe2\x9c\x94", // HEAVY CHECK MARK
            'fail' => " \xe2\x9c\x98", // HEAVY BALLOT X
            'error' => ' [ERROR]',
        ],
        'ascii' => [
            'pass' => ': ok',
            'fail' => ': fail',
            'error' => ': ERROR',
        ],
    ];

    private static $colors = [
        'reset' => "\033[0m",
        'red' => "\033[1;31m",
        'yellow' => "\033[1;33m",
        'green' => "\033[32m",
    ];
}

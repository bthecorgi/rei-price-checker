<?php

namespace PriceChecker;


abstract class Colorizer {

    const GREEN = '32';
    const YELLOW = '33';
    const MAGENTA = '35';
    const WHITE = '37';

    /** @var bool */
    private $shouldColorize;

    public function __construct($shouldColorize) {
        $this->shouldColorize = $shouldColorize;
    }

    protected function colorize($string, $color) {
        if (!$this->shouldColorize) {
            return $string;
        }
        return "\033[0;" . $color . "m" . $string . "\033[0m";
    }
}

?>
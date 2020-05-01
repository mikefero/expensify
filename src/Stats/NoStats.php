<?php

namespace Expensify\Bedrock\Stats;

class NoStats implements StatsInterface {
    public function counter($name, $value = 1) { }
    public function timer($name, $value) { }
    public function benchmark($name, callable $function) {
        return $function();
    }
}
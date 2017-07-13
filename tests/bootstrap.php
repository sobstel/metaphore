<?php
require __DIR__."/../vendor/autoload.php";

// phpunit backward compatibility
if (!class_exists('\PHPUnit\Framework\TestCase') && class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit\Framework\TestCase', '\PHPUnit\Framework\TestCase');
}

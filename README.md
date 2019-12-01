# Asynchronus Function Call in PHP

This library provides a capability for invoke functions asynchrounsly in PHP. It's based on pure PHP thus you do not need any other liberaries or extensions.

## Installation

Just copy php-async.php file in your source code!!

## Usage

```php
require_once "php-async.php"
use \saman\core;

runtime::callAsync(function() {
  // Do async 
});

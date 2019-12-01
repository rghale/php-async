# Asynchronus Function Call in PHP

This library provides a capability for invoke functions asynchrounsly in PHP. It's based on pure PHP thus you do not need any other liberaries or extensions.

## Installation

Just put php-async.php file in your source code!!

## Usage

```php
require_once "php-async.php"
use \saman\core;

runtime::callAsync(function() {
  // Run async 
});
```

### Pass parameters
```php
require_once "php-async.php"
use \saman\core;

$param1 = '';
$param2 = '';

runtime::callAsync(function() use ($param1, $param2) {
  // You can use $param1 and $param2 here 
});
```

### Event listeners
```php
require_once "php-async.php"
use \saman\core;

runtime::callAsync(function() {
  // Run async 
})->done(function($output) {
  // After successful run
})->exception(function(Exception $ex) {
  // When any exceptions raised
});
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

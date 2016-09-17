PSX V8
===

## About

This is a small helper library which simplifies working with the PHP V8 
extension (https://github.com/pinepain/php-v8). It adds small wrapper classes
which help with the type juggling between PHP and the V8 environment.

## Usage

```php
<?php

$script = <<<JS

var message = 'Hello ' + console.foo;

resp = {
    message: console.log(message),
    bar: function(data){
        return 'Foo ' + data;
    },
    foo: 'bar'
};

JS;

$environment = new \PSX\V8\Environment();

$environment->set('console', [
    'foo' => 'foo',
    'log' => function($value){
         return $value . ' and bar';
    }
]);

$environment->run($script);

$resp = $environment->get('resp');

echo $resp->get('message') . "\n"; // Hello foo and bar
echo $resp->get('bar')(['test']) . "\n"; // Foo test
echo $resp->get('foo') . "\n"; // bar

```

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

### Objects

It is also possible to expose complete PHP classes to the V8 engine. Therefor
the class has to implement the `PSX\V8\ObjectInterface` interface. There is also 
a `PSX\V8\ReflectionObject` object which automatically exposes all public 
properties and methods. The javascript defined above would also work with the 
following environment:

```php
<?php

class Console
{
    public $foo;
    
    public function __construct()
    {
        $this->foo = 'foo';
    }
    
    public function log($value)
    {
        return $value . ' and bar';
    }
}

$console = new Console();

$environment = new \PSX\V8\Environment();
$environment->set('console', new \PSX\V8\Object\ReflectionObject($console));
$environment->run($script);

$resp = $environment->get('resp');

```


[![Build Status](https://travis-ci.org/apioo/psx-v8.png)](https://travis-ci.org/apioo/psx-v8)

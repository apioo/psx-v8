<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace PSX\V8\Tests;

use PSX\V8\Environment;
use PSX\V8\Object\ReflectionObject;
use PSX\V8\Tests\Object\Foo;
use PSX\V8\Wrapper\ArrayWrapper;
use PSX\V8\Wrapper\ObjectWrapper;

/**
 * EnvironmentTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('V8\Context')) {
            $this->markTestSkipped('V8 extension not installed');
        }
    }

    public function testRun()
    {
        $script = <<<JS

var message = md5('foobar');

resp = console.log(message);

JS;

        $env = new Environment();
        $env->set('md5', function($value){
            return md5($value);
        });
        $env->set('console', [
            'log' => function($message){
                return $message;
            }
        ]);

        $env->run($script);

        $this->assertEquals(md5('foobar'), $env->get('resp'));
    }

    public function testRunObject()
    {
        $script = <<<JS

var message = console.foo;

resp = console.doFoo(message);

JS;

        $env = new Environment();
        $env->set('console', new ReflectionObject(new Foo()));

        $env->run($script);

        $this->assertEquals('foo: foo', $env->get('resp'));
    }

    public function testJsonResponse()
    {
        $script = <<<JS

function buildResponse() {
  return {
    totalResults: 10,
    entry: [{
      value_boolean: true,
      value_null: null,
      value_integer: 12,
      value_float: 12.34,
      value_string: "foo"
    },{
      object_boolean: new Boolean(false),
      object_string: new String("foo"),
      object_integer: new Number(12),
      object_float: new Number(12.34),
      object_regexp: /^[A-z]+$/,
      object_date: new Date(2017, 1, 9),
      object_array: ["foo", "bar"],
      object_object: {foo: "bar"}
    }]
  };
}

resp = buildResponse();

JS;

        $env = new Environment();
        $env->run($script);

        $actual = json_encode($env->get('resp')->toNative(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    /**
     * @dataProvider providerPrimitiveTypes
     */
    public function testPrimitiveTypes($js, $php, $type)
    {
        $env = new Environment();
        $env->run('data = ' . $js . ';');

        $data = $env->get('data');

        $this->assertInternalType($type, $data);
        $this->assertSame($php, $data);
    }

    public function providerPrimitiveTypes()
    {
        return [
            ['true', true, 'boolean'],
            ['null', null, 'null'],
            ['12', 12, 'integer'],
            ['12.34', 12.34, 'float'],
            ['"foo"', 'foo', 'string'],
            ['new Boolean(false)', false, 'boolean'],
            ['new String("foo")', 'foo', 'string'],
            ['new Number(12)', 12, 'integer'],
            ['new Number(12.34)', 12.34, 'float'],
            ['/^[A-z]+$/', '^[A-z]+$', 'string'],
        ];
    }

    /**
     * @dataProvider providerObjectTypes
     */
    public function testObjectTypes($js, $php, $type, \Closure $transformer)
    {
        $env = new Environment();
        $env->run('data = ' . $js . ';');

        $data = $env->get('data');

        $this->assertInstanceOf($type, $data);
        $this->assertEquals($php, $transformer($data));
    }

    public function providerObjectTypes()
    {
        return [
            ['new Date(2017, 1, 9)', '2017-02-09', \DateTime::class, function(\DateTime $data) { return $data->format('Y-m-d'); } ],
            ['["foo", "bar"]', ['foo', 'bar'], ArrayWrapper::class, function(ArrayWrapper $data) { return $data->toNative(); } ],
            ['{foo: "bar"}', (object) ['foo' => 'bar'], ObjectWrapper::class, function(ObjectWrapper $data) { return $data->toNative(); } ],
        ];
    }
}

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

    /**
     * @dataProvider providerDataTypes
     */
    public function testDataTypes($js, $php, $type, \Closure $transformer = null)
    {
        $env = new Environment();
        $env->run('data = ' . $js . ';');

        $data = $env->get('data');

        $this->assertInternalType($type, $data);

        if ($transformer !== null) {
            $data = $transformer($data);
        }

        $this->assertSame($php, $data);
    }

    public function providerDataTypes()
    {
        return [
            ['true', true, 'boolean'],
            ['null', null, 'NULL'],
            ['12', 12, 'integer'],
            ['12.34', 12.34, 'float'],
            ['"foo"', 'foo', 'string'],
            ['new Boolean(false)', false, 'boolean'],
            ['new String("foo")', 'foo', 'string'],
            ['new Number(12)', 12, 'integer'],
            ['new Number(12.34)', 12.34, 'float'],
            ['new Date(2017, 2, 9)', '2017-02-09', 'object', function(\DateTime $date) { return $date->format('Y-m-d'); } ],
            ['/^[A-z]+$/', '/^[A-z]+$/', 'string'],
            ['["foo", "bar"]', ['foo', 'bar'], 'array'],
            ['{foo: "bar"}', (object) ['foo' => 'bar'], 'object'],
        ];
    }
}

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

use PSX\V8\Encoder;
use PSX\V8\Tests\Data\Json;
use PSX\V8\Tests\Data\Popo;
use PSX\V8\Tests\Data\ToString;
use PSX\V8\Tests\Data\Traversable;
use PSX\V8\Tests\Object\Bar;
use V8\ArrayObject;
use V8\BooleanValue;
use V8\Context;
use V8\DateObject;
use V8\FunctionObject;
use V8\IntegerValue;
use V8\Isolate;
use V8\NullValue;
use V8\NumberValue;
use V8\ObjectValue;
use V8\StringValue;

/**
 * EncoderTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class EncoderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('V8\Context')) {
            $this->markTestSkipped('V8 extension not installed');
        }
    }

    public function testEncode()
    {
        $isolate = new Isolate();
        $context = new Context($isolate);

        $this->assertInstanceOf(BooleanValue::class, Encoder::encode(true, $context));
        $this->assertSame(true, Encoder::encode(true, $context)->Value());
        $this->assertInstanceOf(StringValue::class, Encoder::encode('foo', $context));
        $this->assertSame('foo', Encoder::encode('foo', $context)->Value());
        $this->assertInstanceOf(NumberValue::class, Encoder::encode(12.34, $context));
        $this->assertSame(12.34, Encoder::encode(12.34, $context)->Value());
        $this->assertInstanceOf(IntegerValue::class, Encoder::encode(12, $context));
        $this->assertSame(12, Encoder::encode(12, $context)->Value());
        $this->assertInstanceOf(NullValue::class, Encoder::encode(null, $context));
        $this->assertSame(null, Encoder::encode(null, $context)->Value());
        $this->assertInstanceOf(DateObject::class, Encoder::encode(new \DateTime('2016-09-17T00:00:00'), $context));
        $this->assertSame(1474070400000.0, Encoder::encode(new \DateTime('2016-09-17T00:00:00'), $context)->ValueOf());
        $this->assertInstanceOf(ObjectValue::class, Encoder::encode(new Bar(), $context));
        $this->assertInstanceOf(FunctionObject::class, Encoder::encode(function() {}, $context));
        $this->assertInstanceOf(ObjectValue::class, Encoder::encode(new \ArrayObject(['foo' => 'bar']), $context));
        $this->assertInstanceOf(ArrayObject::class, Encoder::encode(new \ArrayObject(['foo', 'bar']), $context));
        $this->assertInstanceOf(ObjectValue::class, Encoder::encode(new Traversable(['foo' => 'bar']), $context));
        $this->assertInstanceOf(ArrayObject::class, Encoder::encode(new Traversable(['foo', 'bar']), $context));
        $this->assertInstanceOf(ObjectValue::class, Encoder::encode(new Json(['foo' => 'bar']), $context));
        $this->assertInstanceOf(ArrayObject::class, Encoder::encode(new Json(['foo', 'bar']), $context));
        $this->assertInstanceOf(ObjectValue::class, Encoder::encode((object) ['foo' => 'bar'], $context));
        $this->assertInstanceOf(ObjectValue::class, Encoder::encode(['foo' => 'bar'], $context));
        $this->assertInstanceOf(ArrayObject::class, Encoder::encode(['foo', 'bar'], $context));
        $this->assertInstanceOf(ArrayObject::class, Encoder::encode([], $context));
        $this->assertInstanceOf(StringValue::class, Encoder::encode(new ToString(), $context));
        $this->assertSame('foo', Encoder::encode(new ToString(), $context)->Value());
        $this->assertInstanceOf(NullValue::class, Encoder::encode(new Popo(), $context));
    }

    public function testEncodeObject()
    {
        $isolate = new Isolate();
        $context = new Context($isolate);

        /** @var ObjectValue $object */
        $object = Encoder::encode(new Bar(), $context);

        $this->assertInstanceOf(ObjectValue::class, $object);

        $property = $object->Get($context, Encoder::encode('bar', $context));
        $this->assertInstanceOf(StringValue::class, $property);

        $method = $object->Get($context, Encoder::encode('doBar', $context));
        $this->assertInstanceOf(FunctionObject::class, $method);
    }
}

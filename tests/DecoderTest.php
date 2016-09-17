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

use PSX\V8\Decoder;
use V8\ArrayObject;
use V8\BooleanObject;
use V8\BooleanValue;
use V8\Context;
use V8\DateObject;
use V8\IntegerValue;
use V8\Isolate;
use V8\NullValue;
use V8\NumberObject;
use V8\NumberValue;
use V8\ObjectValue;
use V8\RegExpObject;
use V8\StringObject;
use V8\StringValue;

/**
 * DecoderTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class DecoderTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('V8\Context')) {
            $this->markTestSkipped('V8 extension not installed');
        }
    }

    public function testDecode()
    {
        $isolate = new Isolate();
        $context = new Context($isolate);

        $this->assertSame(true, Decoder::decode(new BooleanValue($isolate, true), $context));
        $this->assertSame('foo', Decoder::decode(new StringValue($isolate, 'foo'), $context));
        $this->assertSame(12.34, Decoder::decode(new NumberValue($isolate, 12.34), $context));
        $this->assertSame(12, Decoder::decode(new IntegerValue($isolate, 12), $context));
        $this->assertSame(null, Decoder::decode(new NullValue($isolate), $context));
        $this->assertInstanceOf(\DateTime::class, Decoder::decode(new DateObject($context, 1474070400000.0), $context));
        $this->assertInstanceOf('2016-09-17T00:00:00', Decoder::decode(new DateObject($context, 1474070400000.0), $context)->format('Y-m-dTH:i:s'));
        $this->assertSame(false, Decoder::decode(new BooleanObject($context, false), $context));
        $this->assertSame('bar', Decoder::decode(new StringObject($context, new StringValue($isolate, 'bar')), $context));
        $this->assertSame(12.34, Decoder::decode(new NumberObject($context, 12.34), $context));
        $this->assertSame(12, Decoder::decode(new NumberObject($context, 12), $context));
        $this->assertSame('[A-z]', Decoder::decode(new RegExpObject($context, new StringValue($isolate, '[A-z]')), $context));
        $this->assertSame(['foo', 'bar'], Decoder::decode($this->getArray($context), $context));
        $this->assertSame((object) ['foo' => 'bar'], Decoder::decode($this->getObject($context), $context));
    }

    protected function getArray(Context $context)
    {
        $result = new ArrayObject($context);
        $result->SetIndex($context, 0, new StringValue($context->GetIsolate(), 'foo'));
        $result->SetIndex($context, 1, new StringValue($context->GetIsolate(), 'bar'));

        return $result;
    }

    protected function getObject(Context $context)
    {
        $result = new ObjectValue($context);
        $result->Set(
            $context, 
            new StringValue($context->GetIsolate(), 'foo'), 
            new StringValue($context->GetIsolate(), 'bar')
        );

        return $result;
    }
}

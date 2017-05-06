<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace PSX\V8\Tests\Object;

use PSX\V8\Encoder;
use PSX\V8\Object\ReflectionObject;
use V8\Context;
use V8\FunctionObject;
use V8\Isolate;
use V8\ObjectValue;
use V8\StringValue;

/**
 * ReflectionObjectTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ReflectionObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testGetProperties()
    {
        $isolate = new Isolate();
        $context = new Context($isolate);

        /** @var ObjectValue $object */
        $object = Encoder::encode(new ReflectionObject(new Foo()), $context);
        $this->assertInstanceOf(ObjectValue::class, $object);

        $property = $object->Get($context, Encoder::encode('foo', $context));
        $this->assertInstanceOf(StringValue::class, $property);

        $method = $object->Get($context, Encoder::encode('doFoo', $context));
        $this->assertInstanceOf(FunctionObject::class, $method);
    }
}

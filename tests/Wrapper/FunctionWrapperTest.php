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

namespace PSX\V8\Tests\Wrapper;

use PSX\Framework\Dependency\ObjectBuilder;
use PSX\V8\Environment;
use PSX\V8\Wrapper\ArrayWrapper;
use PSX\V8\Wrapper\FunctionWrapper;
use PSX\V8\Wrapper\ObjectWrapper;

/**
 * FunctionWrapperTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class FunctionWrapperTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('V8\Context')) {
            $this->markTestSkipped('V8 extension not installed');
        }
    }

    public function testObjectWrapper()
    {
        $script = <<<JS

function buildResponse() {
  return function(bar){
    return 'foo ' + bar;
  };
}

resp = buildResponse();

JS;

        $env = new Environment();
        $env->run($script);

        /** @var FunctionWrapper $data */
        $data = $env->get('resp');

        $this->assertInstanceOf(FunctionWrapper::class, $data);

        $callback = $data->toNative();
        $this->assertInstanceOf(\Closure::class, $callback);
        $this->assertEquals('foo bar', $callback('bar'));

        $this->assertEquals('foo bar', $data('bar'));
    }
}

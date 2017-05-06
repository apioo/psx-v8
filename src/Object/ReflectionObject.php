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

namespace PSX\V8\Object;

use InvalidArgumentException;
use PSX\V8\ObjectInterface;

/**
 * ReflectionObject
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ReflectionObject implements ObjectInterface
{
    /**
     * @var object
     */
    protected $object;

    public function __construct($object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException('First parameter must be an object');
        }

        $this->object = $object;
    }

    public function getProperties()
    {
        $reflection = new \ReflectionClass($this->object);
        $properties = [];

        // properties
        $props = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $property) {
            $name = $property->getName();
            $properties[$name] = $this->object->{$name};
        }

        // methods
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $name = $method->getName();
            $properties[$name] = [$this->object, $name];
        }

        return $properties;
    }
}

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

namespace PSX\V8\Wrapper;

use PSX\V8\Encoder;
use V8\Context;
use V8\ObjectValue;

/**
 * ObjectWrapper
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ObjectWrapper extends ValueWrapper implements \IteratorAggregate
{
    /**
     * @var ObjectValue
     */
    protected $value;

    public function __construct(Context $context, ObjectValue $value)
    {
        parent::__construct($context, $value);
    }

    public function set($name, $value)
    {
        return $this->value->set(
            $this->context,
            Encoder::encode($name, $this->context),
            Encoder::encode($value, $this->context)
        );
    }

    public function get($name)
    {
        $return = $this->value->get(
            $this->context,
            Encoder::encode($name, $this->context)
        );

        return $this->wrapValue($return);
    }

    public function has($name)
    {
        return $this->value->has(
            $this->context, 
            Encoder::encode($name, $this->context)
        );
    }

    public function delete($name)
    {
        return $this->value->delete(
            $this->context,
            Encoder::encode($name, $this->context)
        );
    }

    public function getIterator()
    {
        return new ObjectIterator($this->context, $this->value);
    }
}

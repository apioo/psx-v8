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

use PSX\V8\Decoder;
use V8\ArrayObject;
use V8\BooleanObject;
use V8\Context;
use V8\DateObject;
use V8\FunctionObject;
use V8\NumberObject;
use V8\ObjectValue;
use V8\RegExpObject;
use V8\StringObject;
use V8\Value;

/**
 * ValueWrapper
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ValueWrapper
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Value
     */
    protected $value;

    public function __construct(Context $context, Value $value)
    {
        $this->context = $context;
        $this->value = $value;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the PHP value from the V8 value
     *
     * @return \stdClass
     */
    public function toNative()
    {
        return Decoder::decode($this->value, $this->context);
    }

    protected function wrapValue(Value $value)
    {
        if ($value instanceof ArrayObject) {
            return new ArrayWrapper($this->context, $value);
        } elseif ($value instanceof FunctionObject) {
            return new FunctionWrapper($this->context, $value);
        } elseif ($value instanceof DateObject) {
            return new \DateTime('@' . intval($value->ValueOf() / 1000));
        } elseif ($value instanceof BooleanObject) {
            return $value->ValueOf();
        } elseif ($value instanceof StringObject) {
            return $value->ValueOf()->Value();
        } elseif ($value instanceof NumberObject) {
            $val = $value->ValueOf();
            return strpos($val, '.') === false ? intval($val) : $val;
        } elseif ($value instanceof RegExpObject) {
            return $value->GetSource()->Value();
        } elseif ($value instanceof ObjectValue) {
            return new ObjectWrapper($this->context, $value);
        } else {
            return Decoder::decode($value, $this->context);
        }
    }
}

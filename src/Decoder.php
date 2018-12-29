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

namespace PSX\V8;

use V8\ArrayObject;
use V8\BooleanObject;
use V8\BooleanValue;
use V8\Context;
use V8\DateObject;
use V8\FunctionObject;
use V8\IntegerValue;
use V8\NullValue;
use V8\NumberObject;
use V8\NumberValue;
use V8\ObjectValue;
use V8\RegExpObject;
use V8\StringObject;
use V8\StringValue;
use V8\UndefinedValue;
use V8\Value;

/**
 * Decoder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Decoder
{
    /**
     * Converts a V8 value into a PHP data type
     * 
     * @param Value $value
     * @param Context $context
     * @return mixed
     */
    public static function decode(Value $value, Context $context)
    {
        if ($value instanceof BooleanValue) {
            return $value->value();
        } elseif ($value instanceof StringValue) {
            return $value->value();
        } elseif ($value instanceof IntegerValue) {
            return $value->value();
        } elseif ($value instanceof NumberValue) {
            return $value->value();
        } elseif ($value instanceof NullValue) {
            return null;
        } elseif ($value instanceof UndefinedValue) {
            return null;
        } elseif ($value instanceof DateObject) {
            return new \DateTime('@' . intval($value->valueOf() / 1000));
        } elseif ($value instanceof BooleanObject) {
            return $value->valueOf();
        } elseif ($value instanceof StringObject) {
            return $value->valueOf()->value();
        } elseif ($value instanceof NumberObject) {
            $val = $value->valueOf();
            return strpos($val, '.') === false ? intval($val) : $val;
        } elseif ($value instanceof RegExpObject) {
            return $value->getSource()->value();
        } elseif ($value instanceof ArrayObject) {
            $result = [];
            for ($i = 0; $i < $value->length(); $i++) {
                $result[] = self::decode($value->get($context, new IntegerValue($context->getIsolate(), $i)), $context);
            }
            return $result;
        } elseif ($value instanceof FunctionObject) {
            return function(...$arguments) use ($value, $context){
                $result = $value->call(
                    $context,
                    Encoder::encode(new \stdClass(), $context),
                    array_map(function($value) use ($context){
                        return Encoder::encode($value, $context);
                    }, $arguments)
                );

                return self::decode($result, $context);
            };
        } elseif ($value instanceof ObjectValue) {
            $names  = $value->getOwnPropertyNames($context);
            $result = new \stdClass();

            for ($i = 0; $i < $names->length(); $i++) {
                $name = $names->get($context, new IntegerValue($context->getIsolate(), $i));
                if ($name instanceof StringValue) {
                    $key = $name->value();
                    $val = $value->get($context, $name);

                    $result->$key = self::decode($val, $context);
                }
            }

            return $result;
        } else {
            return null;
        }
    }
}

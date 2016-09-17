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

namespace PSX\V8;

use V8\ArrayObject;
use V8\BooleanObject;
use V8\BooleanValue;
use V8\Context;
use V8\DateObject;
use V8\NullValue;
use V8\NumberObject;
use V8\NumberValue;
use V8\ObjectValue;
use V8\RegExpObject;
use V8\StringObject;
use V8\StringValue;
use V8\Value;

/**
 * Decoder
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
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
            return $value->Value();
        } elseif ($value instanceof StringValue) {
            return $value->Value();
        } elseif ($value instanceof NumberValue) {
            return $value->Value();
        } elseif ($value instanceof NullValue) {
            return null;
        } elseif ($value instanceof DateObject) {
            return new \DateTime('@' . intval($value->ValueOf() / 1000));
        } elseif ($value instanceof BooleanObject) {
            return $value->ValueOf();
        } elseif ($value instanceof StringObject) {
            return $value->ValueOf();
        } elseif ($value instanceof NumberObject) {
            return $value->ValueOf();
        } elseif ($value instanceof RegExpObject) {
            return $value->GetSource()->Value();
        } elseif ($value instanceof ArrayObject) {
            $result = [];
            for ($i = 0; $i < $value->Length(); $i++) {
                $result[] = self::decode($value->GetIndex($context, $i), $context);
            }
            return $result;
        } elseif ($value instanceof ObjectValue) {
            $names  = $value->GetOwnPropertyNames($context);
            $result = new \stdClass();

            for ($i = 0; $i < $names->Length(); $i++) {
                $name = $names->GetIndex($context, $i);
                if ($name instanceof StringValue) {
                    $key = $name->Value();
                    $val = $value->Get($context, $name);

                    $result->$key = self::decode($val, $context);
                }
            }

            return $result;
        } else {
            return null;
        }
    }
}

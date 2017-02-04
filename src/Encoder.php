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
use V8\BooleanValue;
use V8\Context;
use V8\DateObject;
use V8\FunctionCallbackInfo;
use V8\FunctionObject;
use V8\IntegerValue;
use V8\NullValue;
use V8\NumberValue;
use V8\ObjectValue;
use V8\RegExpObject;
use V8\StringValue;
use V8\Value;

/**
 * Encoder
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Encoder
{
    /**
     * Converts a PHP data type into a V8 value
     *
     * @param mixed $value
     * @param Context $context
     * @return Value
     */
    public static function encode($value, Context $context)
    {
        if (is_bool($value)) {
            return new BooleanValue($context->GetIsolate(), $value);
        } elseif (is_string($value)) {
            return new StringValue($context->GetIsolate(), $value);
        } elseif (is_float($value)) {
            return new NumberValue($context->GetIsolate(), $value);
        } elseif (is_int($value)) {
            return new IntegerValue($context->GetIsolate(), $value);
        } elseif ($value === null) {
            return new NullValue($context->GetIsolate());
        } elseif ($value instanceof \DateTime) {
            return new DateObject($context, $value->getTimestamp() * 1000);
        } elseif ($value instanceof ObjectInterface) {
            return self::encode($value->getProperties(), $context);
        } elseif ($value instanceof \Closure || is_callable($value)) {
            return new FunctionObject($context, function(FunctionCallbackInfo $info) use ($value){
                $args   = $info->Arguments();
                $params = [];
                foreach ($args as $arg) {
                    $params[] = Decoder::decode($arg, $info->GetContext());
                }

                $return = call_user_func_array($value, $params);

                $info->GetReturnValue()->Set(self::encode($return, $info->GetContext()));
            });
        } elseif ($value instanceof \ArrayObject) {
            return self::encode($value->getArrayCopy(), $context);
        } elseif ($value instanceof \Traversable) {
            return self::encode(iterator_to_array($value), $context);
        } elseif ($value instanceof \JsonSerializable) {
            return self::encode($value->jsonSerialize(), $context);
        } elseif ($value instanceof \stdClass || (is_array($value) && self::isAssoc($value))) {
            $object = new ObjectValue($context);
            foreach ($value as $key => $val) {
                $object->Set(
                    $context,
                    self::encode($key, $context),
                    self::encode($val, $context)
                );
            }
            return $object;
        } elseif (is_array($value)) {
            $array = new ArrayObject($context);
            $index = 0;
            foreach ($value as $val) {
                $array->SetIndex($context, $index, self::encode($val, $context));
                $index++;
            }
            return $array;
        } else {
            return new NullValue($context->GetIsolate());
        }
    }

    /**
     * Returns whether an array is index based or associative
     *
     * @param array $array
     * @return boolean
     */
    protected static function isAssoc(array $array)
    {
        if (empty($array)) {
            return false;
        }

        if (isset($array[0])) {
            $n = count($array) - 1;

            return array_sum(array_keys($array)) != ($n * ($n + 1)) / 2;
        } else {
            return true;
        }
    }
}

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
use V8\ArrayObject;
use V8\Context;

/**
 * ArrayWrapper
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ArrayWrapper extends ValueWrapper implements \Iterator, \Countable
{
    /**
     * @var ArrayObject
     */
    protected $value;

    /**
     * @var int
     */
    private $pos = 0;

    public function __construct(Context $context, ArrayObject $value)
    {
        parent::__construct($context, $value);
    }

    public function count()
    {
        return $this->value->length();
    }

    public function current()
    {
        return $this->wrapValue(
            $this->value->get(
                $this->context,
                Encoder::encode($this->pos, $this->context)
            )
        );
    }

    public function next()
    {
        $this->pos++;
    }

    public function key()
    {
        return $this->pos;
    }

    public function valid()
    {
        return $this->value->has(
            $this->context,
            Encoder::encode($this->pos, $this->context)
        );
    }

    public function rewind()
    {
        $this->pos = 0;
    }
}

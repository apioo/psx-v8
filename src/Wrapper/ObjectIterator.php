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

namespace PSX\V8\Wrapper;

use V8\Context;
use V8\ObjectValue;

/**
 * ObjectIterator
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class ObjectIterator extends ValueWrapper implements \Iterator
{
    /**
     * @var ObjectValue
     */
    protected $value;

    /**
     * @var array
     */
    protected $names;

    /**
     * @var int
     */
    private $pos = 0;

    public function __construct(Context $context, ObjectValue $value, $ownProperties = true)
    {
        parent::__construct($context, $value);

        if ($ownProperties) {
            $this->names = $value->GetOwnPropertyNames($context);
        } else {
            $this->names = $value->GetPropertyNames($context);
        }
    }

    public function current()
    {
        $return = $this->value->Get(
            $this->context,
            $this->names->GetIndex($this->context, $this->pos)
        );

        return $this->wrapValue($return);
    }

    public function next()
    {
        $this->pos++;
    }

    public function key()
    {
        return $this->wrapValue($this->names->GetIndex($this->context, $this->pos));
    }

    public function valid()
    {
        return $this->names->HasIndex($this->context, $this->pos);
    }

    public function rewind()
    {
        $this->pos = 0;
    }
}

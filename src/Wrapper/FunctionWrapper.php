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
use V8\FunctionObject;

/**
 * FunctionWrapper
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class FunctionWrapper extends ValueWrapper
{
    /**
     * @var FunctionObject
     */
    protected $value;

    /**
     * @var mixed
     */
    protected $scope;

    public function __construct(Context $context, FunctionObject $value)
    {
        parent::__construct($context, $value);

        $this->scope = new \stdClass();
    }

    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    function __invoke(...$arguments)
    {
        $result = $this->value->call(
            $this->context,
            Encoder::encode($this->scope, $this->context),
            array_map(function($value){
                return Encoder::encode($value, $this->context);
            }, $arguments)
        );

        return $this->wrapValue($result);
    }
}

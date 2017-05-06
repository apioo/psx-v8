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

use PSX\V8\Wrapper\ObjectWrapper;
use V8\Context;
use V8\Isolate;
use V8\Script;
use V8\ScriptOrigin;
use V8\StringValue;

/**
 * Environment
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Environment extends ObjectWrapper
{
    protected $isolate;
    protected $context;

    public function __construct()
    {
        $this->isolate = new Isolate();
        $this->context = new Context($this->isolate);

        parent::__construct(
            $this->context,
            $this->context->GlobalObject()
        );
    }

    public function setMemoryLimit($memoryLimit)
    {
        $this->isolate->SetMemoryLimit($memoryLimit);
    }

    public function setTimeLimit($timeLimit)
    {
        $this->isolate->SetTimeLimit($timeLimit);
    }

    public function run($script)
    {
        if (!($script instanceof StringValue)) {
            $script = new StringValue($this->isolate, $script);
        }

        $script = new Script($this->context, $script, new ScriptOrigin(__CLASS__));
        $result = $script->Run($this->context);

        return $this->wrapValue($result);
    }
}
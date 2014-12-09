<?php

namespace lib\BackgroundProcess;

class Factory
{
    /** @var string */
    private $className;

    public function __construct($className)
    {
        $this->className = $className;
    }

    public function newProcess($command)
    {
        $className = $this->className;
        return new $className($command);
    }
}

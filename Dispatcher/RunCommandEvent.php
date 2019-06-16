<?php

namespace Cifren\OxPeckerData\Dispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of RunCommandEvent.
 *
 * @author Le Coq Francis
 */
class RunCommandEvent extends Event
{
    protected $name;
    protected $args;

    public function __construct($name, array $args = null)
    {
        $this->name = $name;
        $this->args = $args;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getArgs()
    {
        return $this->args;
    }
}

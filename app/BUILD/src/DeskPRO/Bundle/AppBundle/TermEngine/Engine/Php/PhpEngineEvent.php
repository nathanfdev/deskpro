<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Php;

use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use Symfony\Component\EventDispatcher\Event;

class PhpEngineEvent extends Event
{
    /**
     * @var TermEngineContext
     */
    private $context;

    public function __construct(TermEngineContext $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}

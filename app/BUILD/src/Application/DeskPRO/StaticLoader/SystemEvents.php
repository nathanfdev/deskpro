<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\StaticLoader;

use Application\DeskPRO\EventDispatcher\CallbackListener;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Attaches various events to the event dispatcher.
 */
class SystemEvents
{
    /** @var EventDispatcher */
    protected $event_dispatcher;

    public function __construct($event_dispatcher)
    {
        $this->event_dispatcher = $event_dispatcher;
    }

    public function addNoPhraseEventListener()
    {
        $listener = new CallbackListener(function ($ev) {
            if (strpos($ev->phrase_name, 'obj_') !== 0) {
                $ev->return = "[{$ev->phrase_name}]";
            }
        });

        $this->event_dispatcher->addListener('DeskPRO_onTranslateNoPhrase', $listener);
    }
}

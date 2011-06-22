<?php
/**
 * DeskPRO
 *
 * @package StaticLoader
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\StaticLoader;

use Application\DeskPRO\EventDispatcher\CallbackListener;

/**
 * Attaches various events to the event dispatcher
 */
class SystemEvents
{
	protected $event_dispatcher;

	public function __construct($event_dispatcher)
	{
		$this->event_dispatcher = $event_dispatcher;
	}

	public function addNoPhraseEventListener()
	{
		$listener = new CallbackListener(function ($ev) {
			$ev->return = "[{$ev->phrase_name}]";
		});

		$this->event_dispatcher->addListener('DeskPRO_onTranslateNoPhrase', $listener);
	}
}
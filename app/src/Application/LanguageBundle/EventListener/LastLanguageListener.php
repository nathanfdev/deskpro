<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\LanguageBundle\EventListener;

use Application\LanguageBundle\Language\LanguageStack;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Early in a request, this listener will determine the active brand for this request and push it onto the brand_stack
 * service.
 */
class LastLanguageListener implements EventSubscriberInterface
{
    const COOKIE_NAME = 'dp_last_lang';

    /**
     * @var LanguageStack
     */
    private $language_stack;

    public function __construct(LanguageStack $language_stack)
    {
        $this->language_stack = $language_stack;
    }

	public function onKernelResponse(FilterResponseEvent $event)
	{
        $last_lang = null;

        if ($lang = $this->language_stack->getActive()) {
            $last_lang = $lang->getTwoLetterLanguageCode();
        }

        $event->getResponse()->headers->setCookie(new Cookie(static::COOKIE_NAME, $last_lang));
	}

	public static function getSubscribedEvents()
	{
		return array(
			KernelEvents::RESPONSE => array('onKernelResponse')
		);
	}
}
 
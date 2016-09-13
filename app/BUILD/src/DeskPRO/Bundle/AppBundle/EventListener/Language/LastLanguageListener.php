<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener\Language;

use DeskPRO\Bundle\AppBundle\Language\LanguageStack;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
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
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageStack
     */
    private $language_stack;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(LanguageStack $language_stack, LoggerInterface $logger)
    {
        $this->language_stack = $language_stack;
        $this->logger         = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $last_lang = null;

        // only set the cookie if there is actually a language
        // we don't want to set it to null if there is no language because we might want to redirect in the next request
        if ($lang = $this->language_stack->getActive()) {
            $last_lang = $lang->getUrlCode();
            $this->logger->debug('language: sending cookie for last language: '.$last_lang);
            $event->getResponse()->headers->setCookie(new Cookie(static::COOKIE_NAME, $last_lang));
        }
    }
}

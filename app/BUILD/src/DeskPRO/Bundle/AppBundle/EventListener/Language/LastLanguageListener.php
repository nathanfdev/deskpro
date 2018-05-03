<?php

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

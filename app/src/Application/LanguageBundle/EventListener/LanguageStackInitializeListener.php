<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\LanguageBundle\EventListener;

use Application\AppBundle\Helper\IsProxyRequestHelper;
use Application\LanguageBundle\Language\LanguageManager;
use Application\LanguageBundle\Routing\UrlMatcher;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Application\DeskPRO\Entity\Language;

class LanguageStackInitializeListener implements EventSubscriberInterface
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var LoggerInterface
     */
    private $logger;


    public function __construct(LanguageManager $language_manager, LoggerInterface $logger)
    {
        $this->language_manager = $language_manager;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onRequest', 512) // very high priority
        );
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $language_stack = $this->language_manager->getLanguageStack();

        $this->logger->info('finding a language to push to language stack');

        if (
            ($lang = $this->detectFromRequestPath($request))
            || ($lang = $this->detectFromRequestCookies($request))
            || ($lang = $this->detectFromEsiQuery($request))
        ) {

            $this->logger->info(
                sprintf('detected "%s" and initializing language stack with it',
                    $lang->getTwoLetterLanguageCode()
                )
            );

            $language_stack->push($lang);

            return;
        }

        $language_stack->pushDefault();

        $this->logger->info('detected no language in request - using default');
    }

    protected function detectFromRequestPath(Request $request)
    {
        $pathinfo = $request->getPathInfo();
        $matcher = new UrlMatcher();
        $split = $matcher->extractLanguageCode($pathinfo);
        if ($lang_code = $split['lang_url_code']) {
            $this->logger->info(sprintf('found "%s" in the uri', $lang_code));

            return $this->language_manager->getLanguage($lang_code);
        }

        return null;
    }

    /**
     * @param  Request $request
     * @return Language|null
     */
    protected function detectFromRequestCookies(Request $request)
    {
        if ($lang_code = $request->cookies->get(LastLanguageListener::COOKIE_NAME)) {
            $this->logger->info(sprintf('found "%s" in last language cookie', $lang_code));

            return $this->language_manager->getLanguage($lang_code);
        }

        return null;
    }

    private function detectFromEsiQuery(Request $request)
    {
        if (IsProxyRequestHelper::check($request)) {
            if ($lang_code = $request->query->get('lang_url_code')) {
                $this->logger->info(sprintf('found "%s" in esi lang_url_code query', $lang_code));

                return $this->language_manager->getLanguage($lang_code);
            }
        }

        return null;
    }
}

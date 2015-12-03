<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Routing\UrlMatcher;
use Doctrine\ORM\EntityManager;
use Negotiation\LanguageNegotiator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LanguageStackInitializeListener implements EventSubscriberInterface
{
    /**
     * @var \DeskPRO\Bundle\AppBundle\Language\LanguageManager
     */
    private $language_manager;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EntityManager
     */
    private $connection;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(LanguageManager $language_manager, LoggerInterface $logger, EntityManager $em)
    {
        $this->language_manager = $language_manager;
        $this->logger           = $logger;
        $this->em               = $em;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onRequest', 512), // very high priority
        );
    }

    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request        = $event->getRequest();
        $language_stack = $this->language_manager->getLanguageStack();

        $this->logger->info('finding a language to push to language stack');

        if (
            ($lang = $this->detectFromRequestPath($request))
            || ($lang = $this->detectFromEsiQuery($request))
            || ($lang = $this->detectFromPersonIfLoggedIn($request))
            || ($lang = $this->detectFromRequestCookies($request))
            || ($lang = $this->detectFromRequestHeaders($request))
        ) {
            $this->logger->info(
                sprintf('detected "%s" and initializing language stack with it',
                    $lang->getUrlCode()
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
        $matcher  = new UrlMatcher();
        $split    = $matcher->extractLanguageCode($pathinfo);
        if ($lang_code = $split['lang_url_code']) {
            $this->logger->info(sprintf('found "%s" in the uri', $lang_code));

            return $this->language_manager->getLanguage($lang_code);
        }

        return;
    }

    /**
     * @param Request $request
     *
     * @return Language|null
     */
    protected function detectFromRequestCookies(Request $request)
    {
        if ($lang_code = $request->cookies->get(LastLanguageListener::COOKIE_NAME)) {
            $this->logger->info(sprintf('found "%s" in last language cookie', $lang_code));

            return $this->language_manager->getLanguage($lang_code);
        }

        return;
    }

    protected function detectFromEsiQuery(Request $request)
    {
        if (IsProxyRequestHelper::check($request)) {
            if ($lang_code = $request->query->get('lang_url_code')) {
                $this->logger->info(sprintf('found "%s" in esi lang_url_code query', $lang_code));

                return $this->language_manager->getLanguage($lang_code);
            }
        }

        return;
    }

    protected function detectFromPersonIfLoggedIn(Request $request)
    {
        // this is a request listener that runs before the security firewall
        // the only way to detect language this early (needed for routing)
        // is to manually fetch the sess_data and find the person_id
        if ($session_id = $request->cookies->get('dpsid', null)) {
            $query = $this->em->getConnection()->executeQuery(
                'SELECT * FROM sess_data WHERE sess_id = :sess_id',
                array(
                    'sess_id' => $session_id,
                ),
                array(
                    'sess_id' => \PDO::PARAM_STR,
                )
            );

            if (!$sess_row = $query->fetch()) {
                return;
            }

            if (!isset($sess_row['person_id'])) {
                return;
            }

            if (!$person_id = $sess_row['person_id']) {
                return;
            }

            if (!$person = $this->em->getRepository('DeskPRO:Person')->find($person_id)) {
                return;
            }

            /* @var Person $person */
            return $person->language;
        }
    }

    protected function detectFromRequestHeaders(Request $request)
    {
        $negotiator = new LanguageNegotiator();

        // get the language codes of all portal langs enabled
        $langs      = $this->language_manager->getEnabledLanguages();
        $lang_codes = array_map(function (Language $lang) {
            return $lang->getUrlCode();
        }, $langs);

        // negotiate between our list of supported langs vs. the Accept-Language header
        $header_lang = $negotiator->getBest(
            $request->headers->get('Accept-Language'),
            $lang_codes
        );

        if (!$header_lang) {
            return;
        }

        // make sure the negotiated language was a language that was in the request headers
        // it is possible that a language was negotiated that didn't exist in the request
        // (e.g. if the request header had all langs that we do not support).
        // it's better to move on and eventually use portal-defined default lang than
        // to use a lang they didn't request.
        $header_lang = $header_lang->getValue();
        if (in_array($header_lang, $request->getLanguages())) {
            try {
                return $this->language_manager->getLanguage($header_lang);
            } catch (\Exception $e) {
            }
        }

        return;
    }
}

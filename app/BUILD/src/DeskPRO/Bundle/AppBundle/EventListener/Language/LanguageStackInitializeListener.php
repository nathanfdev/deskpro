<?php

namespace DeskPRO\Bundle\AppBundle\EventListener\Language;

use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session as LegacySessionEntity;
use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use DeskPRO\Bundle\PortalBundle\Routing\UrlMatcher;
use Doctrine\ORM\EntityManager;
use Negotiation\LanguageNegotiator;
use Orb\Util\Web;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class LanguageStackInitializeListener.
 */
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
     * @var PortalModeStorage
     */
    private $portal_mode_store;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param LanguageManager        $language_manager
     * @param PortalModeStorage|null $portal_mode_store
     * @param LoggerInterface        $logger
     * @param EntityManager          $em
     */
    public function __construct(LanguageManager $language_manager, PortalModeStorage $portal_mode_store = null, LoggerInterface $logger, EntityManager $em)
    {
        $this->language_manager  = $language_manager;
        $this->logger            = $logger;
        $this->em                = $em;
        $this->portal_mode_store = $portal_mode_store;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 100], // init after session listeners
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
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
            || ($lang = $this->detectFromPersonIfLoggedInLegacy($request))
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

    /**
     * @param Request $request
     *
     * @return Language
     */
    protected function detectFromRequestPath(Request $request)
    {
        if ($this->portal_mode_store && $mode = $this->portal_mode_store->getMode()) {
            // without taking the internal path from the mode like this,
            // we can get false checks on the regex in the matcher below
            $pathinfo = $mode->getInternalPath();
        } else {
            $pathinfo = $request->getPathInfo();
        }
        $matcher = new UrlMatcher();
        $split   = $matcher->extractLanguageCode($pathinfo);
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
        $langCode = $request->cookies->get(LastLanguageListener::COOKIE_NAME);
        if ($langCode && is_scalar($langCode)) {
            $this->logger->info(sprintf('found "%s" in last language cookie', $langCode));

            return $this->language_manager->getLanguage($langCode);
        }

        return;
    }

    /**
     * @param Request $request
     *
     * @return Language
     */
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

    /**
     * @param Request $request
     *
     * @return Language
     */
    protected function detectFromPersonIfLoggedIn(Request $request)
    {
        // this is a request listener that runs before the security firewall
        // the only way to detect language this early (needed for routing)
        // is to manually fetch the sess_data and find the person_id
        $sessionId = $request->cookies->get('dpsid-portal', null);
        if ($sessionId && is_scalar($sessionId)) {
            $query = $this->em->getConnection()->executeQuery(
                'SELECT person_id, sess_data FROM sess_data WHERE sess_id = :sess_id',
                ['sess_id' => $sessionId],
                ['sess_id' => \PDO::PARAM_STR]
            );

            if (!$row = $query->fetch()) {
                return;
            }

            if (!isset($row['person_id'])) {
                return;
            }

            if (!$personId = $row['person_id']) {
                return;
            }

            if (isset($row['sess_data'])) {
                try {
                    $data = base64_decode($row['sess_data']);
                    $data = Web::unserializeSesisonData($data);
                } catch (\Exception $e) {
                    return;
                }

                if (isset($data['_sf2_attributes']['is_impersonating'])
                    && isset($data['_sf2_attributes']['auth_person_id'])) {
                    $personId = $data['_sf2_attributes']['auth_person_id'];
                }
            }

            if (!$person = $this->em->getRepository(Person::class)->find($personId)) {
                return;
            }

            return $person->getLanguage();
        }
    }

    /**
     * @param Request $request
     *
     * @return Language|void
     */
    protected function detectFromPersonIfLoggedInLegacy(Request $request)
    {
        // Dont attempt to run this on portal since it makes no sense
        if (!defined('DP_INTERFACE') || DP_INTERFACE === 'user') {
            return;
        }

        // this is a request listener that runs before the security firewall
        // the only way to detect language this early (needed for routing)
        // is to manually fetch the sess_data and find the person_id
        $sessionId = $request->cookies->get('dpsid-agent', null);
        if ($sessionId && is_scalar($sessionId)) {
            $sid = LegacySessionEntity::getIdFromCode($sessionId);
            if (!$sid) {
                return;
            }

            $query = $this->em->getConnection()->executeQuery(
                'SELECT person_id FROM sessions WHERE id = :sess_id',
                ['sess_id' => $sid],
                ['sess_id' => \PDO::PARAM_STR]
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
            return $person->getLanguage();
        }
    }

    /**
     * @param Request $request
     *
     * @return Language
     */
    protected function detectFromRequestHeaders(Request $request)
    {
        $negotiator = new LanguageNegotiator();

        // get the language codes of all portal langs enabled
        $langs     = $this->language_manager->getEnabledLanguages();
        $langCodes = array_map(function (Language $lang) {
            return $lang->getUrlCode();
        }, $langs);

        if (!$langCodes) {
            return;
        }

        $header = $request->headers->get('Accept-Language');
        if (!$header) {
            return;
        }

        // negotiate between our list of supported langs vs. the Accept-Language header
        $headerLang = $negotiator->getBest($header, $langCodes);
        if (!$headerLang) {
            return;
        }

        // make sure the negotiated language was a language that was in the request headers
        // it is possible that a language was negotiated that didn't exist in the request
        // (e.g. if the request header had all langs that we do not support).
        // it's better to move on and eventually use portal-defined default lang than
        // to use a lang they didn't request.
        $headerLang = $headerLang->getValue();
        if (in_array($headerLang, $request->getLanguages())) {
            try {
                return $this->language_manager->getLanguage($headerLang);
            } catch (\Exception $e) {
            }
        }

        return;
    }
}

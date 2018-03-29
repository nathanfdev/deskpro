<?php

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RateLimitLog;
use Application\DeskPRO\EntityRepository\RateLimitLog as RateLimitLogRepository;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuseConfig;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuseConfigSet;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseLockoutEvent;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Rate limiting means CAPTCHA or lockout in DeskPRO at the moment.
 */
class RateLimitEventListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AntiAbuse::getEventName(AntiAbuse::ACTION_LOGIN)           => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_REGISTER)        => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_RESET_PASSWORD)  => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SUBMIT_COMMENT)  => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SUBMIT_FEEDBACK) => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SUBMIT_TICKET)   => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_TOKEN_EXCHANGE)  => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_UPLOAD)          => 'checkAntiAbuse',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SHARE_CONTENT)   => 'checkAntiAbuse',
        ];
    }

    /**
     * RateLimitEventListener constructor.
     *
     * @param EntityManager    $em
     * @param SettingsResolver $settings_resolver
     * @param LoggerInterface  $logger
     */
    public function __construct(EntityManager $em, SettingsResolver $settings_resolver, LoggerInterface $logger)
    {
        $this->em                = $em;
        $this->settings_resolver = $settings_resolver;
        $this->logger            = $logger;
    }

    /**
     * @param AntiAbuseEvent $event
     *
     * @throws \Exception
     */
    public function checkAntiAbuse(AntiAbuseEvent $event)
    {
        if ($this->getSetting(AntiAbuse::SETTING_RATE_LIMIT_IS_DISABLED)) {
            $this->logger->debug(
                sprintf(
                    '[AntiAbuse->RateLimitEventListener] Rate Limit for [ %s ] is disabled. Skipping.',
                    $event->getType()
                )
            );

            return;
        }

        if ($this->isWhitelisted($event->getIp())) {
            $this->logger->info('[AntiAbuse->RateLimitEventListener] Whitelist matched IP "'.$event->getIp().'"". Skipping rate limit checks.');

            return;
        }

        if ($this->isCaptchaRequired($event)) {
            $this->log($event, 'captcha');
            $event->markCaptchaRecommended();
        }

        $lockout = $this->isLockoutRequired($event);
        if ($lockout !== false) {
            $this->log($event, 'lockout');
            $antiAbuseConfig = $event->getConfig();
            $estimated       = $antiAbuseConfig->getLockoutTime() ? $this->getLockoutTime($event) : 0;
            $event->markResponseRequired();
            $event->stopPropagation();
            $event->markLockoutRecommended($estimated);
        }
    }

    /**
     * @param AntiAbuseEvent $event
     *
     * @throws \Exception
     *
     * @return bool
     */
    private function isCaptchaRequired(AntiAbuseEvent $event)
    {
        $action    = $event->getType();
        $person    = $event->getPerson();
        $configSet = $this->getConfig($action, $person);

        $required = false;
        foreach ($configSet->getConfigCollection() as $config) {
            if (!$config->isValid()) {
                throw new \Exception('Invalid rate limit action');
            }

            if (!$config->isEnabled() || $config->getResponse() !== AntiAbuseConfig::RESPONSE_CAPTCHA) {
                // no need to count, break early
                continue;
            }

            $event->setConfig($config);

            /** @var RateLimitLogRepository $repository */
            $repository = $this->em->getRepository(RateLimitLog::class);
            $required   = $required || $repository->count($config, $event->getIp()) >= (int) $config->getLimit();
        }

        return $required;
    }

    /**
     * @param AntiAbuseEvent $event
     *
     * @throws \Exception
     *
     * @return bool
     */
    private function isLockoutRequired(AntiAbuseEvent $event)
    {
        $action    = $event->getType();
        $person    = $event->getPerson();
        $configSet = $this->getConfig($action, $person);

        $required = false;
        foreach ($configSet->getConfigCollection() as $config) {
            if (!$config->isValid()) {
                throw new \Exception('Invalid rate limit action');
            }

            if (!$config->isEnabled()
                || (!$event instanceof AntiAbuseLockoutEvent && $config->getResponse() !== AntiAbuseConfig::RESPONSE_LOCKOUT)
            ) {
                // no need to count, break early
                continue;
            }

            $event->setConfig($config);

            /** @var RateLimitLogRepository $repository */
            $repository = $this->em->getRepository(RateLimitLog::class);

            // at first let's decide if we are in lockout
            $lastLockoutAttempt = $repository->getLastLockedOutAttempt($event->getConfig(), $event->getIp());
            if ($lastLockoutAttempt && $lastLockoutAttempt + $config->getLockoutTime() > time()) {
                return true; // we are in lockout already so it's required
            }

            $required = $required || $repository->count($config, $event->getIp()) >= (int) $config->getLimit();
        }

        return $required;
    }

    /**
     * @param AntiAbuseEvent $event
     * @param string         $sanction
     */
    private function log(AntiAbuseEvent $event, $sanction)
    {
        $person = $event->getPerson();
        if ($person instanceof Person) {
            $p = $person->isGuest() ? 'guest' : $person->getId();
        } elseif (is_scalar($person)) {
            $p = $person;
        } else {
            $p = 'unknown';
        }
        $this->logger->info(
            sprintf(
                '[AntiAbuse->RateLimitEventListener] [%s] is recommended for (IP=%s, Person=%s, Type=%s)',
                $sanction,
                $event->getIp(),
                $p,
                $event->getType()
            )
        );
    }

    /**
     * @param string $action
     * @param Person $person
     *
     * @return AntiAbuseConfigSet
     */
    protected function getConfig($action, Person $person)
    {
        $configSet = new AntiAbuseConfigSet();
        if ($action === AntiAbuse::ACTION_LOGIN && !$person->isGuest()) {
            $configSet->addConfig($this->getConfigInternal($action, new PersonGuest()));
        }
        $configSet->addConfig($this->getConfigInternal($action, $person));

        return $configSet;
    }

    /**
     * @param string $action
     * @param Person $person
     *
     * @return AntiAbuseConfig
     */
    protected function getConfigInternal($action, Person $person)
    {
        $config = new AntiAbuseConfig($person, $action);
        foreach (['limit', 'time', 'response', 'enabled', 'lockout_time'] as $key) {
            $method = StringUtils::toCamelCase(sprintf('set_%s', $key));
            //we need to check an agent/user/guest(unknown) settings

            if ($person->isGuest()) {
                $keyAddition = 'guest';
            } elseif ($person->isAgent()) {
                $keyAddition = 'agent';
            } else {
                $keyAddition = false;
            }

            if ($keyAddition) {
                $keyParts = [AntiAbuse::KEY, $action, $keyAddition, $key];
                if (null !== $value = $this->getSetting(implode('.', $keyParts))) {
                    $config->$method($value);
                    continue;
                }
            }

            if (null === $value = $this->getSetting(AntiAbuse::KEY.'.'.$action.'.'.$key)) {
                continue;
            }

            $config->$method($value);
        }

        return $config;
    }

    /**
     * @param string $setting
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getSetting($setting, $default = null)
    {
        return $this->settings_resolver->getGlobalSettings()->get($setting, $default);
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    protected function isWhitelisted($ip)
    {
        $whitelisted = json_decode($this->getSetting(AntiAbuse::SETTING_IP_WHITELIST), true) ?: [];

        return IpUtils::checkIp($ip, $whitelisted);
    }

    /**
     * @param AntiAbuseEvent $event
     *
     * @return int
     */
    protected function getLockoutTime(AntiAbuseEvent $event)
    {
        /** @var RateLimitLogRepository $rep */
        $rep = $this->em->getRepository(RateLimitLog::class);

        return $rep->getLockoutTime(
            $event->getConfig(),
            $event->getIp()
        );
    }
}

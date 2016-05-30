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

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RateLimitLog;
use Application\DeskPRO\EntityRepository\RateLimitLog as RateLimitLogRepository;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuseConfig;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Rate limiting means CAPTCHA in DeskPRO at the moment. This listener contains the logic for our captcha related anti-abuse.
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

        // We should save attempt only AFTER check was performed. Because if the maximum
        // attempts is set to 1 then it will be failed just while checking, that's not right.
        if (!$event->isCheckOnly()) {
            $this->saveRateLimitAction(
                $event->getType(),
                $event->getPerson(),
                $event->getIp(),
                $event->isLockoutRecommended()
            );
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
        $action = $event->getType();
        $person = $event->getPerson();
        $config = $this->getConfig($action, $person);
        if (!$config->isValid()) {
            throw new \Exception('Invalid rate limit action');
        }

        $event->setConfig($config);

        if (!$config->isEnabled()) {
            return false;
        }

        /** @var RateLimitLogRepository $rep */
        $rep = $this->em->getRepository(RateLimitLog::class);
        $res = $rep->count($action, $config->getTime(), $person, $event->getIp());

        return $res >= (int) $config->getLimit()
            ? $config->getResponse() === AntiAbuseConfig::RESPONSE_CAPTCHA
            : false;
    }

    private function isLockoutRequired(AntiAbuseEvent $event)
    {
        $action = $event->getType();
        $person = $event->getPerson();
        $config = $this->getConfig($action, $person);
        if (!$config->isValid()) {
            throw new \Exception('Invalid rate limit action');
        }

        $event->setConfig($config);
        if (!$config->isEnabled()) {
            return false;
        }

        /** @var RateLimitLogRepository $rep */
        $rep = $this->em->getRepository(RateLimitLog::class);

        // at first let's decide if we are in lockout
        $lastLockoutAttempt = $rep->getLastLockedOutAttempt($action, $event->getPerson(), $event->getIp());
        if ($lastLockoutAttempt && $lastLockoutAttempt + $config->getLockoutTime() > time()) {
            return true; // we are in lockout already so it's required
        }

        $res = $rep->count($action, $config->getTime(), $person, $event->getIp());

        return $res >= $config->getLimit()
            ? $config->getResponse() === AntiAbuseConfig::RESPONSE_LOCKOUT
            : false;
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
     * @return AntiAbuseConfig
     */
    protected function getConfig($action, Person $person)
    {
        $config = new AntiAbuseConfig();
        foreach (['limit', 'time', 'response', 'enabled', 'lockout_time'] as $key) {
            $method = StringUtils::toCamelCase(sprintf('set_%s', $key));
            // try guest first
            if ($person instanceof PersonGuest) {
                if (null !== $value = $this->getSetting(AntiAbuse::KEY.'.'.$action.'.guest.'.$key)) {
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
     * @param string $action
     * @param Person $person
     * @param string $ip
     * @param bool   $lockedOut
     */
    protected function saveRateLimitAction($action, Person $person, $ip, $lockedOut)
    {
        /** @var RateLimitLogRepository $rep */
        $rep = $this->em->getRepository(RateLimitLog::class);
        $rep->save($action, $person, $ip, $lockedOut);
    }

    /**
     * @param string $ip
     *
     * @return bool
     */
    protected function isWhitelisted($ip)
    {
        $ip          = ip2long($ip);
        $whitelisted = json_decode($this->getSetting(AntiAbuse::SETTING_IP_WHITELIST), true) ?: [];
        foreach ($whitelisted as $wip) {
            @list($subnet, $bits) = explode('/', $wip);
            $subnet               = ip2long($subnet);

            if (!$bits) {
                if ($ip === $subnet) {
                    return true;
                }
            } else {
                $mask = -1 << (32 - $bits);
                $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
                if (($ip & $mask) === $subnet) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function getLockoutTime(AntiAbuseEvent $event)
    {
        $antiAbuseConfig = $event->getConfig();
        /** @var RateLimitLogRepository $rep */
        $rep = $this->em->getRepository(RateLimitLog::class);

        return $rep->getLockoutTime(
            $event->getPerson(),
            $event->getType(),
            $antiAbuseConfig->getTime(),
            $antiAbuseConfig->getLockoutTime(),
            $event->getIp()
        );
    }
}

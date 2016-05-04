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
namespace DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\RateLimitLog;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Service\RateLimit;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
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

    public static function getSubscribedEvents()
    {
        return [
            AntiAbuse::EVENT_NAME => 'checkAntiAbuse',
        ];
    }

    public function __construct(EntityManager $em, SettingsResolver $settings_resolver, LoggerInterface $logger)
    {
        $this->em                = $em;
        $this->settings_resolver = $settings_resolver;
        $this->logger            = $logger;
    }

    public function checkAntiAbuse(AntiAbuseEvent $event)
    {
        if (!$this->supportsType($event)) {
            return;
        }

        if ($this->getSetting(AntiAbuse::SETTING_RATE_LIMIT_IS_DISABLED)) {
            $this->logger->debug('[AntiAbuse->RateLimitEventListener] Rate Limit is disabled. Skipping.');

            return;
        }

        if ($this->isWhitelisted($event->getIp())) {
            $this->logger->info('[AntiAbuse->RateLimitEventListener] Whitelist matched IP "'.$event->getIp().'"". Skipping rate limit checks.');

            return;
        }

        if (!$event->isCheckOnly()) {
            $this->saveRateLimitAction($event->getType(), $event->getPerson(), $event->getIp());
        }

        if ($this->isCaptchaRequired($event->getType(), $event->getPerson(), $event->getIp())) {
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
                    '[AntiAbuse->RateLimitEventListener] captcha is recommended for (IP=%s, Person=%s, Type=%s)',
                    $event->getIp(),
                    $p,
                    $event->getType()
                )
            );

            $event->markCaptchaRecommended();
        }
    }

    /**
     * response. bool for now.
     *
     * @param $action
     * @param Person $person
     * @param null   $ip
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isCaptchaRequired($action, Person $person, $ip = null)
    {
        if (!$params = $this->getParams($action, $person, $ip)) {
            throw new \Exception('Invalid rate limit action');
        }

        /** @var RateLimitLog $rep */
        $rep = $this->em->getRepository('DeskPRO:RateLimitLog');
        $res = $rep->count($action, $params['time'], $person, $ip);

        // all rate limit actions have a captcha as response, so we return bool for now
        return $res >= (int) $params['limit']
            ? (bool) $params['response']
            : false;
    }

    /**
     * params for current dataset.
     *
     * @param $action
     * @param Person $person
     * @param null   $ip
     *
     * @return array
     */
    protected function getParams($action, Person $person, $ip = null)
    {
        $params = [];
        foreach (['limit', 'time', 'response'] as $key) {
            // try guest first
            if ($person instanceof PersonGuest) {
                if (null !== $value = $this->getSetting(RateLimit::KEY.'.'.$action.'.guest.'.$key)) {
                    $params[$key] = $value;
                    continue;
                }
            }

            if (null === $value = $this->getSetting(RateLimit::KEY.'.'.$action.'.'.$key)) {
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }

    protected function supportsType(AntiAbuseEvent $event)
    {
        return in_array(
            $event->getType(),
            [
                AntiAbuse::ACTION_LOGIN,
                AntiAbuse::ACTION_UPLOAD,
                AntiAbuse::ACTION_REGISTER,
                AntiAbuse::ACTION_RESET_PASSWORD,
                AntiAbuse::ACTION_TOKEN_EXCHANGE,
                AntiAbuse::ACTION_SUBMIT_TICKET,
                AntiAbuse::ACTION_SUBMIT_FEEDBACK,
                AntiAbuse::ACTION_SUBMIT_COMMENT,
            ]
        );
    }

    protected function getSetting($setting, $default = null)
    {
        return $this->settings_resolver->getGlobalSettings()->get($setting, $default);
    }

    protected function saveRateLimitAction($action, Person $person, $ip)
    {
        /** @var RateLimitLog $rep */
        $rep = $this->em->getRepository('DeskPRO:RateLimitLog');
        $rep->save($action, $person, $ip);
    }

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
}

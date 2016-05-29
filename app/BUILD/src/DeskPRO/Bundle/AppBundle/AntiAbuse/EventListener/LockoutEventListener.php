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
use Application\DeskPRO\EntityRepository\LoginLog;
use Application\DeskPRO\NewSettings\SettingsResolver;
use Application\DeskPRO\Settings\LoginRateLimitSettings;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This is meant to "lock out" logins, and can be expanded to lock out other things too.
 */
class LockoutEventListener implements EventSubscriberInterface
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
     * @var UrlGeneratorInterface
     */
    private $url_generator;

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

    public function __construct(
        EntityManager $em,
        SettingsResolver $settings_resolver,
        UrlGeneratorInterface $url_generator,
        LoggerInterface $logger
    ) {
        $this->em                = $em;
        $this->settings_resolver = $settings_resolver;
        $this->url_generator     = $url_generator;
        $this->logger            = $logger;
    }

    public function checkAntiAbuse(AntiAbuseEvent $event)
    {
        if (!$this->supportsType($event)) {
            return;
        }

        $person = $event->getPerson();

        if (!$person instanceof Person) {
            // we cannot check because we require a person object for this listener
            // we cannot throw an exception, because other listeners may not need a Person to do their job
            return;
        }

        $context         = $person->isAgent() ? 'agent' : 'user';
        $settings        = $this->settings_resolver->getGlobalSettings();
        $settings_prefix = $context.'.'.LoginRateLimitSettings::KEY;

        if (!$settings->get($settings_prefix.'.enabled')) {
            $this->logger->debug('[AntiAbuse->LockoutEventListener] Lockout is disabled. Skipping.');

            return;
        }

        /** @var LoginLog $rep */
        $rep          = $this->em->getRepository('DeskPRO:LoginLog');
        $max_attempts = $settings->get($settings_prefix.'.attempts');
        $check_time   = $settings->get($settings_prefix.'.attempts_time');
        $lock_time    = $settings->get($settings_prefix.'.lock_time');

        $lockout_time = $rep->getLoginLockoutTime($person, $max_attempts, $check_time, $lock_time);

        if ($lockout_time > 0) {
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
                    '[AntiAbuse->LockoutEventListener] lockout is required for (IP=%s, Person=%s, Lockout Time=%s)',
                    $event->getIp(),
                    $p,
                    $lockout_time
                )
            );

            $event->setResponse(new RedirectResponse($this->url_generator->generate('portal_login', ['lockout' => 'auth'], UrlGeneratorInterface::ABSOLUTE_PATH)));
            $event->markLockoutRecommended();
            $event->markResponseRequired();
            $event->stopPropagation();
        }
    }

    protected function supportsType(AntiAbuseEvent $event)
    {
        return in_array($event->getType(), [
//            AntiAbuse::ACTION_LOGIN,
        ]);
    }
}

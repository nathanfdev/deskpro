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

namespace DeskPRO\Bundle\AppBundle\AntiAbuse\EventListener;

use Application\DeskPRO\Entity\RateLimitLog;
use Application\DeskPRO\EntityRepository\RateLimitLog as RateLimitLogRepository;
use DeskPRO\Bundle\AppBundle\AntiAbuse\AntiAbuse;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Rate limiting means CAPTCHA or lockout in DeskPRO at the moment.
 */
class RateLimitSaveListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            AntiAbuse::getEventName(AntiAbuse::ACTION_LOGIN)           => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_REGISTER)        => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_RESET_PASSWORD)  => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SUBMIT_COMMENT)  => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SUBMIT_FEEDBACK) => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SUBMIT_TICKET)   => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_TOKEN_EXCHANGE)  => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_UPLOAD)          => 'saveRateLimit',
            AntiAbuse::getEventName(AntiAbuse::ACTION_SHARE_CONTENT)   => 'saveRateLimit',
        ];
    }

    /**
     * RateLimitEventListener constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function saveRateLimit(AntiAbuseEvent $event)
    {
        if (!$event->isCheckOnly()) {
            /** @var RateLimitLogRepository $rep */
            $rep = $this->em->getRepository(RateLimitLog::class);
            $rep->save($event->getType(), $event->getPerson(), $event->getIp(), $event->isLockoutRecommended());
        }
    }
}

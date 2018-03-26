<?php

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

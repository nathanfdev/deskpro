<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AntiAbuse;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\RateLimitLog;
use Application\DeskPRO\EntityRepository\RateLimitLog as RateLimitLogRepository;
use Application\DeskPRO\People\PersonGuest;
use DeskPRO\Bundle\AppBundle\AntiAbuse\Event\AntiAbuseEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A service, used by controllers, that helps prevent abuse to the system.
 */
class AntiAbuse
{
    const KEY = 'rate_limit';

    const SETTING_RATE_LIMIT_IS_DISABLED = 'core.rate_limit_disabled';
    const SETTING_IP_WHITELIST           = 'core.rate_limit_ips';

    const ACTION_LOGIN           = 'login';
    const ACTION_UPLOAD          = 'upload_attachment';
    const ACTION_REGISTER        = 'registration';
    const ACTION_RESET_PASSWORD  = 'reset_password';
    const ACTION_TOKEN_EXCHANGE  = 'token_exchange';
    const ACTION_SUBMIT_COMMENT  = 'submit_comment';
    const ACTION_SUBMIT_FEEDBACK = 'submit_feedback';
    const ACTION_SUBMIT_TICKET   = 'submit_ticket';
    const ACTION_SHARE_CONTENT   = 'share_content';

    // the EVENT_ consts are needed, because the ACTION_ are legacy and
    // cannot be used by themselves as event names for this sytem.
    const EVENT_NAME = 'anti_abuse.event';

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * AntiAbuse constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param EntityManager            $em
     */
    public function __construct(EventDispatcherInterface $dispatcher, EntityManager $em)
    {
        $this->dispatcher = $dispatcher;
        $this->em         = $em;
    }

    /**
     * Run a user event through the anti-abuse system.
     *
     * @param AntiAbuseEvent $event
     *
     * @return AntiAbuseEvent
     */
    public function check(AntiAbuseEvent $event)
    {
        $this->ensureEventHasAPersonObject($event);

        $this->dispatcher->dispatch(self::getEventName($event->getType()), $event);

        if ($event->isResponseRequired() && !$event->isCheckOnly()) {
            // a listener has signaled a response is required to be returned
            // to the user immediately. throw an exception so it is caught
            // by kernel.exception listeners and rendered.
            // if $event is marked as "checkOnly" then don't do this.
            throw $event->generateException();
        }

        return $event;
    }

    /**
     * @param AntiAbuseEvent $event
     */
    private function ensureEventHasAPersonObject(AntiAbuseEvent $event)
    {
        // we allow emails to be used in place of a person object so we resolve that here
        if (!$event->getPerson() instanceof Person) {
            /** @var \Application\DeskPRO\EntityRepository\Person $person_repo */
            $person_repo = $this->em->getRepository('DeskPRO:Person');
            if ($person = $person_repo->findOneByEmail($event->getEmail())) {
                $event->setPerson($person);
            }
        }

        // still no person object? make it a guest.
        if (!$event->getPerson() instanceof Person) {
            $event->setPerson(new PersonGuest());
        }
    }

    /**
     * @param AntiAbuseEvent $event
     */
    public function saveRateLimit(AntiAbuseEvent $event)
    {
        if (!$event->isCheckOnly()) {
            /** @var RateLimitLogRepository $rep */
            $rep = $this->em->getRepository(RateLimitLog::class);
            $rep->save($event->getType(), $event->getPerson(), $event->getIp(), $event->isLockoutRecommended());
        }
    }

    /**
     * @param $eventType
     *
     * @return string
     */
    public static function getEventName($eventType)
    {
        return self::EVENT_NAME.'.'.$eventType;
    }
}

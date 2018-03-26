<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email;

use Application\EmailBundle\Mail\RawTransport\RawTransportException;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\AbstractExceptionEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OutgoingEmailFailureEvent.
 *
 * @ORM\Entity
 */
class OutgoingEmailFailureEvent extends AbstractExceptionEvent
{
    use EmailAccountData;

    /**
     * {@inheritdoc}
     */
    protected $expirationStrategy = AbstractEvent::EXPIRES_WITH_TIME;

    /**
     * @param int                   $emailAccountId
     * @param string                $emailAccountAddress
     * @param RawTransportException $exception
     * @param \DateTime|null        $dateCreated
     */
    public function __construct(
        $emailAccountId, $emailAccountAddress, RawTransportException $exception, \DateTime $dateCreated = null)
    {
        $this->emailAccountId      = $emailAccountId;
        $this->emailAccountAddress = $emailAccountAddress;
        parent::__construct($exception, $dateCreated);
    }
}

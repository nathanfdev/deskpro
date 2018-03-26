<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class OutgoingEmailSuccessEvent.
 *
 * @ORM\Entity
 */
class OutgoingEmailSuccessEvent extends AbstractEvent implements SuccessEvent
{
    use EmailAccountData;

    /**
     * {@inheritdoc}
     */
    protected $expirationStrategy = AbstractEvent::EXPIRES_WITH_TIME;

    /**
     * @param int            $emailAccountId
     * @param string         $emailAccountAddress
     * @param \DateTime|null $dateCreated
     */
    public function __construct($emailAccountId, $emailAccountAddress, $dateCreated = null)
    {
        $this->emailAccountId      = $emailAccountId;
        $this->emailAccountAddress = $emailAccountAddress;
        parent::__construct($dateCreated);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailureType()
    {
        return OutgoingEmailFailureEvent::class;
    }
}

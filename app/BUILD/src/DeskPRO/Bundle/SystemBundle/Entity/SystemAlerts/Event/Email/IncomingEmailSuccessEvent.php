<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\SuccessEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IncomingEmailSuccessEvent.
 *
 * @ORM\Entity
 */
class IncomingEmailSuccessEvent extends AbstractEvent implements SuccessEvent
{
    use EmailAccountData;

    /**
     * {@inheritdoc}
     */
    protected $expirationStrategy = AbstractEvent::EXPIRES_WITH_TIME;

    /**
     * @param EmailAccount   $account
     * @param \DateTime|null $dateCreated
     */
    public function __construct(EmailAccount $account, \DateTime $dateCreated = null)
    {
        $this->emailAccountId      = $account->getId();
        $this->emailAccountAddress = $account->getAddress();
        parent::__construct($dateCreated);
    }

    /**
     * {@inheritdoc}
     */
    public function getFailureType()
    {
        return IncomingEmailFailureEvent::class;
    }
}

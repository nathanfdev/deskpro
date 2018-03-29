<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email;

use Application\DeskPRO\Entity\EmailAccount;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Exception\AbstractExceptionEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IncomingEmailFailureEvent.
 *
 * @ORM\Entity
 */
class IncomingEmailFailureEvent extends AbstractExceptionEvent
{
    use EmailAccountData;

    /**
     * {@inheritdoc}
     */
    protected $expirationStrategy = AbstractEvent::EXPIRES_WITH_TIME;

    /**
     * @param EmailAccount   $account
     * @param \Exception     $exception
     * @param \DateTime|null $dateCreated
     */
    public function __construct(EmailAccount $account, \Exception $exception, \DateTime $dateCreated = null)
    {
        $this->emailAccountId      = $account->getId();
        $this->emailAccountAddress = $account->getAddress();
        parent::__construct($exception, $dateCreated);
    }
}

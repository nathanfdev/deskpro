<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Email;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class EmailAccountData.
 */
trait EmailAccountData
{
    /**
     * @var int
     *
     * @ORM\Column(name="email_account_id", type="integer", options={"unsigned"=true})
     */
    private $emailAccountId;

    /**
     * @var string
     *
     * @ORM\Column(name="email_account_address", type="string")
     */
    private $emailAccountAddress;

    /**
     * @return int
     */
    public function getEmailAccountId()
    {
        return $this->emailAccountId;
    }

    /**
     * @return string
     */
    public function getEmailAccountAddress()
    {
        return $this->emailAccountAddress;
    }

    /**
     * @see \DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event::getSubjectDescription()
     */
    public function getSubjectDescription()
    {
        return $this->getEmailAccountAddress();
    }

    /**
     * @see \DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event::getSubjectUniqueId()
     */
    public function generateSubjectUniqueId()
    {
        return $this->getEmailAccountId();
    }
}

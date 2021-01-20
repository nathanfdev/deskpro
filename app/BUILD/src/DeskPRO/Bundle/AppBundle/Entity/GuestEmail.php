<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class GuestEmail
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\GuestEmailRepository")
 * @ORM\Table(name="guest_emails")
 * @ORM\InheritanceType("NONE")
 *
 * @category Entities
 */
class GuestEmail
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=180, nullable=false)
     */
    protected $ipAddress;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;


    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return GuestEmail
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return GuestEmail
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return GuestEmail
     */
    public function setEmail(string $email)
    {
        $this->setModelField('email', $email);

        return $this;
    }

    /**
     * @return GuestEmail
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return GuestEmail
     */
    public function setIpAddress(string $ipAddress)
    {
        $this->setModelField('ipAddress', $ipAddress);

        return $this;
    }

}

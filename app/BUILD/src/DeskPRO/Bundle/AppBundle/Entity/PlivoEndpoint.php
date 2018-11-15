<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PlivoEndpoints.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plivo_endpoints", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="person_endpoint", columns={"account_id", "person_id"})
 * })
 */
class PlivoEndpoint implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\ManyToOne(targetEntity="PlivoVoiceAccount", inversedBy="numbers")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var PlivoVoiceAccount
     */
    protected $account;

    /**
     * @ORM\Column(name="endpoint_id", type="string")
     *
     * @var string
     */
    protected $endpointId;

    /**
     * @ORM\Column(name="username", type="string")
     *
     * @var string
     */
    protected $username;

    /**
     * @ORM\Column(name="password", type="string")
     *
     * @var string
     */
    protected $password;

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return PlivoVoiceAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return $this
     */
    public function setAccount(PlivoVoiceAccount $account = null)
    {
        $this->setModelField('account', $account);

        return $this;
    }

    /**
     * @return string
     */
    public function getEndpointId()
    {
        return $this->endpointId;
    }

    /**
     * @param string $endpointId
     *
     * @return $this
     */
    public function setEndpointId($endpointId)
    {
        $this->setModelField('endpointId', $endpointId);

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->setModelField('username', $username);

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword($password)
    {
        $this->setModelField('password', $password);

        return $this;
    }
}

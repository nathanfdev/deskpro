<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class VoicemailRecord.
 *
 * @ORM\Entity()
 * @ORM\Table(name="voicemail_records")
 *
 * @JMS\ExclusionPolicy("all")
 */
class VoicemailRecord implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall", inversedBy="voicemailRecord")
     * @ORM\JoinColumn(name="phone_call_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall>")
     *
     * @var VoicePhoneCall
     */
    private $phoneCall;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    private $agent;

    /**
     * @ORM\Column(name="data", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    private $data = [];

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isDeleted = false;

    /**
     * @ORM\Column(name="is_listened", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $isListened = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return VoicePhoneCall
     */
    public function getPhoneCall()
    {
        return $this->phoneCall;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return $this
     */
    public function setPhoneCall(VoicePhoneCall $phoneCall = null)
    {
        $this->setModelField('phoneCall', $phoneCall);

        return $this;
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function setAgent(Person $agent = null)
    {
        $this->setModelField('agent', $agent);

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data = null)
    {
        $this->setModelField('data', $data);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated($dateCreated)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     *
     * @return $this
     */
    public function setIsDeleted($isDeleted)
    {
        $this->setModelField('isDeleted', $isDeleted);

        return $this;
    }

    /**
     * @return bool
     */
    public function isListened()
    {
        return $this->isListened;
    }

    /**
     * @param bool $isListened
     *
     * @return $this
     */
    public function setIsListened($isListened)
    {
        $this->setModelField('isListened', $isListened);

        return $this;
    }
}

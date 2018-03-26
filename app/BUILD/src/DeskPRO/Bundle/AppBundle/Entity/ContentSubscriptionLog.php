<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\ContentSubscriptionLogRepository")
 * @ORM\Table(name="content_subscription_log", indexes={
 *     @ORM\Index(name="content_idx", columns={"content_type", "content_id"})
 * })
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class ContentSubscriptionLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const NOTIFY_NEW    = 'new';
    const NOTIFY_UPDATE = 'update';

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $content_type;

    /**
     * @var string
     * @ORM\Column(type="integer")
     */
    protected $content_id;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $notify_type;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date_sent;

    /**
     * @return int
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
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * This is the base name of the entity object (e.g. Article, News, Download, Feedback).
     *
     * @param string $content_type
     *
     * @return $this
     */
    public function setContentType($content_type)
    {
        $this->setModelField('content_type', $content_type);

        return $this;
    }

    /**
     * @return string
     */
    public function getContentId()
    {
        return $this->content_id;
    }

    /**
     * @param string $content_id
     *
     * @return $this
     */
    public function setContentId($content_id)
    {
        $this->setModelField('content_id', $content_id);

        return $this;
    }

    /**
     * @return string
     */
    public function getNotifyType()
    {
        return $this->notify_type;
    }

    /**
     * @param string $notify_type
     *
     * @return $this
     */
    public function setNotifyType($notify_type)
    {
        $this->setModelField('notify_type', $notify_type);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateSent()
    {
        return $this->date_sent;
    }

    /**
     * @param \DateTime $date_sent
     *
     * @return $this
     */
    public function setDateSent($date_sent)
    {
        $this->setModelField('date_sent', $date_sent);

        return $this;
    }
}

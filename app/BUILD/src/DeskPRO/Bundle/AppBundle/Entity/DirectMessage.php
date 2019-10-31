<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\DirectMessageRepository")
 * @ORM\Table(name="direct_messages")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class DirectMessage implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="message_html", type="text")
     *
     * @var string
     */
    protected $messageHtml;

    /**
     * @ORM\Column(name="message_doc", type="json_array", nullable=true)
     *
     * @var array
     */
    protected $messageDoc = [];

    /**
     * @ORM\ManyToOne(targetEntity="DirectMessageParticipant")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     *
     * @var DirectMessageParticipant
     */
    protected $author;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('dateCreated', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

        return $this;
    }

    /**
     * @return text
     */
    public function getMessageHtml()
    {
        return $this->messageHtml;
    }

    /**
     * @param string $messageHtml
     *
     * @return $this
     */
    public function setMessageHtml($messageHtml)
    {
        $this->setModelField('messageHtml', $messageHtml);

        return $this;
    }

    /**
     * @return array
     */
    public function getMessageDoc()
    {
        return $this->messageDoc;
    }

    /**
     * @param array $messageDoc
     *
     * @return $this
     */
    public function setMessageDoc($messageDoc)
    {
        $this->setModelField('messageDoc', $messageDoc);

        return $this;
    }

    /**
     * @return DirectMessageParticipant
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param DirectMessageParticipant $author
     *
     * @return $this
     */
    public function setAuthor(DirectMessageParticipant $author)
    {
        $this->author = $author;

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
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateCreated($date)
    {
        $this->setModelField('dateCreated', $date);

        return $this;
    }
}

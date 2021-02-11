<?php

namespace DeskPRO\Bundle\AppBundle\Entity\CommentAttachment;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use DateTime;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Numbers;

/**
 * @JMS\ExclusionPolicy("none")
 */
class CommentAttachment
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Blob")
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var Blob
     */
    protected $blob;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'c'>")
     */
    protected $createdAt;

    /**
     * CommentAttachment constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
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
     * @return CommentAttachment
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
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
     * @return CommentAttachment
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob($blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }

    /**
     * @return string
     */
    public function getReadableFileSize()
    {
        if ($this->blob->filesize) {
            return Numbers::filesizeDisplay($this->blob->filesize);
        }

        if (!$this->blob) {
            return '0 B';
        }

        return $this->blob->getReadableFilesize();
    }
}

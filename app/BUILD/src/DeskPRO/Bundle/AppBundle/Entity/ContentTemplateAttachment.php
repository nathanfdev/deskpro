<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;

/**
 * Class ContentTemplateAttachment
 *
 * @ORM\Entity()
 * @ORM\Table(name="content_template_attachments")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @PortalLinkCustom(type="serve")
 */
class ContentTemplateAttachment implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\ContentTemplate", inversedBy="attachments")
     * @ORM\JoinColumn(name="content_template_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\ContentTemplate")
     *
     * @var ContentTemplate
     */
    protected $contentTemplate;

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
     * The date template attachment was created.
     *
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * The date template attachment was updated.
     *
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_updated;

    /**
     * ContentTemplateAttachment constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_updated', new \DateTime());
    }

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
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime|null $dateCreated
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated = null)
    {
        if ($dateCreated === null) {
            $dateCreated = new \DateTime();
        }
        $this->setModelField('date_created', $dateCreated);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param \DateTime $dateUpdated
     *
     * @return $this
     */
    public function setDateUpdated(\DateTime $dateUpdated)
    {
        $this->setModelField('date_updated', $dateUpdated);

        return $this;
    }

    /**
     * @return ContentTemplate
     */
    public function getContentTemplate()
    {
        return $this->contentTemplate;
    }

    /**
     * @param $contentTemplate
     *
     * @return $this
     */
    public function setContentTemplate(ContentTemplate $contentTemplate)
    {
        $this->contentTemplate = $contentTemplate;

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
     * Set blob data.
     *
     * @param Blob $blob
     *
     * @return $this
     */
    public function setBlob(Blob $blob)
    {
        $this->setModelField('blob', $blob);

        return $this;
    }
}

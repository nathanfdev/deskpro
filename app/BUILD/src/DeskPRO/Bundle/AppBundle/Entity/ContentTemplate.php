<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ContentEntity.
 *
 * @ORM\Entity()
 * @ORM\Table(name="content_templates")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ContentTemplate implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const CONTENT_TYPE_ARTICLE = 'article';
    const CONTENT_TYPE_NEWS    = 'news';

    const CONTENT_TYPE_DOWNLOAD = 'download';

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
     * @ORM\Column(type="string", name="type", length=20, nullable=false)
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * Person created this template.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\Column(type="string", name="title", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * The date template was created.
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
     * The date template was updated.
     *
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     *
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_updated;

    /**
     * Template itself.
     *
     * @ORM\Column(type="json_array", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $template;

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
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

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
     * @param \DateTime $dateCreated
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $dateCreated)
    {
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
     * @return array
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param array $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->setModelField('template', $template);

        return $this;
    }
}

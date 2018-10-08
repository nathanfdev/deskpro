<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ImportLog.
 *
 * @ORM\Entity()
 * @ORM\Table(name="import_logs")
 *
 * @JMS\ExclusionPolicy("all")
 */
class ImportLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(name="type", type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(name="counts", type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $counts = [];

    /**
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Blob", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="import_log_blobs",
     *      joinColumns={@ORM\JoinColumn(name="log_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="blob_id", referencedColumnName="id", unique=true)}
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<Application\DeskPRO\Entity\Blob>")
     *
     * @var array
     */
    protected $logBlobs;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->logBlobs    = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
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
     * @return array
     */
    public function getCounts()
    {
        return $this->counts;
    }

    /**
     * @param array $counts
     *
     * @return $this
     */
    public function setCounts(array $counts)
    {
        $this->setModelField('counts', $counts);

        return $this;
    }

    /**
     * @return array
     */
    public function getLogBlobs()
    {
        return $this->logBlobs;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function addLogBlob(Blob $blob)
    {
        $this->logBlobs->add($blob);

        return $this;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function removeLogBlob(Blob $blob)
    {
        $this->logBlobs->removeElement($blob);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}

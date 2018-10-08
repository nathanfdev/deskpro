<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use DeskPRO\Bundle\AppBundle\ObjectRouter\Configuration\PortalLinkCustom;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Numbers;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * News attachments.
 *
 * @PortalLinkCustom(type="serve")
 * @JMS\ExclusionPolicy("all")
 */
class NewsAttachment extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\News>")
     *
     * @var \Application\DeskPRO\Entity\News
     */
    protected $news;

    /**
     * Who created the attachment.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @JMS\Expose()
     * @JMS\Type("Application\DeskPRO\Entity\Blob")
     *
     * @var \Application\DeskPRO\Entity\Blob
     *
     * @Assert\Valid()
     */
    protected $blob;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * Set person.
     *
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

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\NewsAttachment';
        $metadata->setPrimaryTable(['name' => 'news_attachments']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'news',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\News',
            'mappedBy'     => null,
            'inversedBy'   => 'attachments',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'news_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'dpApi' => true,
        ]);
    }
}

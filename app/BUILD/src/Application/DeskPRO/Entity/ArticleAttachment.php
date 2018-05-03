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
use Orb\Util\Numbers;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article attachments.
 *
 * @PortalLinkCustom(type="serve")
 */
class ArticleAttachment extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\ARticle
     */
    protected $article;

    /**
     * Who created the attachment.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
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
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ArticleAttachment';
        $metadata->setPrimaryTable(['name' => 'article_attachments']);
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
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'article',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Article',
            'mappedBy'     => null,
            'inversedBy'   => 'attachments',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'article_id',
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

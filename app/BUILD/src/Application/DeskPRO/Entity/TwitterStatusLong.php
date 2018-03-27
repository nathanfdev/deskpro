<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Twitter Status.
 *
 * Long Reply/Message w/ URL Shortener.
 */
class TwitterStatusLong extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\TwitterStatus
     */
    protected $status;

    /**
     * @var TwitterUser
     */
    protected $for_user;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var bool
     */
    protected $is_public = false;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var bool
     */
    protected $is_read = false;

    /**
     * @var \DateTime
     */
    protected $date_read = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        if (null !== $this->status) {
            return $this->status->getId();
        }

        return 0;
    }

    /**
     * @param int $id
     */
    public function setStatusId($id)
    {
        if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
            $this->setModelField('status', $status);
        } else {
            $this->setModelField('status', null);
        }
    }

    public function getParsedText()
    {
        $text = htmlspecialchars($this->text, ENT_COMPAT, 'utf-8');
        $text = preg_replace('/@([a-z0-9_]+)/i', '<a href="https://twitter.com/$1" target="_blank">$0</a>', $text);
        $text = \Orb\Util\Strings::linkifyHtml($text, true);

        return nl2br($text);
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return (bool) $this->is_public;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return (bool) $this->is_read;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable(['name' => 'twitter_statuses_long']);
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
            'fieldName'  => 'text',
            'type'       => 'string',
            'length'     => 4000,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'text',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_public',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_public',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_read',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_read',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_read',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_read',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapOneToOne([
            'fieldName'    => 'status',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus',
            'mappedBy'     => null,
            'inversedBy'   => 'long',
            'joinColumns'  => [
                [
                    'name'                 => 'status_id',
                    'referencedColumnName' => 'id',
                    'unique'               => false,
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'for_user',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'for_user_id',
                    'referencedColumnName' => 'id',
                    'unique'               => false,
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}

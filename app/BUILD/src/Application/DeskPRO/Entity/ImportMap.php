<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A general map that maps old IDs to new IDs.
 */
class ImportMap extends \Application\DeskPRO\Domain\DomainObject
{
    const TYPE_ZENDESK_ORGANIZATION_FIELD = 'zd_organization_field';
    const TYPE_ZENDESK_USER_FIELD         = 'zd_user_field';
    const TYPE_ZENDESK_TICKET             = 'zd_ticket';
    const TYPE_ZENDESK_TICKET_FIELD       = 'zd_ticket_field';
    const TYPE_ZENDESK_TICKET_MESSAGE     = 'zd_ticket_message';
    const TYPE_ZENDESK_ARTICLE            = 'zd_article';
    const TYPE_ZENDESK_ARTICLE_CATEGORY   = 'zd_article_category';
    const TYPE_DESKPRO_ORGANIZATION_FIELD = 'dp_organization_field';
    const TYPE_DESKPRO_USER_FIELD         = 'dp_user_field';
    const TYPE_DESKPRO_TICKET_FIELD       = 'dp_ticket_field';
    const TYPE_DESKPRO_ARTICLE_FIELD      = 'dp_article_field';
    const TYPE_DESKPRO_FEEDBACK_FIELD     = 'dp_feedback_field';
    const TYPE_CSV_ARTICLE                = 'csv_article';
    const TYPE_CSV_TICKET                 = 'csv_ticket';

    /**
     * The type of id/thing/whatever this is mapping.
     *
     * @var string
     */
    protected $typename;

    /**
     * @var string
     */
    protected $old_id = 0;

    /**
     * @var string
     */
    protected $new_id = 0;

    /**
     * Returns mapping type.
     *
     * @return string
     */
    public function getTypename()
    {
        return $this->typename;
    }

    /**
     * Set mapping type.
     *
     * @param string $typename
     *
     * @return $this
     */
    public function setTypename($typename)
    {
        $this->setModelField('typename', $typename);

        return $this;
    }

    /**
     * Returns external entity id.
     *
     * @return string
     */
    public function getOldId()
    {
        return $this->old_id;
    }

    /**
     * Set external entity id.
     *
     * @param string $old_id
     *
     * @return $this
     */
    public function setOldId($old_id)
    {
        $this->setModelField('old_id', $old_id);

        return $this;
    }

    /**
     * Returns entity id.
     *
     * @return string
     */
    public function getNewId()
    {
        return $this->new_id;
    }

    /**
     * Set entity id.
     *
     * @param string $new_id
     *
     * @return $this
     */
    public function setNewId($new_id)
    {
        $this->setModelField('new_id', $new_id);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ImportMap';
        $metadata->setPrimaryTable(['name' => 'import_map']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'typename',
            'type'       => 'dpblob',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'typename',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'old_id',
            'type'       => 'dpblob',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'old_id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'new_id',
            'type'       => 'dpblob',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'new_id',
        ]);
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
 * Twitter Status Mention.
 */
class TwitterStatusMention extends \Application\DeskPRO\Domain\DomainObject
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
     * @var \Application\DeskPRO\Entity\TwitterUser
     */
    protected $user;

    /**
     * @var int
     */
    protected $starts = 0;

    /**
     * @var int
     */
    protected $ends = 0;

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
        $this->setModelField('status', null);

        if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
            $this->setModelField('status', $status);
        }
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return null !== $this->user ? $this->user->getId() : null;
    }

    /**
     * @param int $id
     */
    public function setUserId($id)
    {
        if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
            $this->setModelField('user', $user);
        } else {
            $this->setModelField('user', null);
        }
    }

    /**
     * @param object $mention
     *
     * @return \Application\DeskPRO\Entity\TwitterStatusMention
     */
    public static function createFromJson($mention)
    {
        $entity           = new self();
        $entity['starts'] = $mention->indices[0];
        $entity['ends']   = $mention->indices[1];

        return $entity;
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable(['name' => 'twitter_statuses_mentions']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(['fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true]);
        $metadata->mapField(['fieldName' => 'starts', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'starts']);
        $metadata->mapField(['fieldName' => 'ends', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'ends']);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne(['fieldName' => 'status', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus', 'mappedBy' => null, 'inversedBy' => 'mentions', 'joinColumns' => [0 => ['name' => 'status_id', 'referencedColumnName' => 'id', 'nullable' => false, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
        $metadata->mapManyToOne(['fieldName' => 'user', 'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser', 'mappedBy' => null, 'inversedBy' => 'mentions', 'joinColumns' => [0 => ['name' => 'user_id', 'referencedColumnName' => 'id', 'nullable' => false, 'onDelete' => 'cascade', 'columnDefinition' => null]]]);
    }
}

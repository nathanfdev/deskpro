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
 * Twitter Account followed by a User.
 */
class TwitterAccountFollower extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\TwitterAccount
     */
    protected $account;

    /**
     * @var \Application\DeskPRO\Entity\TwitterUser
     */
    protected $user;

    /**
     * @var int
     */
    protected $follow_order;

    /**
     * @var bool
     */
    protected $is_archived = false;

    /**
     * @return int
     */
    public function getAccountId()
    {
        if (null !== $this->account) {
            return $this->account->getId();
        }

        return 0;
    }

    /**
     * @param int $id
     */
    public function setAccountId($id)
    {
        if ($id && $account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id)) {
            $this->setModelField('account', $account);
        } else {
            $this->setModelField('account', null);
        }
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        if (null !== $this->user) {
            return $this->user->getId();
        }

        return 0;
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

    public function _preInsert()
    {
        if ($this->follow_order === null) {
            $max = App::getDb()->fetchColumn('
                SELECT MAX(follow_order)
                FROM twitter_accounts_followers
                WHERE account_id = ?
            ', [$this->account->id]);
            $this->follow_order = intval($max) + 1;
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterAccountFollower';
        $metadata->setPrimaryTable([
            'name'              => 'twitter_accounts_followers',
            'uniqueConstraints' => [
                'account_user_idx' => [
                    'columns' => [
                        'account_id',
                        'user_id',
                    ],
                ],
            ],
        ]);
        $metadata->addLifecycleCallback('_preInsert', 'prePersist');
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
            'fieldName'  => 'follow_order',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'follow_order',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_archived',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_archived',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccount',
            'mappedBy'     => null,
            'inversedBy'   => 'followers',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'account_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'user',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterUser',
            'mappedBy'     => null,
            'inversedBy'   => 'followers',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'user_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}

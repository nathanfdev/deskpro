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
 * Twitter Account following a User.
 */
class TwitterAccountFriend extends \Application\DeskPRO\Domain\DomainObject
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

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterAccountFriend';
        $metadata->setPrimaryTable([
            'name'              => 'twitter_accounts_friends',
            'uniqueConstraints' => [
                'account_user_idx' => [
                    'columns' => [
                        0 => 'account_id',
                        1 => 'user_id',
                    ],
                ],
            ],
        ]);
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
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccount',
            'mappedBy'     => null,
            'inversedBy'   => 'friends',
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
            'inversedBy'   => null,
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

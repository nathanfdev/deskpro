<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Util;

/**
 * A cache of various permissions for a given set of usergroups. For example,
 * a computed array of category ID's 1,3,5 has access to.
 *
 * @deprecated we cache using a cache adapter now (\Application\DeskPRO\Cache\CacheAdapterInterface)
 *             on new-portal. this was used on the old way of using permissions.
 *             AGENT/ADMIN/etc still use this
 */
class PermissionCache extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The type of permissions cache.
     *
     * @var string
     */
    protected $name;

    /**
     * Usergroup key is an md5() of all usergroup ID's concatenated
     * with a command in asending order.
     *
     * @var string
     */
    protected $usergroup_key;

    /**
     * A comma-separated list of usergroup_ids this cache applies to.
     *
     * @var string
     */
    protected $usergroup_ids = '';

    /**
     * Permission data.
     *
     * @var bool
     */
    protected $perms = [];

    /**
     * @var null|array
     */
    protected $_usergroup_ids = null;

    public static function newFromLoader(\Application\DeskPRO\People\PermissionLoader\AbstractLoader $loader, $person_id = 0)
    {
        $obj                  = new self();
        $obj['name']          = Util::getBaseClassname($loader);
        $obj['usergroup_ids'] = $loader->getUsergroupIds();
        if ($loader->getSubkey()) {
            $obj->appendKeyId($loader->getSubkey());
        }
        $obj->perms = $loader;

        return $obj;
    }

    public function setUsergroupIds(array $ids)
    {
        sort($ids, SORT_NUMERIC);
        $this->_usergroup_ids = $ids;

        $this->setModelField('usergroup_ids', implode(',', $ids));
        $this->setModelField('usergroup_key', self::generateUsergroupSetKey($this->_usergroup_ids));
    }

    public function getUsergroupIds()
    {
        if ($this->_usergroup_ids === null) {
            $this->_usergroup_ids = explode(',', $this->usergroup_ids);
        }

        return $this->_usergroup_ids;
    }

    public function appendKeyId($id)
    {
        if ($id) {
            $this->setModelField('usergroup_key', $this->usergroup_key.'-'.$id);
        }
    }

    /**
     * Generate a key for a set of usergroups. These same usergroups
     * will always generate the same key.
     *
     * @static
     *
     * @param array $usergroup_ids
     *
     * @return string
     */
    public static function generateUsergroupSetKey(array $usergroup_ids)
    {
        return Usergroup::generateUsergroupSetKey($usergroup_ids);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PermissionCache';
        $metadata->setPrimaryTable([
            'name'    => 'permissions_cache',
            'indexes' => [
                'usergroup_key_idx' => ['columns' => ['usergroup_key']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'name',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'usergroup_key',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'usergroup_key',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'usergroup_ids',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'usergroup_ids',
        ]);
        $metadata->mapField([
            'fieldName'  => 'perms',
            'type'       => 'object',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'perms',
        ]);
    }
}

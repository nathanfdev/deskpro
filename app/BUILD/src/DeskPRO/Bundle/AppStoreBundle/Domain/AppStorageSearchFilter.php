<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppStorage\AccessPermission;

class AppStorageSearchFilter
{
    /** @var string */
    private $appId;

    /** @var AppStorage\EntityId */
    private $entityId;

    /** @var string[]|array */
    private $name;

    /** @var AccessPermission */
    private $accessPermission;

    /**
     * @param AppStorageItemIdentifier $id
     * @return AppStorageSearchFilter
     */
    public static function fromIdentifier(AppStorageItemIdentifier $id)
    {
        return new AppStorageSearchFilter(
            $id->getInstanceId(),
            $id->getEntityId(),
            $id->getName()
        );
    }

    /**
     * @param string $appId
     * @param string $entityId
     * @param string $name
     */
    public function __construct($appId, $entityId, $name = null)
    {
        $this->appId = $appId;
        $this->entityId = $entityId;
        $this->name = [(string) $name];
    }

    public function setAccessPermission(AccessPermission $accessPermission)
    {
        $this->accessPermission = $accessPermission;
    }

    /**
     * @return AccessPermission
     */
    public function getAccessPermision()
    {
        return $this->accessPermission;
    }

    /**
     * @return string
     */
    public function getApplicationInstanceId()
    {
        return (string) $this->appId;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return (string) $this->entityId;
    }

    /**
     * @return bool
     */
    public function hasName() {
        return !empty($this->name);
    }

    /**
     * @param string[]|array $nameList
     * @return AppStorageSearchFilter
     */
    public function setName($nameList)
    {
        $this->name = $nameList;
        return $this;
    }

    /**
     * @return string[]|array
     */
    public function getName()
    {
        return $this->name;
    }
}

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

class StateScope
{
    /** @var string */
    private $permission;

    /** @var string */
    private $targetObjectName;

    /** @var string */
    private $targetObjectId;

    /**
     * @param $string
     * @return null|StateScope
     */
    public static function parseString($string)
    {
        //TODO implement this
        return null;
    }

    public static function convertToString(StateScope $scope)
    {
        $string = $scope->getPermission();

        $targetObject = $scope->getTargetObjectName();
        if (empty($targetObject)) {
            return $string;
        }
        $string .= '.' . $targetObject;

        $targetId = $scope->getTargetObjectId();
        if (empty($targetId)) {
            return $string;
        }

        $string .= ':' . $targetId;
        return $string;
    }

    /**
     * @param $permission
     * @param $targetObjectName
     * @param $targetObjectId
     */
    public function __construct($permission, $targetObjectName, $targetObjectId)
    {
        $this->permission = $permission;
        $this->targetObjectName = $targetObjectName;
        $this->targetObjectId = $targetObjectId;
    }

    /**
     * @return string
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @return string
     */
    public function getTargetObjectName()
    {
        return $this->targetObjectName;
    }

    /**
     * @return string
     */
    public function getTargetObjectId()
    {
        return $this->targetObjectId;
    }
}

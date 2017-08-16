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

/**
 * @deprecated
 */
class StateScope
{
    /** @var string */
    private $permission;

    /** @var string */
    private $targetObjectName;

    /** @var string */
    private $targetObjectId;

    /**
     * @param StateScope $scope
     * @return bool
     */
    public static function isValid(StateScope $scope)
    {
        $validPermissions = [
            Constants::STATE_PERMISSION_PRIVATE,
            Constants::STATE_PERMISSION_SHARED
        ];

        if (
            $scope->getTargetObjectName() == Constants::STATE_TARGET_APPLICATION
            && null === $scope->getTargetObjectId()
            && in_array($scope->getPermission(), $validPermissions)
        ) {
            return true;
        }

        if (
            $scope->getTargetObjectName() != Constants::STATE_TARGET_APPLICATION
            && null !== $scope->getTargetObjectId()
            && in_array($scope->getPermission(), $validPermissions)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $string
     * @return null|StateScope
     */
    public static function parseString($string)
    {
        if (is_null($string)) {
            return null;
        }

        //let's use regex instead of a top-down parser
        $regexList = [
            'appPermission' => '^([a-z]+)\.([a-z]+)$',
            'objectPermission' => '^([a-z]+)\.([a-z]+):([1-9][0-9]*)$'
        ];

        /** @var string[] $matches */
        $matches = [];
        $representationType = null;
        foreach ($regexList as $representationType => $regex) {
            if (1 === preg_match(sprintf('#%s#i', $regex), $string, $matches)) {
                break;
            }
        }

        if (empty($matches)) {
            return null;
        }

        switch ($representationType) {
            case 'appPermission':
                return new StateScope($matches[1], $matches[2]);
                break;
            case 'objectPermission':
                return new StateScope($matches[1], $matches[2], $matches[3]);
                break;
        }

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
    public function __construct($permission, $targetObjectName, $targetObjectId = null)
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

    public function equals($mixed)
    {
        return $mixed instanceof StateScope
            && $this->permission === $mixed->getPermission()
            && $this->targetObjectName === $mixed->getTargetObjectName()
            && $this->targetObjectId === $mixed->getTargetObjectId()
        ;
    }
}

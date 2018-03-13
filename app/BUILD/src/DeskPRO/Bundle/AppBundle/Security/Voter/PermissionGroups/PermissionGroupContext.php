<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups;

use Doctrine\Common\Util\ClassUtils;

/**
 * Class PermissionGroupContext.
 */
class PermissionGroupContext
{
    /**
     * @var string
     */
    private $parentClass;

    /**
     * @var object
     */
    private $parent;

    /**
     * @var string
     */
    private $childClass;

    /**
     * @var mixed
     */
    private $child;

    /**
     * Constructor.
     *
     * @param object|string $parent
     * @param object|string $child
     */
    public function __construct($parent, $child = null)
    {
        if (is_object($parent)) {
            $this->parentClass = ClassUtils::getClass($parent);
            $this->parent      = $parent;
        } else {
            $this->parentClass = $parent;
        }

        if ($child) {
            if (is_object($child)) {
                $this->childClass = ClassUtils::getClass($child);
                $this->child      = $child;
            } else {
                $this->childClass = $child;
            }
        }
    }

    /**
     * @return string
     */
    public function getParentClass()
    {
        return $this->parentClass;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getChildClass()
    {
        return $this->childClass;
    }

    /**
     * @return mixed
     */
    public function getChild()
    {
        return $this->child;
    }
}

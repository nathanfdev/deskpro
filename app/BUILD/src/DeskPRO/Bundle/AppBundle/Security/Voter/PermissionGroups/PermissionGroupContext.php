<?php

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

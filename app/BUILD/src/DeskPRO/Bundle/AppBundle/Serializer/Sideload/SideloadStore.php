<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Sideload;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\Common\Proxy\Proxy;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class SideloadStore.
 */
class SideloadStore
{
    /**
     * @var array
     */
    private $sideloads;

    /**
     * @var array
     */
    private $classmap = [];

    /**
     * @var
     */
    private $loaded;

    /**
     * @var
     */
    private $notLoaded = [];

    /**
     * @var CustomSideload[][]
     */
    private $customs = [];

    /**
     * @var array
     */
    private $interests = [];

    /**
     * @var bool
     */
    private $interestsWasSet = false;

    /**
     * @param $entity
     */
    public function addSideload($entity)
    {
        $this->checkObject($entity);

        /* @var EntityInterface|DomainObject $entity */
        $fqcn = get_class($entity);

        if ($entity instanceof Proxy) {
            $reflection = new \ReflectionClass($entity);
            $parent     = $reflection->getParentClass();
            $fqcn       = $parent->getName();
        }

        $this->addSideloadString($fqcn, $entity->getId());
    }

    /**
     * @param mixed $class
     * @param mixed $id
     */
    public function addSideloadString($class, $id)
    {
        $snake = TypeUtils::getSnakeCaseBaseTypeName($class);
        if (!isset($this->sideloads[$snake])) {
            $this->sideloads[$snake] = [];
        }

        if ($this->interestsWasSet) {
            $this->updateNotLoaded($snake);
        }

        $this->sideloads[$snake][$id] = $id;
        $this->classmap[$snake]       = $class;
    }

    /**
     * @param string                   $interest
     * @param int                      $id
     * @param CallbackDeferredProperty $deferred
     * @param mixed                    $model
     */
    public function addCustomSideload($interest, $id, CallbackDeferredProperty $deferred, $model = null)
    {
        if (!isset($this->customs[$interest])) {
            $this->customs[$interest] = [];
        }

        $this->updateNotLoaded($interest);

        $this->customs[$interest][$id] = new CustomSideload($id, $deferred);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        if ($model && $propertyAccessor->isWritable($model, $interest)) {
            $propertyAccessor->setValue($model, $interest, new InlineCustomSideload($interest, $id));
        }
    }

    /**
     * @param array $interests
     */
    public function setInterests(array $interests = [])
    {
        $this->interestsWasSet = true;
        $this->interests       = $interests;

        foreach ($this->interests as $interest) {
            $fqcn = $this->getFqcn($interest);
            if ($fqcn || array_key_exists($interest, $this->customs)) {
                $this->updateNotLoaded($interest);
            }
        }
    }

    /**
     * @param $snake
     *
     * @return mixed
     */
    public function getFqcn($snake)
    {
        return isset($this->classmap[$snake]) ? $this->classmap[$snake] : null;
    }

    /**
     * @param string $interest
     *
     * @return array
     */
    public function getSideloads($interest)
    {
        $this->updateNotLoaded($interest, false);

        if (!isset($this->loaded[$interest])) {
            $this->loaded[$interest] = [];
        }

        $ids_to_load = array_diff($this->sideloads[$interest], $this->loaded[$interest]);

        $this->loaded[$interest] += $ids_to_load;

        return $ids_to_load;
    }

    /**
     * @param $interest
     *
     * @return CustomSideload[]
     */
    public function getCustom($interest)
    {
        $this->updateNotLoaded($interest, false);

        if (!isset($this->loaded[$interest])) {
            $this->loaded[$interest] = [];
        }
        $return = [];
        foreach ($this->customs[$interest] as $custom) {
            $id = is_numeric($custom->getId()) ? (int) $custom->getId() : $custom->getId();
            if (false === array_search($id, $this->loaded[$interest])) {
                $this->loaded[$interest][] = $id;
                $return[]                  = $custom;
            }
        }

        return $return;
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return array_merge(array_keys($this->classmap), array_keys($this->customs));
    }

    /**
     * @return mixed
     */
    public function hasSideloads()
    {
        $hasSideloads = array_reduce($this->notLoaded, [$this, 'reduce'], false);

        return $hasSideloads;
    }

    /**
     * @param $interest
     *
     * @return bool
     */
    public function hasCustom($interest)
    {
        return isset($this->customs[$interest]);
    }

    /**
     * @param $carry
     * @param $item
     *
     * @return bool
     */
    private function reduce($carry, $item)
    {
        return $carry || $item;
    }

    /**
     * @param $entity
     */
    private function checkObject($entity)
    {
        if (!is_object($entity)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'SideloadStore::addSideload expected $entity to be and object, but [ %s ] given',
                    TypeUtils::getVarType($entity)
                )
            );
        }

        if (!$entity instanceof EntityInterface && !$entity instanceof DomainObject) {
            throw new \InvalidArgumentException(
                sprintf(
                    'SideloadStore::addSideload expected $entity to be an instance of EntityInterface or DomainObject, but [ %s ] given',
                    TypeUtils::getBaseTypeName($entity)
                )
            );
        }
    }

    /**
     * @param      $interest
     * @param bool $notLoaded
     */
    private function updateNotLoaded($interest, $notLoaded = true)
    {
        $snake = TypeUtils::getSnakeCaseBaseTypeName($interest);
        if (in_array($snake, $this->interests)) {
            $this->notLoaded[$snake] = $notLoaded;
        }
    }
}

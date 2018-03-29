<?php

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\TypeUtils;

/**
 * Class FieldFilterService.
 */
class FieldFilterService
{
    /**
     * @var FieldFilterInterface[]
     */
    private $fieldsFilters = [];

    /**
     * @var array
     */
    private $filters = [];

    /**
     * @param FieldFilterInterface $filter
     * @param                      $name
     */
    public function registerFilter(FieldFilterInterface $filter, $name)
    {
        if (array_key_exists($name, $this->filters)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Filter with alias [ %s ] already registered, check your configuration',
                    $filter
                )
            );
        }
        $this->filters[$name] = $filter;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function getNamedFilter($name)
    {
        return array_key_exists($name, $this->filters) ? $this->filters[$name] : null;
    }

    /**
     * @param array  $filters
     * @param string $entityClass
     * @param string $field
     * @param string $action
     */
    public function buildFilters(array $filters, $entityClass, $field, $action)
    {
        foreach ($filters as $filter) {
            $this->buildFilter($filter, $entityClass, $field, $action);
        }
    }

    /**
     * @param mixed  $filter
     * @param string $entityClass
     * @param string $field
     * @param string $action
     */
    private function buildFilter($filter, $entityClass, $field, $action)
    {
        $key = $this->getKey($entityClass, $field, $action);

        switch (true) {
            case $createdFilter = $this->buildFromString($filter):
                break;
            case $createdFilter = $this->buildFromCallable($filter):
                break;
            case $createdFilter = $this->buildFromArray($filter):
                break;
            default:
                throw new \InvalidArgumentException('Field filter should be a service alias, a callable or a class name');
        }

        $this->fieldsFilters[$key][] = $createdFilter;
    }

    /**
     * @param       $filter
     * @param array $arguments
     *
     * @return null|object
     */
    private function buildFromString($filter, array $arguments = [])
    {
        if (is_string($filter)) {
            if (array_key_exists($filter, $this->filters)) {
                $createdFilter = $this->filters[$filter];
                if ($arguments) {
                    return new GenericFieldFilter([$createdFilter, 'filter'], $arguments);
                }

                return $createdFilter;
            } elseif (class_exists($filter) && $filter instanceof FieldFilterInterface) {
                if ($arguments) {
                    $reflection = new \ReflectionClass($filter);

                    return $reflection->newInstanceArgs($arguments);
                } else {
                    return new $filter();
                }
            }
        }

        return;
    }

    /**
     * @param       $filter
     * @param array $arguments
     *
     * @return GenericFieldFilter|null
     */
    private function buildFromCallable($filter, array $arguments = [])
    {
        if (is_callable($filter)) {
            return new GenericFieldFilter($filter, $arguments);
        }

        return;
    }

    /**
     * @param $filter
     *
     * @return GenericFieldFilter|null|object
     */
    private function buildFromArray($filter)
    {
        if (is_array($filter) && count($filter) == 2) {
            if ($inner = $this->buildFromString($filter[0], $filter[1])) {
                return $inner;
            } elseif ($inner = $this->buildFromCallable($filter[0], $filter[1])) {
                return $inner;
            }
        }

        return;
    }

    /**
     * @param EntityInterface|DomainObject $entity
     * @param string                       $field
     * @param string                       $action
     *
     * @return bool
     */
    public function hasFilters($entity, $field, $action)
    {
        $entityClass = TypeUtils::getEntityClass($entity);

        return isset($this->fieldsFilters[$this->getKey($entityClass, $field, $action)]);
    }

    /**
     * @param EntityInterface|DomainObject $entity
     * @param string                       $field
     * @param string                       $action
     * @param mixed                        $value
     *
     * @return array
     */
    public function filterField($entity, $field, $action, $value)
    {
        $entityClass = TypeUtils::getEntityClass($entity);
        $key         = $this->getKey($entityClass, $field, $action);

        if (isset($this->fieldsFilters[$key])) {
            foreach ($this->fieldsFilters[$key] as $filter) {
                /* @var FieldFilterInterface $filter */
                $value = $filter->filter($value);
            }
        }
        // just a precaution - should be called without hasFilters() check
        return $value;
    }

    /**
     * @param $entityClass
     * @param $field
     * @param $action
     *
     * @return mixed
     */
    private function getKey($entityClass, $field, $action)
    {
        return sprintf('%s::%s::%s', $entityClass, $field, $action);
    }
}

<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AuditBundle\Log\FieldFilter;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Util\TypeUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FieldFilterService.
 */
class FieldFilterService
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var FieldFilterInterface[]
     */
    private $fieldsFilters = [];

    /**
     * FieldFilterService constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param array  $filters
     * @param string $entityClass
     * @param string $field
     */
    public function registerFilters(array $filters, $entityClass, $field)
    {
        foreach ($filters as $filter) {
            $this->registerFilter($filter, $entityClass, $field);
        }
    }

    /**
     * @param mixed  $filter
     * @param string $entityClass
     * @param string $field
     */
    private function registerFilter($filter, $entityClass, $field)
    {
        $key = $entityClass.'::'.$field;

        switch (true) {
            case $createdFilter = $this->createFromString($filter):
                break;
            case $createdFilter = $this->createFromCallable($filter):
                break;
            case $createdFilter = $this->createFromArray($filter):
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
    private function createFromString($filter, array $arguments = [])
    {
        if (is_string($filter)) {
            if ($this->container->has('audit_log.field_filter.'.$filter)) {
                $createdFilter = $this->container->get('audit_log.field_filter.'.$filter);
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
    private function createFromCallable($filter, array $arguments = [])
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
    private function createFromArray($filter)
    {
        if (is_array($filter) && count($filter) == 2) {
            if ($inner = $this->createFromString($filter[0], $filter[1])) {
                return $inner;
            } elseif ($inner = $this->createFromCallable($filter[0], $filter[1])) {
                return $inner;
            }
        }

        return;
    }

    /**
     * @param EntityInterface|DomainObject $entity
     * @param string                       $field
     * @param array                        $values
     *
     * @return array
     */
    public function filterField($entity, $field, array $values)
    {
        $entityClass = TypeUtils::getEntityClass($entity);

        $key = $entityClass.'::'.$field;
        if (isset($this->fieldsFilters[$key])) {
            foreach ($this->fieldsFilters[$key] as $filter) {
                /* @var FieldFilterInterface $filter */
                foreach ($values as &$value) {
                    $value = $filter->filter($value);
                }
            }
        }

        return $values;
    }
}

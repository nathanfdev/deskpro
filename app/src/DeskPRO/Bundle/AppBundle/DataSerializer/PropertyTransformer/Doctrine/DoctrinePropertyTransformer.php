<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\Doctrine;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTypeMap;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\PropertyTransformationContext;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\PropertyTransformerInterface;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\DoctrineAssociation\DoctrineAssociationManager;
use Doctrine\Common\Collections\Collection;

/**
 * This will take care of doctrine association serialization.
 */
class DoctrinePropertyTransformer implements PropertyTransformerInterface
{
    /**
     * @var DoctrineAssociationManager
     */
    private $assoc_manager;

    /**
     * @var DataTypeMap
     */
    private $type_map;

    public function __construct(DoctrineAssociationManager $assoc_manager, DataTypeMap $type_map)
    {
        $this->assoc_manager = $assoc_manager;
        $this->type_map      = $type_map;
    }

    public function transform(PropertyTransformationContext $property_context)
    {
        $val                = $property_context->getValue();
        $data               = $property_context->getData();
        $property_name      = $property_context->getPropertyName();
        $serializer_context = $property_context->getSerializerContext();

        $new_val = null;
        if ($val instanceof DomainObject || $val instanceof EntityInterface) {
            $type              = $this->getType($data, $property_name);
            $doctrine_deferred = $this->assoc_manager->deferAssociationIds($data, $property_name);
            $new_val           = new DoctrineDeferredProperty($doctrine_deferred, $type);
            if ($serializer_context->isTypeIncluded($type)) {
                $serializer_context->getSideloads()->addDeferred(
                    $type,
                    new DoctrineDeferredInclude($doctrine_deferred)
                );
            }
        } elseif ($val === null || $val instanceof Collection || is_array($val) || $val instanceof \Traversable) {
            if ($this->assoc_manager->isAssociation($data, $property_name)) {
                // this is an association, we don't want to worry about getting these IDs yet
                $type              = $this->getType($data, $property_name);
                $doctrine_deferred = $this->assoc_manager->deferAssociationIds($data, $property_name);
                $new_val           = new DoctrineDeferredProperty($doctrine_deferred, $type);
                if ($serializer_context->isTypeIncluded($type)) {
                    $serializer_context->getSideloads()->addDeferred(
                        $type,
                        new DoctrineDeferredInclude($doctrine_deferred)
                    );
                }
            }
        }

        if ($new_val) {
            $property_context->transform($new_val);
        }
    }

    protected function getType($entity, $property_name)
    {
        $type = null;
        $meta = $this->assoc_manager->getMetadata($entity)->getAssociationMapping($property_name);
        if (array_key_exists('targetEntity', $meta)) {
            $type = $this->type_map->findTypeForClass($meta['targetEntity']);
        }

        return $type;
    }

    /**
     * @param DeferredPropertyInterface $deferred_property
     *
     * @return bool true if supports this deferred property, false otherwise
     */
    public function supportsDeferredProperty(DeferredPropertyInterface $deferred_property)
    {
        return $deferred_property instanceof DoctrineDeferredProperty
            || $deferred_property instanceof DoctrineDeferredInclude;
    }

    /**
     * @param DeferredPropertyInterface $deferred_property
     *
     * @return mixed
     */
    public function resolveDeferredProperty(DeferredPropertyInterface $deferred_property)
    {
        if ($deferred_property instanceof DoctrineDeferredProperty) {
            return $deferred_property->resolveProperty();
        }
        /* @var DoctrineDeferredInclude $deferred_property */
        return $deferred_property->resolveInclude();
    }
}

<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\Doctrine;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use DeskPRO\Component\DoctrineAssociation\Deferred\DeferredIdentity;
use Doctrine\Common\Collections\Collection;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\PropertyTransformationContext;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\PropertyTransformerInterface;
use DeskPRO\Component\DoctrineAssociation\DoctrineAssociationManager;

class DoctrinePropertyTransformer implements PropertyTransformerInterface
{
    /**
     * @var DoctrineAssociationManager
     */
    private $assoc_manager;

    public function __construct(DoctrineAssociationManager $assoc_manager)
    {
        $this->assoc_manager = $assoc_manager;
    }

    public function transform(PropertyTransformationContext $property_context)
    {
        $val = $property_context->getValue();
        $data = $property_context->getData();
        $property_name = $property_context->getPropertyName();

        $new_val = null;
        if ($val instanceof DomainObject || $val instanceof NotifyPropertyChangeEntity) {
            $new_val = new DoctrineDeferredProperty($this->assoc_manager->deferAssociationIds($data, $property_name));
        } elseif ($val instanceof Collection || is_array($val) || $val instanceof \Traversable || $val === null) {
            if ($this->assoc_manager->isAssociation($data, $property_name)) {
                // this is an association, we don't want to worry about getting these IDs yet
                $new_val = new DoctrineDeferredProperty($this->assoc_manager->deferAssociationIds($data, $property_name));
            }
        }

        if ($new_val) {
            $property_context->transform($new_val);
        }
    }

    /**
     * @param DeferredPropertyInterface $deferred_property
     * @return bool true if supports this deferred property, false otherwise
     */
    public function supportsDeferredProperty(DeferredPropertyInterface $deferred_property)
    {
        return $deferred_property instanceof DoctrineDeferredProperty;
    }

    /**
     * @param DeferredPropertyInterface $deferred_property
     * @return mixed
     */
    public function resolveDeferredProperty(DeferredPropertyInterface $deferred_property)
    {
        /** @var DoctrineDeferredProperty $deferred_property */
        return $deferred_property->resolve();
    }
}

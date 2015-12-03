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

namespace DeskPRO\Bundle\AppBundle\DataSerializer;

use DeskPRO\Bundle\AppBundle\DataSerializer\Exception\DataSerializerException;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\PropertyTransformationContext;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\PropertyTransformerInterface;

/**
 * Transforms properties that were whitelisted for auto-inclusion in the serialization process by the Transformer.
 *
 * While transfomers do allow you to explicitly transform properties, most of the properites we serialize are taken
 * care of automatically (dates, string, ints, bools, doctrine associations). These are whitelisted in the transformer
 * and this sub-system needs to transform them automatically.
 *
 * This service transforms every whitelisted property of every piece of data we serialize.
 *
 * It uses a collection of PropertyTransformer\PropertyTransformerInterface's to do this, so you can plug
 * into the process by injecting a new PropertyTransfomer into this service. See the DoctrinePropertyTransformer for
 * example.
 */
class DataPropertyTransformer
{
    /**
     * @var PropertyTransformerInterface[]
     */
    private $property_transformers;

    public function __construct(array $property_transformers)
    {
        $this->property_transformers = $property_transformers;
    }

    public function transform(PropertyTransformationContext $property_context)
    {
        foreach ($this->property_transformers as $transformer) {
            $transformer->transform($property_context);
        }
    }

    /**
     * During transform(), a property transformer might have returned a deferred property. This is a place where the
     * property transformer can now resolve that deferred property.
     *
     * @param DeferredPropertyInterface $deferred_property
     *
     * @throws DataSerializerException
     *
     * @return mixed
     */
    public function resolveDeferredProperty(DeferredPropertyInterface $deferred_property)
    {
        foreach ($this->property_transformers as $transformer) {
            if ($transformer->supportsDeferredProperty($deferred_property)) {
                return $transformer->resolveDeferredProperty($deferred_property);
            }
        }

        throw new DataSerializerException(
            'deferred property not supported by data serializer: '.get_class($deferred_property)
        );
    }
}

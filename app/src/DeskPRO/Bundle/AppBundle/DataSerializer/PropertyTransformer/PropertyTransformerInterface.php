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
namespace DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer;

/**
 * Every "white listed" property in a DataSerializerTransformer is run through every PropertyTransformerInterface.
 *
 * The goal here is to take a value that is on the data we are serializing (be it an entity/object property, or a value on a key in an arbitrary array) and filter it in some way so that it can be properly serialized.
 *
 * For example, \DateTime objects need to be converted to the correct output format, and doctrine associations
 * need to be turned into an array of IDs.
 *
 * This allows for various "plugins" or "PropertyTransformerInterface's" to be injected and used to
 * manipulate certain properties or property values.
 *
 * See the Doctrine\DoctrinePropertyTransformer as an example of how you can plug in to this process.
 *
 * Everything is done in context, so you can get the data, property name, and value from the context.
 *
 * If you want to transform it, you can call $property_context->transform('new value').
 *
 * You can also check to see if the value was already transformed $property_context->isTransformed() and
 * ignore it if so (but some transformers might not care, and want to transform previously transformed properties).
 */
interface PropertyTransformerInterface
{
    /**
     * @param PropertyTransformationContext $property_context
     */
    public function transform(PropertyTransformationContext $property_context);

    /**
     * @param DeferredPropertyInterface $deferred_property
     *
     * @return bool true if supports this deferred property, false otherwise
     */
    public function supportsDeferredProperty(DeferredPropertyInterface $deferred_property);

    /**
     * @param DeferredPropertyInterface $deferred_property
     *
     * @return mixed
     */
    public function resolveDeferredProperty(DeferredPropertyInterface $deferred_property);
}

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

/**
 * All events are fired with a DataSerializerEvent, which contains the mutable DataSerializeContext.
 */
class DataSerializerEvents
{
    /**
     * This is fired as soon as the DataSerializer::serilize method starts, and is a
     * stardard way to find the "type" for the context, or to mutate the context.
     */
    const PRE_SERIALIZE = 'data_serializer.event.pre_serialize';

    /**
     * Last chance to mutate the context before the transformer is called.
     */
    const PRE_TRANSFORM = 'data_serializer.event.pre_transform';

    /**
     * Do the actual transformation of the main data... the job here is to take $context->getMainData() and then
     * set the transformed array onto $context->setMainTransformed(), usually by using the $context->getTransformer()
     * which is set in PRE_TRANSFORM.
     */
    const TRANSFORM = 'data_serializer.event.transform';

    /**
     * The $context now has the getMainTransformed() array from the transformer, and the $context is now open to
     * mutation from the listeners. This is where we start mutating the array in $context->getSerializedArray().
     */
    const POST_SERIALIZE = 'data_serializer.event.post_serialize';

    /**
     * A final pass on the $context before returning the $context->getSerializedArray() data.
     */
    const POST_TRANSFORM = 'data_serializer.event.post_transform';
}

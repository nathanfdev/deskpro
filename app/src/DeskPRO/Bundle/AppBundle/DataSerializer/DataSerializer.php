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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * The main entry point into the DataSerializer package, which simply calls an ordered series of events.
 */
class DataSerializer
{
    /**
     * @var EventDispatcherInterface
     */
    private $event_dispatcher;

    /**
     * @var DataTypeIdFinder
     */
    private $id_finder;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $event_dispatcher
     * @param DataTypeIdFinder         $id_finder
     */
    public function __construct(
        EventDispatcherInterface $event_dispatcher,
        DataTypeIdFinder $id_finder
    ) {
        $this->event_dispatcher = $event_dispatcher;
        $this->id_finder        = $id_finder;
    }

    /**
     * @param mixed       $data            required, the data you are serializing (an entity, an object, or an arbitrary array)
     * @param string|null $includes_string optional, you can specify a comma separated list of "types" and any time
     *                                     the serialized objects contain an array of IDs of one of these types, we
     *                                     will also side load the data as an include.
     * @param null        $view            optional, each type transformer can support more than 1 view. the default is the
     *                                     "main" or "standard" api view and if that is what you need you can leave this null
     * @param null        $type            optional, the type map is used, this only exists for edge cases where the map can't
     *                                     find the correct transformer (if you are serializing an arbitrary array for example)
     *
     * @throws DataSerializerException
     *
     * @return array
     */
    public function serialize($data, $includes_string = null, $view = null, $type = null)
    {
        /*
         * CREATE CONTEXT
         *
         * this context holds all state for this serialization, and it is passed to every event and mutated by
         * those events until it finally holds the final serialized array.
         */
        $context = DataSerializerContext::create($data, $includes_string, $view, $type, $this->id_finder);

        /*
         * PRE_SERIALIZE
         *
         * core listeners:
         * EventListener\TypeListener - maps an api object "type" onto the context
         */
        $this->event_dispatcher->dispatch(DataSerializerEvents::PRE_SERIALIZE, new DataSerializerEvent($context));

        /*
         * PRE_TRANSFORM
         *
         * core listeners:
         * EventListener\TransformerListener - find the proper transformer based on "type" and put it in the context
         */
        $this->event_dispatcher->dispatch(DataSerializerEvents::PRE_TRANSFORM, new DataSerializerEvent($context));

        /*
         * TRANSFORM
         *
         * core listeners:
         * EventListener\TransformerListener - execute the transformer and call $context->setMainTransformed()
         */
        $this->event_dispatcher->dispatch(DataSerializerEvents::TRANSFORM, new DataSerializerEvent($context));

        /*
         * POST_TRANSFORM
         *
         * core listeners:
         * EventListener\JsonApiFormatListener - decorates the main transformation array with api format, and starts
         *                                       setting up the final $context->getSerializedArray()
         * EventListener\DeferredPropertiesListener - deal with deferred properties
         */
        $this->event_dispatcher->dispatch(DataSerializerEvents::POST_TRANSFORM, new DataSerializerEvent($context));

        /*
         * POST_SERIALIZE
         *
         * core listeners:
         * EventListener\SideloadListener - now that serilization of the main is done, process includes
         */
        $this->event_dispatcher->dispatch(DataSerializerEvents::POST_SERIALIZE, new DataSerializerEvent($context));

        /*
         * DONE
         *
         * the context should now have the final serialized array ready to be returned
         */
        return $context->getSerializedArray();
    }
}

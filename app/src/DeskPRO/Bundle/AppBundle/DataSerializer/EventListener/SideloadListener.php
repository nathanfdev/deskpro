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

namespace DeskPRO\Bundle\AppBundle\DataSerializer\EventListener;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataPropertyTransformer;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerEvent;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializerEvents;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerFactory;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Process the $includes array in the DataSerializerContext into $includes_processed.
 */
class SideloadListener implements EventSubscriberInterface
{
    /**
     * @var DataTransformerFactory
     */
    private $data_transformer;

    /**
     * @var DataPropertyTransformer
     */
    private $property_transformer;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var DeferredPropertiesListener
     */
    private $deferred_properties_listener;

    public function __construct(
        DataTransformer $data_transformer,
        DataPropertyTransformer $property_transformer,
        DeferredPropertiesListener $deferred_properties_listener,
        LoggerInterface $logger
    ) {
        $this->data_transformer             = $data_transformer;
        $this->property_transformer         = $property_transformer;
        $this->deferred_properties_listener = $deferred_properties_listener;
        $this->logger                       = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            DataSerializerEvents::POST_SERIALIZE => ['postSerialize', 0],
        ];
    }

    /**
     * Process the $context->getSideloads() object, and add it to "linked" in the serialized array.
     */
    public function postSerialize(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        $sideloads = $context->getSideloads();

        // keep looping and resolving sideloads until there are none left to resolve
        while ($sideloads->hasUnprocessed()) {
            foreach ($sideloads->getAndClearDeferred() as $type => $deferred_array) {
                foreach ($deferred_array as $deferred_property) {
                    $sideload_data = null;
                    if ($deferred_property instanceof DeferredPropertyInterface) {
                        // this value was deferred, so we need to resolve it before transforming it
                        // i.e. if this was a deferred doctrine value, we need to get the entity first and then transform it
                        $sideload_data = $this->property_transformer->resolveDeferredProperty($deferred_property);
                    }

                    if (is_array($sideload_data) || $sideload_data instanceof \Traversable) {
                        $sideloads->addSideloadCollection($type, $sideload_data);
                    } elseif ($sideload_data) {
                        $sideloads->addSideloadData($type, $sideload_data);
                    }
                }
            }

            $sideloads->processCollections();

            $sideloads_transformed = [];
            $processing            = $sideloads->getSideloadData();
            foreach ($processing as $type => $datas) {
                foreach ($datas as $id => $data) {
                    $transformation_request            = new DataTransformerRequest($data, $context);
                    $transformer_response              = $this->data_transformer->transform($transformation_request);
                    $sideloads_transformed[$type][$id] = $transformer_response->getTransformed();
                }
            }

            $serialized = $context->getSerializedArray();

            $sideloads_transformed = $this->deferred_properties_listener->processArrayDeferredProperties(
                $sideloads_transformed
            );
            $serialized['linked'] = $sideloads_transformed;

            $context->setSerializedArray($serialized);
        }
    }
}

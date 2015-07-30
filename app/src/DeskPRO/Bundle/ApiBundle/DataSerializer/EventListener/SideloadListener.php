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

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\EventListener;


use DeskPRO\Bundle\ApiBundle\DataSerializer\DataPropertyTransformer;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerEvent;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerEvents;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerFactory;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use DeskPRO\Bundle\ApiBundle\DataSerializer\Transformer\AbstractDataSerializerTransformer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Process the $includes array in the DataSerializerContext into $includes_processed
 */
class SideloadListener implements EventSubscriberInterface
{
    /**
     * @var DataTransformerFactory
     */
    private $transformer_factory;

    /**
     * @var DataPropertyTransformer
     */
    private $property_transformer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DataTransformerFactory $transformer_factory,
        DataPropertyTransformer $property_transformer,
        LoggerInterface $logger
    )
    {
        $this->transformer_factory = $transformer_factory;
        $this->property_transformer = $property_transformer;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            DataSerializerEvents::POST_TRANSFORM => ['postTransform', -128],
            DataSerializerEvents::POST_SERIALIZE => ['postSerialize', 0],
        ];
    }

    /**
     * For any include value, we need to transform it and add it to the $context as a processed include.
     */
    public function postTransform(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        $includes = $context->getIncludes();

        foreach ($includes as $type => $type_includes) {
            $transformed = [];
            $transformer = $this->transformer_factory->findByType($type);
            foreach ($type_includes as $type_include) {
                if ($type_include instanceof DeferredPropertyInterface) {
                    // this value was deferred, so we need to resolve it before transforming it
                    // i.e. if this was a deferred doctrine value, we need to get the entity first and then transform it
                    $type_include = $this->property_transformer->resolveDeferredProperty($type_include);
                }

                // this one was not deferred, and we already have the actual array
                $transformed[] = $transformer->transform($type_include);
            }

            foreach ($transformed as $transformed_include) {
                $context->addTransformedInclude($type, $transformed_include);
            }

        }

        $context->clearIncludes(); // these were dealt with already, celar them and make way for the next pass
        // TODO: do this recursively
    }

    /**
     * All of the $includes should be processed, and now we can finalize the side-load data into the serialized array.
     *
     * @param DataSerializerEvent $event
     */
    public function postSerialize(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        // TODO: recursion should be dealt with, confirm.

        $serialized = $context->getSerializedArray();

        if ($linked = $context->getIncludesTransformed()) {
            $serialized['linked'] = $linked;
        }

        $context->setSerializedArray($serialized);
    }
}

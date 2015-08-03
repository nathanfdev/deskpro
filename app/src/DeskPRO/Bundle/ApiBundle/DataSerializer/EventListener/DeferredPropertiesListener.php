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
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerContext;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerEvent;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataSerializerEvents;
use DeskPRO\Bundle\ApiBundle\DataSerializer\Exception\DataSerializerException;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Recursively finds DeferredPropertyInterface's in the serialized array and processes them
 */
class DeferredPropertiesListener implements EventSubscriberInterface
{
    /**
     * @var DataPropertyTransformer
     */
    private $property_transformer;

    public function __construct(DataPropertyTransformer $property_transformer, LoggerInterface $logger)
    {
        $this->property_transformer = $property_transformer;
    }

    public static function getSubscribedEvents()
    {
        return [
            DataSerializerEvents::POST_TRANSFORM => ['postTransform', 0]
        ];
    }

    public function postTransform(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        $this->processDeferredPropertiesInSerializedArray($context);
    }

    protected function processDeferredPropertiesInSerializedArray(DataSerializerContext $context)
    {
        // this is a big array that might have some deferred properties to deal with
        // we now replace the deferred properties with the resolved values
        $transformed = $context->getSerializedArray();
        $context->setSerializedArray($this->processArrayDeferredProperties($transformed));
    }

    /**
     * This is public because the SideloadListener uses this service and calls this method...
     *
     * @param array $data
     * @return array
     * @throws DataSerializerException
     */
    public function processArrayDeferredProperties(array $data)
    {
        $processed = [];

        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $processed[$key] = $this->processArrayDeferredProperties($val);
            } elseif ($val instanceof DeferredPropertyInterface) {
                $processed[$key] = $this->property_transformer->resolveDeferredProperty($val);
            } else {
                $processed[$key] = $val;
            }
        }

        return $processed;
    }
}

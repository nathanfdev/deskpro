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
use DeskPRO\Bundle\ApiBundle\DataSerializer\Transformer\AbstractDataSerializerTransformer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Find the right transformer during PRE_TRANSFORM and execute it during TRANSFORM
 */
class TransformerListener implements EventSubscriberInterface
{
    /**
     * @var DataTransformerFactory
     */
    private $transformer_factory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        DataTransformerFactory $transformer_factory,
        LoggerInterface $logger
    )
    {
        $this->transformer_factory = $transformer_factory;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            DataSerializerEvents::PRE_TRANSFORM => ['preTransform', 0],
            DataSerializerEvents::TRANSFORM => ['transform', 0]
        ];
    }

    /**
     * Get the transformer for this context "type"
     */
    public function preTransform(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        // normally the type is found and set onto the $context via the TypeListener
        $transformer = $this->transformer_factory->findByType($context->getMainType());

        $context->setMainTransformer($transformer);
    }

    /**
     * Now execute the transformer and set the "main transformed data"
     */
    public function transform(DataSerializerEvent $event)
    {
        $context = $event->getContext();

        $transformed_array = $context->getMainTransformer()->transform($context);
        $context->setMainTransformed($transformed_array);
    }
}

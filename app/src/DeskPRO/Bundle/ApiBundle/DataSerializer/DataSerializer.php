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

namespace DeskPRO\Bundle\ApiBundle\DataSerializer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\Exception\DataSerializerException;
use DeskPRO\Bundle\ApiBundle\DataSerializer\PropertyTransformer\DeferredPropertyInterface;
use DeskPRO\Bundle\ApiBundle\DataSerializer\Transformer\AbstractDataSerializerTransformer;
use Psr\Log\LoggerInterface;

/**
 * The main entry point into the DataSerializer package.
 */
class DataSerializer
{
    /**
     * @var DataTypeMap
     */
    private $type_map;

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
        DataTypeMap $type_map,
        DataTransformerFactory $transformer_factory,
        DataPropertyTransformer $property_transformer,
        LoggerInterface $logger
    )
    {
        $this->type_map = $type_map;
        $this->transformer_factory = $transformer_factory;
        $this->property_transformer = $property_transformer;
        $this->logger = $logger;
    }

    /**
     * @param mixed $data required, the data you are serializing (an entity, an object, or an arbitrary array)
     * @param string|null $includes_string optional, you can specify a comma separated list of "types" and any time
     *                              the serialized objects contain an array of IDs of one of these types, we
     *                              will also side load the data as an include.
     * @param null $view optional, each type transformer can support more than 1 view. the default is the
     *                   "main" or "standard" api view and if that is what you need you can leave this null
     * @param null $type optional, the type map is used, this only exists for edge cases where the map can't
     *                   find the correct transformer (if you are serializing an arbitrary array for example)
     * @return array
     * @throws DataSerializerException
     */
    public function serialize($data, $includes_string = null, $view = null, $type = null)
    {
        if (null === $type) {
            if (!$type = $this->type_map->findType($data)) {
                throw new DataSerializerException('could not find object type for given data, and no specific type provided');
            }
        }

        $transformer = $this->transformer_factory->findByType($type);
        if ($transformer instanceof AbstractDataSerializerTransformer) {
            $transformer->setPropertyTransformer($this->property_transformer);
            $transformer->setLogger($this->logger);
        }

        $context = DataSerializerContext::create($data, $includes_string, $type, $view);

        $context->setMainTransformed($transformer->transform($context));

        // the following call will happen recursively as we side-load data
        $this->processPass($context);

        return ['data' => $context->getMainTransformed()];
    }

    protected function processPass(DataSerializerContext $context)
    {
        // this is a big array that might have some deferred properties to deal with
        // we now replace the deferred properties with the resolved values
        $transformed = $context->getMainTransformed();
        $context->setMainTransformed($this->processArrayDeferredProperties($transformed));

        // TODO: side-loading
    }

    protected function processArrayDeferredProperties(array $data)
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

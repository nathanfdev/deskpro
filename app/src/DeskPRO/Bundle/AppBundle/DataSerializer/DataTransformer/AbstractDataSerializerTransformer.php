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
namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataPropertyTransformer;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\DataSerializer\PropertyTransformer\PropertyTransformationContext;
use Psr\Log\LoggerInterface;

/**
 * Base class for all DataTypeTransformers. It offers abstract methods that simplify the transform() method.
 * Most children will need to only implement the getAutomaticProperties() and getCustomProperties() methods.
 * However, it is possible to implement DataSerializerTransformerInterface yourself if you don't need this
 * abstraction.
 */
abstract class AbstractDataSerializerTransformer implements DataSerializerTransformerInterface
{
    /**
     * @var DataPropertyTransformer
     */
    protected $property_transformer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * The context contains all of the data needed to do the transformation, including what "view" and also
     * what is going to be "included" (side-loaded). The job of the transformer is to call setMainTransformed()
     * on the context with the transformed data.
     *
     * @param DataTransformerRequest $transformation_request
     *
     * @return array
     */
    public function transform(DataTransformerRequest $transformation_request)
    {
        $data        = $transformation_request->getDataToBeTransformed();
        $transformed = [];

        foreach ($this->getAutomaticProperties($transformation_request) as $property_name) {
            $property_context = new PropertyTransformationContext($data, $property_name, $transformation_request->getSerializerContext());

            $this->property_transformer->transform($property_context);

            if ($property_context->isTransformed()) {
                $transformation = $property_context->getTransformedValue();
            } elseif (!$property_context->isTransformed() && is_scalar($property_context->getValue())) {
                $transformation = $property_context->getValue();
            } else {
                // a catch-all in case a non-scalar was not transformed
                $this->logger->warning(
                    'failed to transform a non-scalar value during serialization',
                    [$property_context->getValue()]
                );
                $transformation = null;
            }

            $transformed[$property_name] = $transformation;
        }

        if (!$transformed) {
            return $this->getCustomProperties($transformation_request);
        } else {
            return array_merge($transformed, $this->getCustomProperties($transformation_request));
        }
    }

    /**
     * Before being asked to transform data, the serializer will call this method.
     *
     * @param DataPropertyTransformer $property_transformer
     */
    public function setPropertyTransformer(DataPropertyTransformer $property_transformer)
    {
        $this->property_transformer = $property_transformer;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}

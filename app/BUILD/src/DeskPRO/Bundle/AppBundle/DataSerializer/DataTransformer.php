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

/**
 * Transforms data using child data transformers. Incoming data are associated to a particular DataTypeTransfomer via the DataTypeMap, which is configurable.
 */
class DataTransformer
{
    /**
     * @var DataTransformerRegistry
     */
    private $transformed_registry;
    /**
     * @var DataTypeMap
     */
    private $type_map;

    /**
     * @var DataTransformerFactory
     */
    private $transformer_factory;

    /**
     * @var DataTypeIdFinder
     */
    private $id_finder;

    /**
     * Constructor.
     *
     * @param DataTransformerRegistry $transformed_registry
     * @param DataTransformerFactory  $transformer_factory
     * @param DataTypeMap             $type_map
     * @param DataTypeIdFinder        $id_finder
     */
    public function __construct(
        DataTransformerRegistry $transformed_registry,
        DataTransformerFactory $transformer_factory,
        DataTypeMap $type_map,
        DataTypeIdFinder $id_finder
    ) {
        $this->transformed_registry = $transformed_registry;
        $this->type_map             = $type_map;
        $this->transformer_factory  = $transformer_factory;
        $this->id_finder            = $id_finder;
    }

    /**
     * @param mixed $data
     *
     * @return bool
     */
    public function canTransformData($data)
    {
        if ($data === null || empty($data)) {
            return true;
        }

        $type = $this->type_map->findType($data);

        if ($type && $this->transformer_factory->hasType($type)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param DataTransformerRequest $transformation_request
     *
     * @throws DataSerializerException
     *
     * @return DataTransformerResponse
     */
    public function transform(DataTransformerRequest $transformation_request)
    {
        $data = $transformation_request->getDataToBeTransformed();

        if ($data === null || empty($data)) {
            return new DataTransformerResponse($transformation_request, $transformation_request->getView(), null, null, []);
        }

        $type = $this->type_map->findType($data);
        $id   = $this->id_finder->findDataId($data);

        if ($type && $id) {
            if (!$transformed = $this->transformed_registry->getTransformed($type, $id)) {
                $transformed = $this->doTransform($transformation_request, $type);
                $this->transformed_registry->registerTransformed($type, $id, $transformed);
            }

            $transformed = $this->transformed_registry->getTransformed($type, $id);
        } elseif ($type) {
            $transformed = $this->doTransform($transformation_request, $type);
        } else {
            throw new DataSerializerException('could not find data type');
        }

        return new DataTransformerResponse(
            $transformation_request,
            $transformation_request->getView(),
            $type,
            $id,
            $transformed
        );
    }

    /**
     * @param mixed                 $data
     * @param DataSerializerContext $context
     *
     * @return mixed
     */
    public function recursiveTransform($data, DataSerializerContext $context)
    {
        if (is_object($data) && $this->canTransformData($data)) {
            $transformation_request = new DataTransformerRequest(
                $data,
                $context,
                $context->getMainView() ?: DataTransformerRequest::DEFAULT_VIEW
            );

            $transformed = $this->transform($transformation_request);
            $data        = $this->recursiveTransform($transformed->getTransformed(), $context);
        } elseif (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as &$value) {
                $value = $this->recursiveTransform($value, $context);
            }
        }

        return $data;
    }

    /**
     * @param DataTransformerRequest $transformation_request
     * @param $type
     *
     * @return array
     */
    protected function doTransform(DataTransformerRequest $transformation_request, $type)
    {
        $transformer = $this->transformer_factory->findByType($type);
        $transformed = $transformer->transform($transformation_request);

        return $transformed;
    }
}

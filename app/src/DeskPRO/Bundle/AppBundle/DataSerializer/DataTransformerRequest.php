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
 * DataTransformers return an instance of this class. It represents the "response" of a DataTransformer.
 */
class DataTransformerRequest
{
    const DEFAULT_VIEW = 'default';

    private $data_to_be_transformed;
    private $serializer_context;
    private $view;

    public function __construct(
        $data_to_be_transformed,
        DataSerializerContext $serializer_context,
        $view = self::DEFAULT_VIEW
    ) {
        $this->data_to_be_transformed = $data_to_be_transformed;
        $this->serializer_context     = $serializer_context;
        $this->view                   = $view;
    }

    /**
     * @return mixed
     */
    public function getDataToBeTransformed()
    {
        return $this->data_to_be_transformed;
    }

    public function isDefaultView()
    {
        return self::DEFAULT_VIEW === $this->view;
    }

    /**
     * @return DataSerializerContext
     */
    public function getSerializerContext()
    {
        return $this->serializer_context;
    }

    /**
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }
}

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
class DataTransformerResponse
{
    /**
     * @var array
     */
    private $transformed;

    /**
     * @var DataTransformerRequest
     */
    private $transformation_request;
    private $view;
    private $type;
    private $id;

    public function __construct(
        DataTransformerRequest $transformation_request,
        $view,
        $type,
        $id,
        array $transformed
    ) {
        $this->transformation_request = $transformation_request;
        $this->view                   = $view;
        $this->type                   = $type;
        $this->id                     = $id;
        $this->transformed            = $transformed;
    }

    /**
     * @return array
     */
    public function getTransformed()
    {
        return $this->transformed;
    }

    /**
     * @return DataTransformerRequest
     */
    public function getTransformationRequest()
    {
        return $this->transformation_request;
    }

    /**
     * @return mixed
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}

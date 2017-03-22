<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation;

use Nelmio\ApiDocBundle\Annotation\ApiDoc as BaseApiDoc;

/**
 * @Annotation
 */
class ApiDoc extends BaseApiDoc
{
    /**
     * @var string
     */
    protected $target;

    /**
     * @var array
     */
    protected $apiModes;

    /**
     * @var array
     */
    protected $apiTags;

    /**
     * @var string
     */
    protected $classOutput;

    /**
     * @var string
     */
    protected $classInput;

    /**
     * Constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        if (isset($data['target'])) {
            $this->target = $data['target'];
        }

        parent::__construct($data);
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param mixed $modes
     *
     * @return $this
     */
    public function setApiModes($modes)
    {
        $this->apiModes = $modes;

        return $this;
    }

    /**
     * @param mixed $tags
     *
     * @return $this
     */
    public function setApiTags($tags)
    {
        $this->apiTags = $tags;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApiModes()
    {
        return $this->apiModes;
    }

    /**
     * @return mixed
     */
    public function getApiTags()
    {
        return $this->apiTags;
    }

    /**
     * @param mixed $classOutput
     *
     * @return $this
     */
    public function setClassOutput($classOutput)
    {
        $this->classOutput = $classOutput;

        return $this;
    }

    /**
     * @param mixed $classInput
     *
     * @return $this
     */
    public function setClassInput($classInput)
    {
        $this->classInput = $classInput;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data                = parent::toArray();
        $data['apiModes']    = $this->apiModes;
        $data['apiTags']     = $this->apiTags;
        $data['classOutput'] = $this->classOutput;
        $data['classInput']  = $this->classOutput;
        $data['target']      = $this->target;

        return $data;
    }

    /**
     * @return null|string
     */
    public function getOutput()
    {
        $output = parent::getOutput();
        if (!$output) {
            $output = $this->classOutput;
        }

        return $output;
    }

    /**
     * @return null|string
     */
    public function getInput()
    {
        $input = parent::getInput();
        if (!$input) {
            $input = $this->classInput;
        }

        return $input;
    }
}

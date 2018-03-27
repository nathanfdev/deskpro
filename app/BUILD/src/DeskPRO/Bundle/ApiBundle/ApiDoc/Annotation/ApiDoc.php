<?php

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
     * @var bool
     */
    protected $noOutput = false;

    /**
     * @var bool
     */
    protected $noInput = false;

    /**
     * @var string
     */
    protected $documentationOverride = '';

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
        if (isset($data['noOutput'])) {
            $this->noOutput = $data['noOutput'];
        }
        if (isset($data['noInput'])) {
            $this->noInput = $data['noInput'];
        }
        if (isset($data['documentation'])) {
            $this->documentationOverride = $data['documentation'];
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
        $data['classInput']  = $this->classInput;
        $data['target']      = $this->target;

        if ($this->documentationOverride) {
            $data['documentation'] = $this->documentationOverride;
        }

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

    /**
     * @return bool
     */
    public function isNoOutput()
    {
        return $this->noOutput;
    }

    /**
     * @param bool $noOutput
     *
     * @return $this
     */
    public function setNoOutput($noOutput)
    {
        $this->noOutput = $noOutput;

        return $this;
    }

    /**
     * @return bool
     */
    public function isNoInput()
    {
        return $this->noInput;
    }

    /**
     * @param bool $noInput
     *
     * @return $this
     */
    public function setNoInput($noInput)
    {
        $this->noInput = $noInput;

        return $this;
    }

    /**
     * @return string
     */
    public function getDocumentationOverride()
    {
        return $this->documentationOverride;
    }

    /**
     * @param string $documentationOverride
     *
     * @return $this
     */
    public function setDocumentationOverride($documentationOverride)
    {
        $this->documentationOverride = $documentationOverride;

        return $this;
    }
}

<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class ChatPermissionSettings.
 */
class ChatPermissionSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $viewTranscripts;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $viewUnassigned;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $viewOthers;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $createLabels;

    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $delete;

    /**
     * @return bool
     */
    public function isViewTranscripts()
    {
        return $this->viewTranscripts;
    }

    /**
     * @param bool $viewTranscripts
     *
     * @return $this
     */
    public function setViewTranscripts($viewTranscripts)
    {
        $this->viewTranscripts = $viewTranscripts;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewUnassigned()
    {
        return $this->viewUnassigned;
    }

    /**
     * @param bool $viewUnassigned
     *
     * @return $this
     */
    public function setViewUnassigned($viewUnassigned)
    {
        $this->viewUnassigned = $viewUnassigned;

        return $this;
    }

    /**
     * @return bool
     */
    public function isViewOthers()
    {
        return $this->viewOthers;
    }

    /**
     * @param bool $viewOthers
     *
     * @return $this
     */
    public function setViewOthers($viewOthers)
    {
        $this->viewOthers = $viewOthers;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCreateLabels()
    {
        return $this->createLabels;
    }

    /**
     * @param bool $createLabels
     *
     * @return $this
     */
    public function setCreateLabels($createLabels)
    {
        $this->createLabels = $createLabels;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * @param mixed $delete
     *
     * @return $this
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;

        return $this;
    }
}

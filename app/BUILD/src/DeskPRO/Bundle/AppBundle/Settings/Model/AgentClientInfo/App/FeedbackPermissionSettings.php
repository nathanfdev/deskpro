<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App;

use JMS\Serializer\Annotation as JMS;

/**
 * Class FeedbackPermissionSettings.
 */
class FeedbackPermissionSettings
{
    /**
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    private $createLabels;

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
}

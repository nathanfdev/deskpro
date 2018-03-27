<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AttachmentsSettings.
 */
class AttachmentsSettings
{
    /**
     * @var AttachmentSettings
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\Core\Attachments\AttachmentSettings")
     */
    private $agents;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->agents = new AttachmentSettings();
    }

    /**
     * @return AttachmentSettings
     */
    public function getAgents()
    {
        return $this->agents;
    }
}

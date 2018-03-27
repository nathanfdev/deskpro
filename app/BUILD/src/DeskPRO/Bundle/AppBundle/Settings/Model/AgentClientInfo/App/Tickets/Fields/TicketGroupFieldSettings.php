<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields;

use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketGroupFieldSettings.
 */
class TicketGroupFieldSettings extends AbstractFieldSettings
{
    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $fieldId;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $type
     * @param int    $fieldId
     */
    public function __construct($id, $type, $fieldId)
    {
        parent::__construct($id, $type);

        $this->fieldId = $fieldId;
    }

    /**
     * @return int
     */
    public function getFieldId()
    {
        return $this->fieldId;
    }
}

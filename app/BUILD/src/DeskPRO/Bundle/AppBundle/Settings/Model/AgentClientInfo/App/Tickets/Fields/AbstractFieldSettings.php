<?php

namespace DeskPRO\Bundle\AppBundle\Settings\Model\AgentClientInfo\App\Tickets\Fields;

use JMS\Serializer\Annotation as JMS;

/**
 * Class AbstractFieldSettings.
 */
abstract class AbstractFieldSettings
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $id;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $type
     */
    public function __construct($id, $type)
    {
        $this->id   = $id;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}

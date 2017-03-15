<?php

namespace DeskPRO\Bundle\AppStoreBundle\API;

use DeskPRO\Bundle\AppBundle\Entity;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("NONE")
 */
class AppStateRepresentation
{
    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $scope;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    private $value;

    public function mapFromAppStateEntity(Entity\AppStore\AppState $appState)
    {
        $this->scope = $appState->getScope();
        $this->name = $appState->getName();
        $this->value = $appState->getName();
    }

    public function mapToAppStateEntity(Entity\AppStore\AppState $appState)
    {
        $appState->setScope($this->scope);
        $appState->setName($this->name);
        $appState->setValue($this->value);
    }
}

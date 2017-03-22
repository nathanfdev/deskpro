<?php

namespace DeskPRO\Bundle\AppStoreBundle\API;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("NONE")
 */
class AppStateRepresentation
{
    /**
     * @JMS\Expose()
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\StateScope")
     *
     * @var Domain\StateScope
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

    public function mapFromState(Domain\ApplicationState $appState)
    {
        $this->scope = $appState->getScope();
        $this->name = $appState->getName();
        $this->value = $appState->getValue(); //json_decode($appState->getValue(), $assoc = true);
    }

    public function mapFromAppStateEntity(Entity\AppStore\AppState $appState)
    {
        $this->mapFromState($appState);
    }

    public function mapToAppStateEntity(Entity\AppStore\AppState $appState)
    {
        $appState->setScope($this->scope);
        $appState->setName($this->name);
        $appState->setValue($this->value);
    }
}

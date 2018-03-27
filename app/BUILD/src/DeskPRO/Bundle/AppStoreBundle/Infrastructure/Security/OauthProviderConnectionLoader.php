<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use Doctrine\ORM;

class OauthProviderConnectionLoader
{
    /**
     * OauthConnectionLoader constructor.
     *
     * @param string            $providerName
     * @param ORM\EntityManager $manager
     */
    public function __construct($providerName, ORM\EntityManager $manager)
    {
        $this->providerName = $providerName;
        $this->manager      = $manager;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @param $instance
     * @param $readableBy
     *
     * @return SerializedOauth1Connection|null
     */
    public function loadOauth1Connection($instance, $readableBy)
    {
        $appState = $this->loadConnectionStorage($instance, $readableBy);
        if ($appState instanceof AppState) {
            return SerializedOauth1Connection::fromJSON($appState->getValue());
        }

        return null;
    }

    /**
     * @param AppInstance|string $instance
     * @param Person             $readableBy
     *
     * @return SerializedOauth2Connection|null
     */
    public function loadOauth2Connection($instance, $readableBy)
    {
        $appState = $this->loadConnectionStorage($instance, $readableBy);
        if ($appState instanceof AppState) {
            return SerializedOauth2Connection::fromJSON($appState->getValue());
        }

        return null;
    }

    /**
     * @param $instance
     * @param $readableBy
     *
     * @return AppState|null
     */
    private function loadConnectionStorage($instance, $readableBy)
    {
        $stateName = sprintf('oauth:%s', $this->providerName);
        /** @var AppStateRepository $appStateRepo */
        $appStateRepo = $this->manager->getRepository(AppState::class);

        return $appStateRepo->findOneReadableByName($instance, $readableBy, $stateName);
    }
}

<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use Doctrine\ORM;
use League\OAuth2\Client\Token\AccessToken;

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
     * @param AppInstance|string $instance
     * @param Person             $readableBy
     * @param string             $storageKey
     * @return AccessToken|null
     */
    public function loadOauth2Tokens($instance, $readableBy, $storageKey)
    {
        /** @var AppStateRepository $appStateRepo */
        $appStateRepo = $this->manager->getRepository(AppState::class);
        $appState = $appStateRepo->findOneReadableByName($instance, $readableBy, $storageKey);
        if ($appState instanceof AppState) {
            $value = $appState->getValue();
            $jsonDecodedValue = \json_decode($value, $assoc = true);

            if (JSON_ERROR_NONE === json_last_error()) {
                return new AccessToken($jsonDecodedValue);
            }
        }

        return null;
    }

    /**
     * @param AppInstance|string $instance
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

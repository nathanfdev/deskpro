<?php namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AppInstance;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppState;
use DeskPRO\Bundle\AppBundle\Entity\Repository\AppStateRepository;
use Doctrine\ORM;

class Oauth2ProviderConnectionLoader
{
    /**
     * OauthConnectionLoader constructor.
     * @param string $providerName
     * @param ORM\EntityManager $manager
     */
    public function __construct($providerName, ORM\EntityManager $manager)
    {
        $this->providerName = $providerName;
        $this->manager = $manager;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @param AppInstance|string $instance
     * @param Person $readableBy
     *
     * @return SerializedOauth2Connection|null
     */
    public function loadReadable($instance, $readableBy)
    {
        $stateName = sprintf('oauth:%s', $this->providerName);

        /** @var AppStateRepository $appStateRepo */
        $appStateRepo = $this->manager->getRepository(AppState::class);
        $appState = $appStateRepo->findOneReadableByName($instance, $readableBy, $stateName);

        if ($appState instanceof AppState) {
            return SerializedOauth2Connection::fromJSON($appState->getValue());
        }
        return null;
    }
}

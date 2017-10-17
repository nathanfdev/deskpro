<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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

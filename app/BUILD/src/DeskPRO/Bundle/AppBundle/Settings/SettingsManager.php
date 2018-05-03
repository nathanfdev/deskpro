<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use DeskPRO\Bundle\AppBundle\Settings\Model\AgentSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\IMSettings;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\TicketsSettings;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class SettingsManager.
 */
class SettingsManager
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Person
     */
    private $user;

    /**
     * @param EntityManager $em
     * @param TokenStorage  $tokenStorage
     */
    public function __construct(EntityManager $em, TokenStorage $tokenStorage)
    {
        $this->em   = $em;
        $this->user = $tokenStorage->getToken()->getUser();
    }

    /**
     * @return AgentSettings
     */
    public function getAgentSettings()
    {
        $settings = new AgentSettings();
        $settings->setTickets($this->getTicketsSettings());
        $settings->setIM($this->getIMSettings());

        return $settings;
    }

    /**
     * @return TicketsSettings
     */
    private function getTicketsSettings()
    {
        $settings = new TicketsSettings();

        $grouping = [];
        $settings->setFilterGroupings($grouping);

        return $settings;
    }

    /**
     * @return IMSettings
     */
    private function getIMSettings()
    {
        $settings = new IMSettings();

        $chatsOrderPreference = $this
            ->em
            ->getRepository(PersonPref::class)
            ->findOneBy(['name' => 'agent.ui.im.chats_order', 'person' => $this->user]);

        $settings->setChatsOrder($chatsOrderPreference ? $chatsOrderPreference->getValue() : []);

        return $settings;
    }
}

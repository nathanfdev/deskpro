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

namespace DeskPRO\Bundle\AppBundle\Notification;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationClient;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationConfiguration;
use Doctrine\ORM\EntityManager;

/**
 * Class NotificationService.
 */
class NotificationService
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SettingsBag
     */
    protected $settings;

    /**
     * @param EntityManager    $em
     * @param SettingsResolver $settings
     */
    public function __construct(EntityManager $em, SettingsResolver $settings)
    {
        $this->em       = $em;
        $this->settings = $settings->getGlobalSettings();
    }

    /**
     * @param        $last
     * @param Person $user
     *
     * @return array
     */
    public function getLastActionAlerts($last, Person $user)
    {
        $actionAlertRepo = $this->em->getRepository(ActionAlert::class);
        $qb              = $actionAlertRepo->createQueryBuilder('aa');
        $date            = new \DateTime('@'.$last);
        $result          = $qb->where('aa.date_created > (:last)')
            ->andWhere('aa.target_id = :target_id')
            ->setParameter('last', $date->format('Y-m-d H:i:s'))
            ->setParameter('target_id', $user->getId())
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @return int
     */
    public function lastAlert()
    {
        $date = new \DateTime();

        return $date->getTimestamp();
    }

    /**
     * @return NotificationConfiguration
     */
    public function getClientsSetup()
    {
        $handlers = [];

        $strategies_config = $this->settings->get('notification.settings.strategies');

        $strategies = array_merge(
            is_array($strategies_config) ? $strategies_config : [],
            [$this->settings->get('notification.settings.default_strategy')]
        );

        foreach ($strategies as $strategy) {
            foreach ($strategy['delivery'] as $handler) {
                $handlers[$handler] = true;
            }
        }

        $setup = [];
        foreach (array_keys($handlers) as $handler) {
            $setup[] = $this->getClientSetup($handler);
        }

        return new NotificationConfiguration($setup);
    }

    public function sendNote(Person $person, $ids, $noteText)
    {
        $agentChatRepository = $this->em->getRepository(AgentChat::class);
        if (!$chat = $agentChatRepository->findGroupChat($person, $ids)) {
            $chat = new AgentChat();
            $chat
                ->setType(AgentChat::TYPE_GROUP)
                ->setName('Notification from: '.$person->getDisplayName());
            $participants = $this->em->getRepository(Person::class)->findBy(['id' => $ids]);
            foreach ($participants as $participant) {
                $chat->addParticipant($participant);
            }
            $chat->addParticipant($person);
        }
        $message = new AgentChatMessage();
        $message
            ->setPerson($person)
            ->setChat($chat)
            ->setUuid(uniqid('', true))
            ->setMessage($noteText);
        $this->em->persist($chat);
        $this->em->persist($message);
        $this->em->flush();
    }

    /**
     * @param $handler
     *
     * @return NotificationClient
     */
    protected function getClientSetup($handler)
    {
        switch ($handler) {
            case 'pusher':
                return new NotificationClient('pusher', [
                    'appKey' => $this->settings->get('notification.settings.pusher_client.appKey'),
                    'debug'  => $this->settings->get('notification.settings.pusher_client.debug'),
                ]);
            case 'db':

                return new NotificationClient('polling', [
                    'last_alert'       => $this->lastAlert(),
                    'polling_interval' => $this->settings->get('notification.settings.polling_client.polling_interval', 5000),
                ]);
            default:
                throw new \RuntimeException(sprintf('We can\'t find settings for [ %s ] client', $handler));
        }
    }
}

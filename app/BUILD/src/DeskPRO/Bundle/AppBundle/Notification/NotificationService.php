<?php

namespace DeskPRO\Bundle\AppBundle\Notification;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\Notification;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationClient;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationConfiguration;
use DeskPRO\Component\Util\RandUtils;
use Doctrine\ORM\EntityManager;
use Firebase\JWT\JWT;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NotificationService.
 */
class NotificationService
{
    const TARGET_BROADCAST = 'agent_public';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var SettingsBag
     */
    protected $settings;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param EntityManager         $em
     * @param SettingsResolver      $settings
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManager $em, SettingsResolver $settings, TokenStorageInterface $tokenStorage)
    {
        $this->em           = $em;
        $this->settings     = $settings->getGlobalSettings();
        $this->tokenStorage = $tokenStorage;
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
        $result          = $qb->where('aa.id > :last')
            ->andWhere('aa.target_id = :target_id')
            ->setParameter('last', $last)
            ->setParameter('target_id', $user->getId())
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param int    $userId
     * @param string $visitorId
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int
     */
    public function lastAlert($userId, $visitorId)
    {
        $actionAlertRepo = $this->em->getRepository(ActionAlert::class);
        $qb              = $actionAlertRepo->createQueryBuilder('aa');
        $alert           = $qb->orderBy('aa.id', 'DESC')->setMaxResults(1);

        if ($userId) {
            $alert->andWhere('aa.target_id = :target')->setParameter('target', $userId);
        } elseif ($visitorId) {
            $alert->andWhere('aa.target_id = :target')->setParameter('target', $visitorId);
        }

        $alert = $alert->getQuery()->getOneOrNullResult();

        return $alert ? (int) $alert->getId() : 0;
    }

    /**
     * @param int    $userId
     * @param string $visitorId
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return int
     */
    public function lastNotify($userId, $visitorId)
    {
        $notificationRepo = $this->em->getRepository(Notification::class);
        $qb               = $notificationRepo->createQueryBuilder('n');
        $notification     = $qb->orderBy('n.id', 'DESC')->setMaxResults(1);

        if ($userId) {
            $notification->andWhere('n.target_id = :target')->setParameter('target', $userId);
        } elseif ($visitorId) {
            $notification->andWhere('n.target_id = :target')->setParameter('target', $visitorId);
        }

        $notification = $notification->getQuery()->getOneOrNullResult();

        return $notification ? (int) $notification->getId() : 0;
    }

    /**
     * @param int    $userId
     * @param string $visitorId
     *
     * @return NotificationConfiguration
     */
    public function getClientsSetup($userId = null, $visitorId = null)
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
            $setup[] = $this->getClientSetup($handler, $userId, $visitorId);
        }

        return new NotificationConfiguration($setup);
    }

    /**
     * @param Person $person
     * @param array  $ids
     * @param string $noteText
     */
    public function sendNote(Person $person, $ids, $noteText)
    {
        $agentChatRepository = $this->em->getRepository(AgentChat::class);
        $personRepository    = $this->em->getRepository(Person::class);

        //ugly cheat
        foreach ($personRepository->findBy(['id' => $ids]) as $participant) {
            $noteText = str_replace(
                '@'.$participant->getDisplayName(),
                '<strong>@'.$participant->getDisplayName().'</strong>',
                $noteText
            );
        }

        $chat = null;

        foreach ($ids as $participantId) {
            $participant = $personRepository->find($participantId);
            $chat        = $agentChatRepository->findChatWithAgent($participant, $person);
            if (!$chat) {
                $chat = new AgentChat();
                $chat->setType(AgentChat::TYPE_AGENT);
                $chat->addParticipant($participant);
                $chat->addParticipant($person);
            }

            $message = new AgentChatMessage();
            $message
                ->setPerson($person)
                ->setChat($chat)
                ->setUuid(RandUtils::uuidV4())
                ->setMessage($noteText)
                ->setMetadata(['mention' => true]);
            $this->em->persist($chat);
            $this->em->persist($message);
        }

        $this->em->flush();
    }

    /**
     * @param string $handler
     * @param int    $userId
     * @param string $visitorId
     *
     * @return NotificationClient
     */
    protected function getClientSetup($handler, $userId = null, $visitorId = null)
    {
        switch ($handler) {
            case 'pusher':
                return new NotificationClient('pusher', [
                    'appKey'        => $this->settings->get('notification.settings.pusher_client.appKey'),
                    'channelPrefix' => $this->settings->get('notification.settings.pusher_client.channel_prefix'),
                    'cluster'       => $this->settings->get('notification.settings.pusher_client.cluster'),
                    'debug'         => $this->settings->get('notification.settings.pusher_client.debug'),
                ]);
            case 'db':
                return new NotificationClient('legacy', [
                    'last_alert'  => $this->lastAlert($userId, $visitorId),
                    'last_notify' => $this->lastNotify($userId, $visitorId),
                ]);
            case 'deskpro':
                return new NotificationClient('deskpro', [
                    'token'  => $this->getJwtToken($visitorId),
                    'debug'  => $this->settings->get('notification.settings.deskpro_client.debug'),
                    'host'   => $this->settings->get('notification.settings.deskpro_client.host'),
                    'port'   => $this->settings->get('notification.settings.deskpro_client.port'),
                    'secure' => $this->settings->get('notification.settings.deskpro_client.secure', false),
                ]);
            default:
                throw new \RuntimeException(sprintf('We can\'t find settings for [ %s ] client', $handler));
        }
    }

    protected function getJwtToken($visitorId = null)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return JWT::encode(
            [
                'id'         => $visitorId ? $visitorId : ($user instanceof Person ? $user->getId() : 0),
                'by_visitor' => (bool) $visitorId,
            ],
            $this->settings->get('notification.settings.deskpro_client.secret')
        );
    }
}

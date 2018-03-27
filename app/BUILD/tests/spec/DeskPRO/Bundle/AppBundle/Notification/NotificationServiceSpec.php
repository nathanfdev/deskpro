<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification;

use Application\DeskPRO\NewSettings\SettingsBag;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use DeskPRO\Bundle\AppBundle\Entity\Notification;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationClient;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Notifications\NotificationConfiguration;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @mixin NotificationService
 */
class NotificationServiceSpec extends ObjectBehavior
{
    public function let(
        SettingsResolver $settings_resolver,
        EntityManager $em,
        SettingsBag $settings,
        EntityRepository $repo,
        QueryBuilder $qb,
        AbstractQuery $query,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token

    ) {
        $settings_resolver->getGlobalSettings()->willReturn($settings);
        $settings->get('notification.settings.strategies')->willReturn($this->getStrategies());
        $settings->get('notification.settings.default_strategy')->willReturn($this->getDefaultStrategy());
        $settings->get('notification.settings.polling_client.polling_interval', 5000)->willReturn(25000);
        $settings->get('notification.settings.pusher_client.appKey')->willReturn('pusherAppKey');
        $settings->get('notification.settings.pusher_client.debug')->willReturn(true);
        $settings->get('notification.settings.pusher_client.channel_prefix')->willReturn('');
        $settings->get('notification.settings.pusher_client.cluster')->willReturn('mt1');
        $em->getRepository(ActionAlert::class)->willReturn($repo);
        $em->getRepository(Notification::class)->willReturn($repo);
        $repo->createQueryBuilder(Argument::type('string'))->willReturn($qb);
        $qb->orderBy(Argument::any(), Argument::any())->willReturn($qb);
        $qb->setMaxResults(Argument::any())->willReturn($qb);
        $qb->getQuery()->willReturn($query);
        $query->getOneOrNullResult(Argument::any())->willReturn(null);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn('anon.');
        $this->beConstructedWith($em, $settings_resolver, $tokenStorage);
    }

    public function it_could_construct_configuration_array()
    {
        $this->getClientsSetup()->shouldHaveType(NotificationConfiguration::class);
        $clients = $this->getClientsSetup()->getClients();
        $clients->shouldBeArray();
        $clients->shouldHaveCount(2);

        $client = $clients[0];
        $client->shouldHaveType(NotificationClient::class);
        $client->getType()->shouldBe('legacy');
        $client->getOptions()->shouldBe([
            'last_alert'  => $this->lastAlert(),
            'last_notify' => $this->lastNotify(),
        ]);

        $client = $clients[1];
        $client->shouldHaveType(NotificationClient::class);
        $client->getType()->shouldBe('pusher');
        $client->getOptions()->shouldBe([
            'appKey'        => 'pusherAppKey',
            'channelPrefix' => '',
            'cluster'       => 'mt1',
            'debug'         => true,
        ]);
    }

    private function getStrategies()
    {
        return [
            'notification.yet.another.system.event' => [
                'strategy' => 'immediate',
                'delivery' => [
                    'db',
                ],
            ],
            'notification.yet.another.system.event_for_pusher' => [
                'strategy' => 'immediate',
                'delivery' => [
                    'pusher',
                ],
            ],
        ];
    }

    private function getDefaultStrategy()
    {
        return [
            'strategy' => 'immediate',
            'delivery' => [
                'db',
            ],
        ];
    }
}

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

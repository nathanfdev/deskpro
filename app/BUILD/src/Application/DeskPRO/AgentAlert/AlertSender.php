<?php

namespace Application\DeskPRO\AgentAlert;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\AgentAlert;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AlertSender.
 */
class AlertSender
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * @var AvatarResolver
     */
    protected $avatarResolver;

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param AvatarResolver           $avatarResolver
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        EntityManager $em,
        AvatarResolver $avatarResolver,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em              = $em;
        $this->db              = $em->getConnection();
        $this->resolver        = new OptionsResolver();
        $this->avatarResolver  = $avatarResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->configureOptions();
    }

    /**
     * @param Person $agent
     * @param string $type
     * @param array  $data
     *
     * @return AgentAlert
     */
    public function send($agent, $type, array $data)
    {
        $data = $this->resolver->resolve($data);

        $tplLine = null;
        $alert   = $this->createAlert($agent, $type, $data);
        $this->em->persist($alert);
        $this->em->flush($alert);

        if (isset($data['browser_rendered'])) {
            $tplLine = $data['browser_rendered'];

            $this->eventDispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent('agent-notify.tickets', [
                    'target'   => $agent,
                    'type'     => $type,
                    'alert_id' => $alert->getId(),
                    'row'      => $tplLine,
                    'icon'     => $this->avatarResolver->getAvatar($agent, 48),
                ]
            ));
        }

        return $alert;
    }

    /**
     * @param       $agent
     * @param       $type
     * @param array $data
     *
     * @return AgentAlert
     */
    public function createAlert($agent, $type, array $data)
    {
        $alert = new AgentAlert();
        $alert->setPerson($agent);
        $alert->setTypename($type);
        $alert->setData($data);

        if (isset($data['browser_rendered'])) {
            $alert->addTargetMap(AgentAlert::TARGET_BROWSER, ['browser_rendered']);
        }

        return $alert;
    }

    /**
     * @param AgentAlert $alert
     *
     * @return array
     */
    public function getDataArray(AgentAlert $alert, $target = null)
    {
        $data = $alert->getData($target);

        if (isset($data['@fetch_types'])) {
            $fetchTypes = $data['@fetch_types'];
            unset($data['@fetch_types']);

            foreach ($fetchTypes as $k => $type) {
                if (!isset($data[$k]) || !$data[$k]) {
                    $data[$k] = null;
                    continue;
                }

                $val = $data[$k];
                if (is_array($val)) {
                    $data[$k] = $this->em->getRepository($type)->getByIds($val, true);

                    foreach ($data[$k] as &$sub) {
                        $sub = $sub->toApiData(true, false);
                    }
                    unset($sub);
                } else {
                    $data[$k] = $this->em->getRepository($type)->find($val);
                    if ($data[$k] && $data[$k] instanceof DomainObject) {
                        $data[$k] = $data[$k]->toApiData(true, false);
                    } else {
                        unset($data[$k]);
                    }
                }
            }
        }

        return $data;
    }

    private function configureOptions()
    {
        $this->resolver->setDefined(
            [
                '@fetch_types',
                'browser_rendered',
                'is_new_agent_note',
                'is_new_agent_reply',
                'is_new_ticket',
                'is_new_user_reply',
                'ticket',
            ]
        );
        $this->resolver->setRequired('performer');
    }
}

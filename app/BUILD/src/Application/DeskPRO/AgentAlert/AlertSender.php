<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\AgentAlert;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\AgentAlert;
use Application\DeskPRO\Entity\ClientMessage;
use Doctrine\ORM\EntityManager;
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

    protected $resolver;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em       = $em;
        $this->db       = $em->getConnection();
        $this->resolver = new OptionsResolver();
        $this->configureOptions();
    }

    /**
     * @param \Application\DeskPRO\Entity\Person $agent
     * @param string                             $type
     * @param array                              $data
     *
     * @return AgentAlert
     */
    public function send($agent, $type, array $data)
    {
        $data = $this->resolver->resolve($data);

        $tpl_line = null;
        $alert    = $this->createAlert($agent, $type, $data);
        $this->em->persist($alert);
        $this->em->flush($alert);

        if (isset($data['browser_rendered'])) {
            $tpl_line = $data['browser_rendered'];

            $cm = new ClientMessage();
            $cm->fromArray(
                [
                    'channel' => 'agent-notify.tickets',
                    'data'    => [
                        'type'     => $type,
                        'alert_id' => $alert->getId(),
                        'row'      => $tpl_line,
                    ],
                    'for_person'        => $agent,
                    'created_by_client' => 'sys',
                ]
            );
            $this->em->persist($cm);
            $this->em->flush($cm);
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
            $fetch_types = $data['@fetch_types'];
            unset($data['@fetch_types']);

            foreach ($fetch_types as $k => $type) {
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

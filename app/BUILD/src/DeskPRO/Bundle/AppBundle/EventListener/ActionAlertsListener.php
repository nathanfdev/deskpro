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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Application\DeskPRO\HttpFoundation\Session;
use Orb\Util\Strings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ActionAlertsListener.
 */
class ActionAlertsListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ActionAlertsListener constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onResponse', 1024],
        ];
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request  = $event->getRequest();
        $response = $event->getResponse();
        if (($lastId = $request->headers->get('x-deskpro-return-actionalerts')) && $this->isPollingEnabled()) {
            $this->container->get('deskpro.notification.event_manager')->deliver();
            $alerts = $this->getActionAlerts($lastId);
            if ($alerts) {
                $splitPoint          = '--action-alerts-'.Strings::random(30, Strings::CHARS_ALPHANUM);
                $originalContent     = $response->getContent();
                $actionAlertsContent = json_encode($alerts);

                $response->setContent($actionAlertsContent."\r\n".$splitPoint."\r\n".$originalContent);
                $response->headers->set('X-DeskPRO-With-ActionAlerts-SplitPoint', $splitPoint);
            }
        }
    }

    private function getActionAlerts($lastId)
    {
        if (!$this->container->has('session')) {
            return null;
        }

        $session = $this->container->get('session');
        if (!$session instanceof Session || !$session->getPerson() || !$session->getPerson()->getId()) {
            return null;
        }

        $sql = <<<'SQL'
SELECT * FROM `notify_action_alerts`
WHERE `target_id` = :target_id OR `target_id` = -100
  AND `id` > :last
ORDER BY `id` ASC
SQL;
        $stmnt = $this->container->get('doctrine.orm.default_entity_manager')->getConnection()->prepare($sql);
        $stmnt->execute([
            'target_id' => $session->getPerson()->getId(),
            'last'      => $lastId,
        ]);

        $all = $stmnt->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($all as &$datum) {
            foreach ($datum as &$innerData) {
                if (is_numeric($innerData)) {
                    $innerData = (int) $innerData;
                }
            }
            $date                  = new \DateTime($datum['date_created']);
            $datum['date_created'] = $date->format(\DateTime::ISO8601);
            $datum['timestamp']    = $date->getTimestamp();
        }

        return $all;
    }

    private function isPollingEnabled()
    {
        $clients = $this->container->get('deskpro.notification.service')->getClientsSetup()->getClients();
        foreach ($clients as $client) {
            if ($client->getType() === 'legacy') {
                return true;
            }
        }

        return false;
    }
}

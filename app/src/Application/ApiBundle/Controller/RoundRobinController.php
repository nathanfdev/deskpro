<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\UserTypePermission;
use Application\DeskPRO\Entity\RoundRobin;
use Application\DeskPRO\Tickets\Actions\ActionComposite;
use Application\DeskPRO\Tickets\Actions\SetRoundRobin;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;

class RoundRobinController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        $data = array();
        /** @var $rr RoundRobin */
        foreach ($this->em->getRepository('DeskPRO:RoundRobin')->findAll() as $rr) {
            $data[] = $rr->toApiData();
        }

        return $this->createApiResponse($data);
    }

    ###################################################################################################################
    # get RR
    ####################################################################################################################

    public function getAction($id)
    {
        /** @var $rr RoundRobin */
        if (!$rr = $this->em->getRepository('DeskPRO:RoundRobin')->find($id)) {
            throw $this->createNotFoundException();
        }

        return $this->createApiResponse($rr->toApiData());
    }

    ###################################################################################################################
    # save RR
    ####################################################################################################################

    public function setAction($id)
    {
        /** @var \Application\DeskPRO\EntityRepository\RoundRobin $rep */
        $rep = $this->em->getRepository('DeskPRO:RoundRobin');

        /** @var $rr RoundRobin */
        if (!$id) {
            $rr = new RoundRobin();
            $this->em->persist($rr);
        } elseif (!$rr = $rep->find($id)) {
            throw $this->createNotFoundException();
        }

        $data = $this->in->getAll('req');
        unset($data['next']);

        $agents = $data['agents'];
        unset($data['agents']);
        $rr->fromArray($data);
        $rep->setAgents($rr, $agents);

        $this->em->flush();

        return $this->getAction($rr['id']);
    }

    ###################################################################################################################
    # delete RR
    ####################################################################################################################

    public function deleteAction($id)
    {
        /** @var $rr RoundRobin */
        if (!$rr = $this->em->getRepository('DeskPRO:RoundRobin')->find($id)) {
            throw $this->createNotFoundException();
        }

        $this->countRoundRobinTriggers(true, $rr['id']);
        $this->em->remove($rr);
        $this->em->flush();

        return $this->createApiResponse(array());
    }

    ###################################################################################################################
    # setup RR
    ####################################################################################################################

    public function settingsAction()
    {
        if ($this->request->isMethod('PUT')) {
            $enabled = $this->in->getBool('enabled');
            $this->settings->setSetting('core.round_robin.enabled', $enabled);

            if (!$enabled) {
                $this->countRoundRobinTriggers(true);
            }
        }

        return $this->createApiResponse(array(
            'enabled' => (bool) $this->settings->get('core.round_robin.enabled', false),
        ));
    }

    /**
     * check triggers using round robin id, or all round robins if id is null
     * @param $id
     * @return Response
     */
    public function checkTriggersAction($id)
    {
        return $this->createApiResponse(array('active_triggers' => $this->countRoundRobinTriggers(false, $id)));
    }

    protected function isTriggerActionClear($action, $roundRobinId = null)
    {
        if ($action instanceof SetRoundRobin) {
            if (!$roundRobinId || $action->getActionOption('id') == $roundRobinId) {
                return false;
            }
        } elseif ($action instanceof ActionComposite) {
            foreach ($action as $subAction) {
                if (!$this->isTriggerActionClear($subAction)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function countRoundRobinTriggers($disable = false, $roundRobinId = null)
    {
        $count    = 0;
        $triggers = $this->em->getRepository('DeskPRO:TicketTrigger')->getTriggers();
        foreach ($triggers as $trigger) {
            $newActions = new TriggerActions();
            /** @var TriggerActions $actions */
            $actions = $trigger->actions;
            if (!$actions) {
                continue;
            }

            foreach ($actions as $action) {
                if ($this->isTriggerActionClear($action, $roundRobinId)) {
                    $newActions->addAction($action);
                }
            }

            if ($newActions->count() !== $actions->count()) {
                $count++;

                if ($disable) {
                    $trigger->actions      = $newActions;
                    $trigger['is_enabled'] = false;
                    $this->em->flush();
                }
            }
        }

        return $count;
    }
}

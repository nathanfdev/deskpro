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

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\ChatRoundRobin;
use Application\DeskPRO\Entity\ChatRoundRobinLogEntry;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Tickets\Actions\ActionComposite;
use Application\DeskPRO\Tickets\Actions\SetRoundRobin;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\UserTypePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 */
class ChatRoundRobinController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new UserTypePermission(UserTypePermission::AGENT);
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction()
    {
        $data  = [];
        $adata = $this->container->getAgentData();
        /** @var $rr ChatRoundRobin */
        foreach ($this->em->getRepository(ChatRoundRobin::class)->findAll() as $rr) {
            $rrdata = $rr->toApiData();
            if ($next = $rr->getNextAgent($adata)) {
                $rrdata['next'] = $next->toApiData();
            }
            $data[] = $rrdata;
        }

        return $this->createApiResponse($data);
    }

    //##################################################################################################################
    // get RR
    //###################################################################################################################

    public function getAction($id)
    {
        /** @var $rr ChatRoundRobin */
        if (!$rr = $this->em->getRepository(ChatRoundRobin::class)->find($id)) {
            throw $this->createNotFoundException();
        }

        $data = $rr->toApiData();
        if ($next = $rr->getNextAgent($this->container->getAgentData())) {
            $data['next'] = $next->toApiData();
        }

        return $this->createApiResponse($data);
    }

    //##################################################################################################################
    // save RR
    //###################################################################################################################

    public function setAction($id)
    {
        /** @var \Application\DeskPRO\EntityRepository\ChatRoundRobin $rep */
        $rep = $this->em->getRepository(ChatRoundRobin::class);

        /* @var $rr ChatRoundRobin */
        if (!$id) {
            $rr = new ChatRoundRobin();
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

    //##################################################################################################################
    // delete RR
    //###################################################################################################################

    public function deleteAction($id)
    {
        /** @var $rr ChatRoundRobin */
        if (!$rr = $this->em->getRepository(ChatRoundRobin::class)->find($id)) {
            throw $this->createNotFoundException();
        }

        $this->countRoundRobinTriggers(true, $rr['id']);
        $this->em->remove($rr);
        $this->em->flush();

        return $this->createApiResponse([]);
    }

    //##################################################################################################################
    // setup RR
    //###################################################################################################################

    public function settingsAction()
    {
        if ($this->request->isMethod('PUT')) {
            $enabled = $this->in->getBool('enabled');
            $this->settings->setSetting('core.chat_round_robin.enabled', $enabled);

            if (!$enabled) {
                $this->countRoundRobinTriggers(true);
            }
        }

        return $this->createApiResponse([
            'enabled' => (bool) $this->settings->get('core.chat_round_robin.enabled', false),
        ]);
    }

    /**
     * check triggers using round robin id, or all round robins if id is null.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function checkTriggersAction($id)
    {
        return $this->createApiResponse(['active_triggers' => $this->countRoundRobinTriggers(false, $id)]);
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
        $triggers = $this->em->getRepository(TicketTrigger::class)->getTriggers();
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
                ++$count;

                if ($disable) {
                    $trigger->actions      = $newActions;
                    $trigger['is_enabled'] = false;
                    $this->em->flush();
                }
            }
        }

        return $count;
    }

    /**
     * @param $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logsAction($id)
    {
        if (!$rr = $this->em->find(ChatRoundRobin::class, $id)) {
            throw new NotFoundHttpException();
        }

        $entries = $this->em->getRepository(ChatRoundRobinLogEntry::class)->findBy(
            ['rr' => $rr],
            ['created' => 'desc']
        );

        return $this->render('AdminInterfaceBundle:ChatRoundRobin:logs.html.twig', [
            'entries' => $entries,
            'rr'      => $rr,
        ]);
    }
}

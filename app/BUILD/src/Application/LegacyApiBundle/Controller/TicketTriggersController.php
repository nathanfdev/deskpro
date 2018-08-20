<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\EntityRepository\TicketTrigger as TicketTriggerRepository;
use Application\DeskPRO\Tickets\Triggers\Edit\SpecialTriggerEdit;
use Application\DeskPRO\Tickets\Triggers\Terms\TriggerTermComposite;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Operations about Ticket triggers.
 *
 * @ApiModes("all")
 */
class TicketTriggersController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    /**
     * @param string|null $type
     *
     * @return Response
     */
    public function listAction($type = null)
    {
        if (!$type || $type == 'all') {
            $type = null;
        }

        /** @var TicketTriggerRepository $ticketTriggerRepository */
        $ticketTriggerRepository = $this->em->getRepository(TicketTrigger::class);

        $triggers        = $ticketTriggerRepository->getTriggers($type);
        $data            = $this->getApiData($triggers);
        $res             = [];
        $res['triggers'] = $data;

        if ($type == 'all' || $type == 'newticket' || $type == 'update') {
            $res['department_triggers_enabled']   = false;
            $res['emailaccount_triggers_enabled'] = false;
            foreach ($triggers as $t) {
                if ($t->department && $t->is_enabled) {
                    $res['department_triggers_enabled'] = true;
                }
                if ($t->email_account && $t->is_enabled) {
                    $res['emailaccount_triggers_enabled'] = true;
                }
            }
        }

        return $this->createApiResponse($res);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    /**
     * @param int         $id
     * @param string|null $special_type
     *
     * @return Response
     */
    public function getAction($id, $special_type = null)
    {
        /** @var TicketTriggerRepository $ticketTriggerRepository */
        $ticketTriggerRepository = $this->em->getRepository('DeskPRO:TicketTrigger');

        switch ($special_type) {
            case 'departments':
            case 'departments_changed':
                $dep = $this->container->getTicketDepartments()->getById($id);
                if (!$dep) {
                    throw $this->createNotFoundException();
                }

                $event   = $special_type == 'departments' ? TicketTrigger::EVENT_TYPE_NEWTICKET : TicketTrigger::EVENT_TYPE_UPDATE;
                $trigger = $ticketTriggerRepository->findOneBy(['department' => $dep, 'event_trigger' => $event]);

                if (!$trigger) {
                    $trigger = new TicketTrigger();
                    $edit    = SpecialTriggerEdit::createWithDepartment($dep, $event);
                    $edit->applyToTrigger($trigger);
                    $this->em->persist($trigger);
                    $this->em->flush();
                }
                break;

            case 'email_accounts':
                if (!$this->container->getEmailAccountManager()->hasAcccount($id)) {
                    throw $this->createNotFoundException();
                }

                $acc = $this->container->getEmailAccountManager()->getAccount($id);

                $trigger = $ticketTriggerRepository->findOneBy(['email_account' => $acc]);
                if (!$trigger) {
                    $trigger = new TicketTrigger();
                    $edit    = SpecialTriggerEdit::createWithEmailAccount($acc);
                    $edit->applyToTrigger($trigger);
                    $this->em->persist($trigger);
                    $this->em->flush();
                }
                break;

            case 'satisfaction':

                $satisfactions = [
                    0 => 'negative',
                    1 => 'neutral',
                    2 => 'positive',
                ];

                if (!isset($satisfactions[$id])) {
                    throw new NotFoundHttpException();
                }

                $name    = SpecialTriggerEdit::TYPE_SATISFACTION.'_'.$satisfactions[$id];
                $trigger = $ticketTriggerRepository->findOneBy(['sys_name' => $name]);
                if (!$trigger) {
                    $trigger             = new TicketTrigger();
                    $trigger->is_enabled = false;
                    $edit                = SpecialTriggerEdit::createWithSatisfaction($satisfactions[$id]);
                    $edit->applyToTrigger($trigger);
                    $this->em->persist($trigger);
                    $this->em->flush();
                }
                break;

            default:
                $trigger = $this->em->find(TicketTrigger::class, $id);
        }

        if (!$trigger) {
            throw $this->createNotFoundException();
        }

        $data = $this->getApiData($trigger);

        return $this->createApiResponse([
            'trigger' => $data,
        ]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

    /**
     * @param int         $id
     * @param string|null $special_type
     *
     * @return Response
     */
    public function saveAction($id, $special_type = null)
    {
        if ($id) {
            /** @var TicketTriggerRepository $ticketTriggerRepository */
            $ticketTriggerRepository = $this->em->getRepository('DeskPRO:TicketTrigger');
            switch ($special_type) {
                case 'departments':
                case 'departments_changed':
                    $dep = $this->container->getTicketDepartments()->getById($id);
                    if (!$dep) {
                        throw $this->createNotFoundException();
                    }

                    $event = $special_type == 'departments' ? TicketTrigger::EVENT_TYPE_NEWTICKET : TicketTrigger::EVENT_TYPE_UPDATE;
                    /** @var TicketTrigger $trigger */
                    $trigger = $ticketTriggerRepository->findOneBy(['department' => $dep, 'event_trigger' => $event]);
                    if (!$trigger) {
                        $trigger             = new TicketTrigger();
                        $trigger->is_enabled = false;
                        $edit                = SpecialTriggerEdit::createWithDepartment($dep, $event);
                        $edit->applyToTrigger($trigger);
                        $this->em->persist($trigger);
                        $this->em->flush();
                    }
                    break;

                case 'satisfaction':

                    $satisfactions = [
                        0 => 'negative',
                        1 => 'neutral',
                        2 => 'positive',
                    ];

                    if (!isset($satisfactions[$id])) {
                        throw new NotFoundHttpException();
                    }

                    $name    = SpecialTriggerEdit::TYPE_SATISFACTION.'_'.$satisfactions[$id];
                    $trigger = $ticketTriggerRepository->findOneBy(['sys_name' => $name]);
                    if (!$trigger) {
                        $trigger             = new TicketTrigger();
                        $trigger->is_enabled = false;
                        $edit                = SpecialTriggerEdit::createWithSatisfaction($satisfactions[$id]);
                        $edit->applyToTrigger($trigger);
                        $this->em->persist($trigger);
                        $this->em->flush();
                    }
                    break;

                case 'email_accounts':
                    if (!$this->container->getEmailAccountManager()->hasAcccount($id)) {
                        throw $this->createNotFoundException();
                    }

                    $acc = $this->container->getEmailAccountManager()->getAccount($id);

                    $trigger = $ticketTriggerRepository->findOneBy(['email_account' => $acc]);
                    if (!$trigger) {
                        $trigger             = new TicketTrigger();
                        $trigger->is_enabled = false;
                        $edit                = SpecialTriggerEdit::createWithEmailAccount($acc);
                        $edit->applyToTrigger($trigger);
                        $this->em->persist($trigger);
                        $this->em->flush();
                    }
                    break;

                default:
                    $trigger = $this->em->find(TicketTrigger::class, $id);

                    if ($trigger->department) {
                        if ($trigger->event_trigger == 'newticket') {
                            $special_type = 'departments';
                        } else {
                            $special_type = 'departments_changed';
                        }
                    } elseif ($trigger->email_account) {
                        $special_type = 'email_accounts';
                    }
            }
        } else {
            if ($special_type) {
                throw $this->createNotFoundException();
            }
            $trigger = new TicketTrigger();
        }

        $isNew = !((bool) $trigger->id);

        $trigger->title = $this->in->getString('title');

        if ($isNew) {
            $trigger->event_trigger = $this->in->getString('event_trigger');
        }

        if ($trigger->event_trigger == TicketTrigger::EVENT_TYPE_UPDATE) {
            if ($this->in->getBool('flags.run_newreply')) {
                $trigger->addEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY);
            } else {
                $trigger->removeEventFlag(TicketTrigger::EVENT_FLAG_RUN_NEWREPLY);
            }
        }

        $trigger->setByAgentMode($this->in->getArrayOfStrings('by_agent_mode'));
        $trigger->setByUserMode($this->in->getArrayOfStrings('by_user_mode'));
        $trigger->setByAppMode($this->in->getArrayOfStrings('by_app_mode'));

        $errorCriteria = [];
        $errorActions  = [];
        $errorMessages = [];

        $terms = new TriggerTerms();
        foreach ($this->in->getArrayValue('criteria_sets') as $set) {
            if ($set) {
                $composite = new TriggerTermComposite([], TriggerTermComposite::OP_AND);
                foreach ($set as $ti) {
                    try {
                        $t = $terms->getTermFromArray($ti);
                        $composite->add($t);
                    } catch (\Exception $e) {
                        $errorCriteria[] = $ti['type'];
                        $errorMessages[] = $e->getMessage();
                    }
                }
                if ($composite->count()) {
                    $terms->addTerm($composite);
                }
            }
        }

        $actionDefs = $this->container->getTicketActionDefManager();

        $actions = new TriggerActions();
        foreach ($this->in->getArrayValue('actions') as $act) {
            if ($act) {
                $type = $act['type'];

                if (isset($act['DP_DISABLED'])) {
                    continue;
                }

                if ($actionDefs->hasNamedDef($type)) {
                    $act['type_class'] = $actionDefs->getNamedDef($type)->getDef()->getTriggerActionClass();
                    if (!$act['type_class']) {
                        continue;
                    }
                }

                try {
                    $actions->addActionFromArray($act);
                } catch (\Exception $e) {
                    $errorActions[]  = $act['type'];
                    $errorMessages[] = $e->getMessage();
                }
            }
        }

        $ret = [];

        if ($errorCriteria || $errorActions) {
            $ret['errors'] = [];
            if ($errorCriteria) {
                $ret['errors']['criteria'] = $errorCriteria;
            }
            if ($errorActions) {
                $ret['errors']['actions'] = $errorActions;
            }
            $ret['errors']['error_messages'] = $errorMessages;

            return $this->createApiErrorInfoResponse('invalid', 'One or more criteria or actions are invalid', $ret['errors']);
        }

        $trigger->terms = $terms;
        $trigger->setActions($actions);

        if ($trigger->department) {
            $event = $special_type == 'departments' ? TicketTrigger::EVENT_TYPE_NEWTICKET : TicketTrigger::EVENT_TYPE_UPDATE;
            $edit  = SpecialTriggerEdit::createWithDepartment($trigger->department, $event);
            $edit->applyToTrigger($trigger);
        } elseif ($trigger->email_account) {
            $edit = SpecialTriggerEdit::createWithEmailAccount($trigger->email_account);
            $edit->applyToTrigger($trigger);
        }

        if ($isNew) {
            $ro = 0;
            if ($trigger->department) {
                $ro = $this->db->fetchColumn('SELECT run_order FROM ticket_triggers WHERE department_id IS NOT NULL LIMIT 1');
            } elseif ($trigger->email_account) {
                $ro = $this->db->fetchColumn('SELECT run_order FROM ticket_triggers WHERE email_account_id IS NOT NULL LIMIT 1');
            }

            if (!$ro) {
                $ro = $this->db->fetchColumn('SELECT run_order FROM ticket_triggers ORDER BY run_order DESC');
            }

            $trigger->run_order = $ro + 10;
        }

        if ($trigger->department) {
            $trigger->is_enabled = (bool) $this->db->fetchColumn('SELECT id FROM ticket_triggers WHERE department_id IS NOT NULL AND is_enabled = 1 AND event_trigger = ?', [$trigger->event_trigger]);
        } elseif ($trigger->email_account) {
            $trigger->is_enabled = (bool) $this->db->fetchColumn('SELECT id FROM ticket_triggers WHERE email_account_id IS NOT NULL AND is_enabled = 1 AND event_trigger = ?', [$trigger->event_trigger]);
        }

        $this->em->persist($trigger);
        $this->em->flush();

        // Sanity check
        if ($trigger->department) {
            $this->db->executeUpdate('
                DELETE FROM ticket_triggers
                WHERE department_id = ? AND event_trigger = ? AND id != ?
            ', [$trigger->department->id, $trigger->event_trigger, $trigger->id]);
        }
        if ($trigger->email_account) {
            $this->db->executeUpdate('
                DELETE FROM ticket_triggers
                WHERE email_account_id = ?
                AND id != ?
            ', [$trigger->email_account->id, $trigger->id]);
        }

        $ret['trigger_id'] = $trigger->id;

        return $this->createSuccessResponse($ret);
    }

    //###################################################################################################################
    // delete
    //###################################################################################################################

    /**
     * @param int $id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
        if (!$trigger) {
            throw $this->createNotFoundException();
        }

        $oldId = $trigger->id;

        $this->em->remove($trigger);
        $this->em->flush();

        return $this->createSuccessResponse(['old_id' => $oldId]);
    }

    //###################################################################################################################
    // toggle-trigger
    //###################################################################################################################

    /**
     * @param int  $id
     * @param bool $is_enabled
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     */
    public function toggleTriggerAction($id, $is_enabled)
    {
        $trigger = $this->em->find('DeskPRO:TicketTrigger', $id);
        if (!$trigger) {
            throw $this->createNotFoundException();
        }

        $trigger->is_enabled = $is_enabled;
        $this->em->persist($trigger);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // toggle-trigger-group
    //###################################################################################################################

    /**
     * @param string $special_type
     * @param bool   $is_enabled   - defined by route
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function toggleTriggerGroupAction($special_type, $is_enabled)
    {
        $is_enabled = (int) $is_enabled;

        switch ($special_type) {
            case 'departments':
                $this->db->executeUpdate("
                    UPDATE ticket_triggers
                    SET is_enabled = ?
                    WHERE department_id IS NOT NULL AND event_trigger = 'newticket'
                ", [$is_enabled]);
                break;
            case 'departments_changed':
                $this->db->executeUpdate("
                    UPDATE ticket_triggers
                    SET is_enabled = ?
                    WHERE department_id IS NOT NULL AND event_trigger = 'update'
                ", [$is_enabled]);
                break;
            case 'email_accounts':
                $this->db->executeUpdate("
                    UPDATE ticket_triggers
                    SET is_enabled = ?
                    WHERE email_account_id IS NOT NULL AND event_trigger = 'newticket'
                ", [$is_enabled]);
                break;
            default:
                throw $this->createNotFoundException();
        }

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // save-run-order
    //###################################################################################################################

    /**
     * @return Response
     */
    public function saveRunOrderAction()
    {
        $runOrders = $this->in->getCleanValueArray('run_orders', 'string', 'discard');
        /** @var TicketTriggerRepository $ticketTriggerRepository */
        $ticketTriggerRepository = $this->em->getRepository('DeskPRO:TicketTrigger');
        $ticketTriggerRepository->updateRunOrders($runOrders);

        return $this->createSuccessResponse();
    }

    //###################################################################################################################
    // get-custom-actions
    //###################################################################################################################

    /**
     * @return Response
     */
    public function getCustomActionsAction()
    {
        $manager = $this->container->getTicketActionDefManager();

        $actions = [];
        foreach ($manager->getAllDefs() as $d) {
            $actions[] = $d->toApiData();
        }

        return $this->createJsonResponse(['action_defs' => $actions]);
    }

    //###################################################################################################################
    // get-app-events
    //###################################################################################################################

    public function getAppEventsAction($type = 'update')
    {
        if ($type != 'update') {
            throw $this->createNotFoundException();
        }

        $events = [];

        foreach ($this->container->getAppManager()->getAllApps() as $app) {
            $appEvents = $app->getTriggerEvents($type);

            if ($appEvents) {
                $appInfo = [
                    'id'      => $app->id,
                    'title'   => $app->title,
                    'package' => [
                        'name'  => $app->package->name,
                        'title' => $app->package->title,
                    ],
                ];

                foreach ($appEvents as $ev) {
                    $events[] = [
                        'event' => $ev,
                        'app'   => $appInfo,
                    ];
                }
            }
        }

        return $this->createJsonResponse(['app_events' => $events, 'event_type' => $type]);
    }
}

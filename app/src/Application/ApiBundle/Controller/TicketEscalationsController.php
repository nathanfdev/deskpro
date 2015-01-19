<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Tickets\Actions\SendUserEmail;
use Application\DeskPRO\Tickets\Filters\FilterTerms;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;

class TicketEscalationsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }


    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        $escalations = $this->em->getRepository('DeskPRO:TicketEscalation')->getEscalations();
        $data = $this->getApiData($escalations, false);

        return $this->createApiResponse(array(
            'escalations' => $data
        ));
    }

    ####################################################################################################################
    # get
    ####################################################################################################################

    public function getAction($id, $special_type = null)
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketEscalation $rep */
        $rep = $this->em->getRepository('DeskPRO:TicketEscalation');
        switch ($special_type) {
            case 'satisfaction':

                if (!$esc = $rep->findOneBy(array('sys_name' => 'satisfaction'))) {
                    $esc = new TicketEscalation();
                    $esc->title = 'Satisfaction request';
                    $esc->sys_name = 'satisfaction';
                    $esc->event_trigger = TicketEscalation::EVENT_TYPE_TIME_RESOLVED;
                    $esc->event_trigger_time = 60 * 60 * 24 * 3; // 3 days by default
                    $esc->is_enabled = true;
                    $esc->actions->addAction(new SendUserEmail(array(
                        'template' => 'DeskPRO:emails_user:ticket-rate.html.twig',
                        'do_cc_users' => false,
                        'from_name' => 'helpdesk_name',
                        'from_account' => 0,
                        'headers' => array(),
                    )));

                    $this->em->persist($esc);
                    $this->em->flush();
                }

                break;
            default:
                $esc = $rep->find($id);
        }

        if (!$esc) {
            throw $this->createNotFoundException();
        }

        $trans = new LegacyTermsTransformer();
        $crit = $trans->toFilterTerms($esc->terms);
        $crit2 = $trans->toFilterTerms($esc->terms_any);

        $esc = $this->getApiData($esc);
        $esc['terms'] = $crit->exportToArray();
        $esc['terms_any'] = $crit2->exportToArray();

        return $this->createApiResponse(array(
            'escalation' => $esc
        ));
    }

    ####################################################################################################################
    # save
    ####################################################################################################################

    public function saveAction($id, $special_type = null)
    {
        /** @var \Application\DeskPRO\EntityRepository\TicketEscalation $rep */
        $rep = $this->em->getRepository('DeskPRO:TicketEscalation');

        if ($id) {

            switch ($special_type) {
                case 'satisfaction':

                    if (!$esc = $rep->findOneBy(array('sys_name' => 'satisfaction'))) {
                        $esc = new TicketEscalation();
                        $esc->title = 'Satisfaction request';
                        $esc->sys_name = 'satisfaction';
                        $esc->event_trigger = TicketEscalation::EVENT_TYPE_TIME_RESOLVED;
                        $esc->event_trigger_time = 60 * 60 * 24 * 3; // 3 days by default
                        $esc->is_enabled = true;
                        $esc->actions->addAction(new SendUserEmail(array(
                            'template' => 'DeskPRO:emails_user:ticket-rate.html.twig',
                            'do_cc_users' => false,
                            'from_name' => 'helpdesk_name',
                            'from_account' => 0,
                            'headers' => array(),
                        )));

                        $this->em->persist($esc);
                        $this->em->flush();
                    }

                    break;
                default:
                    $esc = $rep->find($id);
            }

            if (!$esc) {
                throw $this->createNotFoundException();
            }
        } else {
            $esc = new TicketEscalation();
        }

        $esc->title = $this->in->getString('title');
        $esc->event_trigger = $this->in->getString('event_trigger');
        $esc->event_trigger_time = $this->in->getUint('event_trigger_time') ?: 1;

        $crit = new FilterTerms();
        foreach ($this->in->getArrayValue('terms') as $term_info) {
            $crit->addTermFromArray($term_info);
        }

        $trans = new LegacyTermsTransformer();
        $esc->terms = $trans->toLegacyTerms($crit);

        $crit = new FilterTerms();
        foreach ($this->in->getArrayValue('terms_any') as $term_info) {
            $crit->addTermFromArray($term_info);
        }

        $trans = new LegacyTermsTransformer();
        $esc->terms_any = $trans->toLegacyTerms($crit);

        $actions = new TriggerActions();
        foreach ($this->in->getArrayValue('actions') as $act) {
            if ($act) {
                $actions->addActionFromArray($act);
            }
        }
        $esc->actions = $actions;

        $this->em->persist($esc);
        $this->em->flush();

        return $this->createSuccessResponse(array(
            'escalation_id' => $esc->id
        ));
    }

    ####################################################################################################################
    # remove
    ####################################################################################################################

    public function deleteAction($id)
    {
        $esc = $this->em->getRepository('DeskPRO:TicketEscalation')->find($id);

        if (!$esc) {
            throw $this->createNotFoundException();
        }

        $this->em->remove($esc);
        $this->em->flush();

        return $this->createSuccessResponse(array(
            'old_id' => $id
        ));
    }

    ####################################################################################################################
    # toggle-trigger
    ####################################################################################################################

    public function toggleEscalationAction($id, $is_enabled)
    {
        $trigger = $this->em->find('DeskPRO:TicketEscalation', $id);
        if (!$trigger) {
            throw $this->createNotFoundException();
        }

        $trigger->is_enabled = $is_enabled;
        $this->em->persist($trigger);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # save-display-order
    ####################################################################################################################

    public function saveRunOrderAction()
    {
        $run_order = $this->in->getCleanValueArray('run_order', 'uint', 'discard');
        $this->em->getRepository('DeskPRO:TicketEscalation')->updateRunOrder($run_order);

        return $this->createSuccessResponse();
    }
}

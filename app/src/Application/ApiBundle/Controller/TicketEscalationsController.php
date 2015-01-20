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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $esc = $special_type ? $rep->getSpecialEscalation($special_type, $id) : $rep->find($id);

        if (!$esc) {
            throw new NotFoundHttpException;
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

        if ($special_type) {
            $esc = $rep->getSpecialEscalation($special_type, $id);
        } elseif ($id) {
            $esc = $rep->find($id);
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
            throw new NotFoundHttpException;
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
            throw new NotFoundHttpException;
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

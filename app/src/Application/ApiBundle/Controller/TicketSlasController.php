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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;

class TicketSlasController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction()
    {
        $slas = $this->em->getRepository('DeskPRO:Sla')->getAllSlas();

        $data = array();

        foreach ($slas as $sla) {
            $row = array(
                'id'                => $sla->id,
                'title'             => $sla->title,
                'sla_type'          => $sla->sla_type,
            );

            $data[] = $row;
        }

        return $this->createApiResponse(array(
            'slas' => $data,
        ));
    }

    ####################################################################################################################
    # get
    ####################################################################################################################

    public function getAction($id)
    {
        $sla = $this->em->find('DeskPRO:Sla', $id);
        if (!$sla) {
            throw $this->createNotFoundException();
        }

        $data = $this->getApiData($sla);

        return $this->createApiResponse(array(
            'sla' => $data,
        ));
    }

    ####################################################################################################################
    # save
    ####################################################################################################################

    public function saveAction($id)
    {
        if ($id) {
            $sla = $this->em->find('DeskPRO:Sla', $id);
            if (!$sla) {
                throw $this->createNotFoundException();
            }
        } else {
            $sla = new Sla();
        }

        $sla->title          = $this->in->getString('title');
        $sla->sla_type       = $this->in->getString('sla_type');
        $sla->active_time    = $this->in->getString('active_time');
        $sla->apply_type     = $this->in->getString('apply_type');
        $sla->warn_time      = $this->in->getUint('warn_time');
        $sla->warn_time_unit = $this->in->getString('warn_time_unit');
        $sla->fail_time      = $this->in->getUint('fail_time');
        $sla->fail_time_unit = $this->in->getString('fail_time_unit');

        $apply_terms = new TriggerTerms();
        foreach ($this->in->getArrayValue('apply_terms') as $set) {
            if ($set) {
                $apply_terms->addTermFromArray(array('set_terms' => $set));
            }
        }
        $sla->apply_terms = $apply_terms;

        $warn_actions = new TriggerActions();
        foreach ($this->in->getArrayValue('warn_actions') as $act) {
            if ($act) {
                $warn_actions->addActionFromArray($act);
            }
        }
        $sla->warn_actions = $warn_actions;

        $fail_actions = new TriggerActions();
        foreach ($this->in->getArrayValue('fail_actions') as $act) {
            if ($act) {
                $fail_actions->addActionFromArray($act);
            }
        }
        $sla->fail_actions = $fail_actions;

        if ($sla->active_time == 'custom') {
            $sla->work_timezone = $this->in->getString('work_timezone') ?: 'UTC';
            $sla->work_start    = $this->in->getUInt('work_start');
            $sla->work_end      = $this->in->getUInt('work_end');
            $sla->work_days     = $this->in->getArrayOfUInts('work_days');

            $sla->resetHolidays();
            foreach ($this->in->getArrayValue('holidays') as $hol) {
                $sla->addHoliday(
                    $hol['name'],
                    $hol['day'],
                    $hol['month'],
                    $hol['year'] ?: null
                );
            }
        }

        $this->em->persist($sla);
        $this->em->flush();

        return $this->createSuccessResponse(array(
            'sla_id' => $sla->id,
        ));
    }

    ####################################################################################################################
    # delete
    ####################################################################################################################

    public function deleteAction($id)
    {
        $sla = $this->em->find('DeskPRO:Sla', $id);
        if (!$sla) {
            throw $this->createNotFoundException();
        }

        $old_id = $sla->id;

        $this->em->remove($sla);
        $this->em->flush();

        return $this->createSuccessResponse(array('old_id' => $old_id));
    }
}

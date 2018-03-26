<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class TicketSlasController extends AbstractController implements ProtectedControllerInterface
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

    public function listAction()
    {
        $slas = $this->em->getRepository('DeskPRO:Sla')->getAllSlas();

        $data = [];

        foreach ($slas as $sla) {
            $row = [
                'id'       => $sla->id,
                'title'    => $sla->title,
                'sla_type' => $sla->sla_type,
            ];

            $data[] = $row;
        }

        return $this->createApiResponse([
            'slas' => $data,
        ]);
    }

    //###################################################################################################################
    // get
    //###################################################################################################################

    public function getAction($id)
    {
        $sla = $this->em->find('DeskPRO:Sla', $id);
        if (!$sla) {
            throw $this->createNotFoundException();
        }

        $data = $this->getApiData($sla);

        return $this->createApiResponse([
            'sla' => $data,
        ]);
    }

    //###################################################################################################################
    // save
    //###################################################################################################################

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
                $apply_terms->addTermFromArray(['set_terms' => $set]);
            }
        }
        $sla->apply_terms = $apply_terms;

        $action_defs  = $this->container->getTicketActionDefManager();
        $warn_actions = new TriggerActions();
        foreach ($this->in->getArrayValue('warn_actions') as $act) {
            if ($act) {
                $type = $act['type'];

                if ($action_defs->hasNamedDef($type)) {
                    $act['type_class'] = $action_defs->getNamedDef($type)->getDef()->getTriggerActionClass();
                    if (!$act['type_class']) {
                        continue;
                    }
                }

                try {
                    $warn_actions->addActionFromArray($act);
                } catch (\Exception $e) {
                    return $this->createApiErrorResponse('validation_error', $e->getMessage());
                }
            }
        }
        $sla->warn_actions = $warn_actions;

        $fail_actions = new TriggerActions();
        foreach ($this->in->getArrayValue('fail_actions') as $act) {
            if ($act) {
                $type = $act['type'];

                if ($action_defs->hasNamedDef($type)) {
                    $act['type_class'] = $action_defs->getNamedDef($type)->getDef()->getTriggerActionClass();
                    if (!$act['type_class']) {
                        continue;
                    }
                }

                try {
                    $fail_actions->addActionFromArray($act);
                } catch (\Exception $e) {
                    return $this->createApiErrorResponse('validation_error', $e->getMessage());
                }
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

        return $this->createSuccessResponse([
            'sla_id' => $sla->id,
        ]);
    }

    //###################################################################################################################
    // delete
    //###################################################################################################################

    public function deleteAction($id)
    {
        $sla = $this->em->find('DeskPRO:Sla', $id);
        if (!$sla) {
            throw $this->createNotFoundException();
        }

        $old_id = $sla->id;

        $this->em->remove($sla);
        $this->em->flush();

        return $this->createSuccessResponse(['old_id' => $old_id]);
    }
}

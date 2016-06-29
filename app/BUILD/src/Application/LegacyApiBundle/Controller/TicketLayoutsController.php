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

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use Application\DeskPRO\TicketLayout\LayoutFieldFilter;
use Application\LegacyApiBundle\HttpFoundation\JsonResponse;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use Application\LegacyApiBundle\PermissionStrategy\MultiPermissions;
use Application\LegacyApiBundle\PermissionStrategy\PassPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\FormFields;

/**
 * Simple ticket layouts CRUD.
 *
 * @ApiModes("all")
 */
class TicketLayoutsController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        $multi = new MultiPermissions();
        $multi->addPermissionStrategy(new AdminManagePermission());
        $multi->addPermissionStrategy(new PassPermission(), 'listAction');

        return $multi;
    }

    ####################################################################################################################
    # get
    ####################################################################################################################

    /**
     * @param int $dep_id
     *
     * @return JsonResponse
     */
    public function getAction($dep_id = 0)
    {
        $isDefault    = false;
        $ticketLayout = null;

        if ($dep_id) {
            $dep = $this->container->getSystemService('ticket_departments')->getById($dep_id);
            if (!$dep) {
                throw $this->createNotFoundException();
            }

            $ticketLayout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(['department' => $dep]);
        }

        if (!$ticketLayout) {
            $isDefault    = true;
            $ticketLayout = $this->em->getRepository('DeskPRO:TicketLayout')->findOneBy(['department' => null]);
        }

        if (!$ticketLayout) {
            $isDefault    = true;
            $ticketLayout = new TicketLayout(null);
        }

        $filter = new LayoutFieldFilter(
            $this->container->getTicketFieldManager(),
            $this->container->getPersonFieldManager()
        );
        $filter->filterInvalid($ticketLayout->user_layout);
        $filter->filterInvalid($ticketLayout->agent_layout);

        return $this->createApiResponse([
            'layout' => [
                'user'  => $ticketLayout->user_layout->exportToArray(),
                'agent' => $ticketLayout->agent_layout->exportToArray(),
            ],
            'is_default' => $isDefault,
        ]);
    }

    ####################################################################################################################
    # stats
    ####################################################################################################################

    /**
     * @return JsonResponse
     */
    public function getLayoutStatsAction()
    {
        $depsWithLayouts = $this->db->fetchAllCol('
            SELECT department_id
            FROM ticket_layouts
            WHERE department_id IS NOT NULL
        ');
        if ($depsWithLayouts) {
            $depsWithLayouts = array_fill_keys($depsWithLayouts, true);
        }

        $data = ['default' => [], 'custom' => []];

        /** @var \Application\DeskPRO\Departments\TicketDepartments $ticketDeps */
        $ticketDeps = $this->container->getSystemService('ticket_departments');

        foreach ($ticketDeps->getAll() as $dep) {
            if (isset($depsWithLayouts[$dep->getId()])) {
                $data['custom'][] = $dep->toApiData();
            } else {
                $data['default'][] = $dep->toApiData();
            }
        }

        return $this->createApiResponse([
            'layout_info'   => $data,
            'count_custom'  => count($data['custom']),
            'count_default' => count($data['default']),
        ]);
    }

    ####################################################################################################################
    # save
    ####################################################################################################################

    /**
     * @param int $dep_id
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function saveAction($dep_id = 0)
    {
        if ($dep_id) {
            $dep = $this->container->getSystemService('ticket_departments')->getById($dep_id);
            if (!$dep) {
                throw $this->createNotFoundException();
            }

            $layout = $this->em->getRepository(TicketLayout::class)->findOneBy(['department' => $dep]);
        } else {
            $dep    = null;
            $layout = $this->em->getRepository(TicketLayout::class)->findOneBy(['department' => null]);
        }

        if (!$layout) {
            $layout = new TicketLayout($dep);
        }

        $userLayout  = new Layout();
        $agentLayout = new Layout();

        $layoutUserArray  = $this->in->getArrayValue('layout.user');
        $layoutAgentArray = $this->in->getArrayValue('layout.agent');

        $fnOrder = function ($a, $b) {
            $a_o = isset($a['display_order']) ? $a['display_order'] : 0;
            $b_o = isset($b['display_order']) ? $b['display_order'] : 0;

            if ($a_o == $b_o) {
                return 0;
            }

            return $a_o < $b_o ? -1 : 1;
        };

        usort($layoutUserArray, $fnOrder);
        usort($layoutAgentArray, $fnOrder);

        foreach ($layoutUserArray as $field_info) {
            $field = new LayoutField($field_info['field_type'], $field_info['field_id'] ?: null);
            if (!$this->filterField($field)) {
                continue;
            }
            $field->setOptionsFromArray($field_info['options']);
            $userLayout->add($field);
        }
        foreach ($layoutAgentArray as $field_info) {
            $field = new LayoutField($field_info['field_type'], $field_info['field_id'] ?: null);
            if (!$this->filterField($field)) {
                continue;
            }
            $field->setOptionsFromArray($field_info['options']);
            $agentLayout->add($field);
        }

        $layout->setUserLayout($userLayout);
        $layout->setAgentLayout($agentLayout);

        $this->em->persist($layout);
        $this->em->flush();

        // Sanity check
        if ($layout->department) {
            $this->db->executeUpdate('DELETE FROM ticket_layouts WHERE department_id = ? AND id != ?',
                [$layout->getDepartment()->getId(), $layout->getId()]);
        } else {
            $this->db->executeUpdate('DELETE FROM ticket_layouts WHERE department_id IS NULL AND id != ?',
                [$layout->getId()]);
        }

        return $this->createSuccessResponse();
    }

    private function filterField(LayoutField $field)
    {
        static $fields          = [];
        static $customFieldsIds = [];
        if (!$fields) {
            // We retrieve constants from FormFields to filter ticket layout
            $reflectionClass = new \ReflectionClass(FormFields::class);
            $fields          = $reflectionClass->getConstants();
        }

        if (!$customFieldsIds) {
            /** @var \Application\DeskPRO\CustomFields\TicketFieldManager $fieldManager */
            $fieldManager    = $this->container->getSystemService('ticket_fields_manager');
            $customFields    = $fieldManager->getDefinedFields();
            $customFieldsIds = array_map(function (CustomDefTicket $o) { return $o->getId(); }, $customFields);
        }
        if (!in_array($field->getFieldType(), $fields)) {
            return false;
        }
        if (in_array($field->getFieldType(), ['user_field', 'ticket_field', 'org_field', 'custom_field'])) {
            if (!in_array($field->getFieldId(), $customFieldsIds)) {
                return false;
            }
        }

        return true;
    }

    ####################################################################################################################
    # delete
    ####################################################################################################################

    /**
     * @param $dep_id
     *
     * @throws \Exception
     *
     * @return JsonResponse
     */
    public function deleteAction($dep_id)
    {
        $dep = $this->container->getSystemService('ticket_departments')->getById($dep_id);
        if (!$dep) {
            throw $this->createNotFoundException();
        }

        $this->db->executeUpdate('DELETE FROM ticket_layouts WHERE department_id = ?', [$dep->id]);

        return $this->createSuccessResponse();
    }

    ####################################################################################################################
    # get-field-status
    ####################################################################################################################

    /**
     * Get field use statistic.
     *
     * @param $field_id
     *
     * @return JsonResponse
     */
    public function getFieldStatusAction($field_id)
    {
        $dm = $this->getContainer()->getTicketDepartments();
        $lm = $this->getContainer()->getTicketLayoutManager();

        $agentStatus = [];
        $userStatus  = [];

        foreach ($lm->getAgentLayouts() as $id => $layout) {
            $dep = null;
            if ($id) {
                $dep = $dm->getById($id);
                if (!$dep) {
                    continue;
                }
                $dep = $dep->toApiData();
            }
            $agentStatus[$id] = [
                'department' => $dep,
                'enabled'    => $layout->has($field_id),
            ];
        }
        foreach ($lm->getUserLayouts() as $id => $layout) {
            $dep = null;
            if ($id) {
                $dep = $dm->getById($id);
                if (!$dep) {
                    continue;
                }
                $dep = $dep->toApiData();
            }
            $userStatus[$id] = [
                'department' => $dep,
                'enabled'    => $layout->has($field_id),
            ];
        }

        $sortFn = function ($a, $b) {
            $ao = $a['department'] ? $a['department']['display_order'] : -1000;
            $bo = $b['department'] ? $b['department']['display_order'] : -1000;

            if ($ao == $bo) {
                return 0;
            }

            return $ao < $bo ? -1 : 1;
        };
        uasort($agentStatus, $sortFn);
        uasort($userStatus, $sortFn);

        return $this->createApiResponse([
            'agent_layouts' => $agentStatus,
            'user_layouts'  => $userStatus,
        ]);
    }

    ####################################################################################################################
    # save-field-status
    ####################################################################################################################

    /**
     * @param $field_id
     *
     * @return JsonResponse
     */
    public function saveFieldStatusAction($field_id)
    {
        $enableUserLayouts  = $this->in->getArrayOfUInts('enable_user_layouts');
        $enableAgentLayouts = $this->in->getArrayOfUInts('enable_agent_layouts');

        $layoutRecords = $this->em->getRepository(TicketLayout::class)->findAll();

        $fieldType   = $field_id;
        $fieldTypeId = null;

        if (preg_match('#^(ticket_field)_(\d+)$#', $field_id, $m)) {
            $fieldType   = $m[1];
            $fieldTypeId = $m[2];
        }

        foreach ($layoutRecords as $layout) {
            /* @var $layout TicketLayout */
            $depId = $layout->getDepartment() ? $layout->getDepartment()->getId() : 0;

            $userLayout  = clone $layout->getUserLayout();
            $agentLayout = clone $layout->getAgentLayout();

            $hasUser  = $layout->getUserLayout()->has($field_id);
            $hasAgent = $layout->getAgentLayout()->has($field_id);

            $wantUser  = in_array($depId, $enableUserLayouts);
            $wantAgent = in_array($depId, $enableAgentLayouts);

            $change = false;

            if ($hasUser && !$wantUser) {
                $userLayout->remove($field_id);
                $change = true;
            } elseif (!$hasUser && $wantUser) {
                $field = new LayoutField($fieldType, $fieldTypeId);
                $field->setOptionsFromArray([
                    'on_editticket'      => true,
                    'on_viewticket'      => true,
                    'on_viewticket_mode' => 'always',
                    'on_newticket'       => true,
                ]);
                $userLayout->add($field, 'message');
                $change = true;
            }

            if ($hasAgent && !$wantAgent) {
                $agentLayout->remove($field_id);
                $change = true;
            } elseif (!$hasAgent && $wantAgent) {
                $field = new LayoutField($fieldType, $fieldTypeId);
                $field->setOptionsFromArray([
                    'on_editticket'      => true,
                    'on_viewticket'      => true,
                    'on_viewticket_mode' => 'always',
                    'on_newticket'       => true,
                ]);
                $agentLayout->add($field, 'message');
                $change = true;
            }

            if ($change) {
                $layout->setUserLayout($userLayout);
                $layout->setAgentLayout($agentLayout);
                $layout->setDateUpdated(new \DateTime());
                $this->em->persist($layout);
            }
        }

        $this->em->flush();

        return $this->createApiSuccessResponse();
    }
}

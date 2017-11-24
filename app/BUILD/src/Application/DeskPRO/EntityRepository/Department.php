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
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Department as DepartmentEntity;

class Department extends AbstractCategoryRepository
{
    /**
     * @return mixed
     */
    public function countAll()
    {
        return $this->_em->createQuery('SELECT count(d) FROM DeskPRO:Department d')->getSingleScalarResult();
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getAll()
    {
        return $this->_em->createQuery('
            SELECT d
            FROM DeskPRO:Department d
            ORDER BY d.display_order ASC
        ')->execute();
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getTicketDepartments()
    {
        return $this->_em->createQuery('
            SELECT d
            FROM DeskPRO:Department d
            WHERE d.is_tickets_enabled = true
            ORDER BY d.display_order ASC
        ')->execute();
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */
    public function getChatDepartments()
    {
        return $this->_em->createQuery(
            '
            SELECT d
            FROM DeskPRO:Department d
            WHERE d.is_chat_enabled = true
            ORDER BY d.display_order ASC
            '
        )->execute();
    }

    /**
     * @param DepartmentEntity $dep
     *
     * @return array
     */
    public function getPermissionsInfo(DepartmentEntity $dep)
    {
        $perms = App::getDb()->fetchAll(
            'SELECT usergroup_id, person_id, name FROM department_permissions WHERE department_id = ?',
            [$dep->getId()]
        );

        $data = [
            'usergroups'  => [],
            'agentgroups' => [],
            'agents'      => [],
        ];

        foreach ($perms as $perm) {
            if ($perm['usergroup_id']) {
                if (App::getContainer()->getDataService('Usergroup')->get($perm['usergroup_id'])->is_agent_group) {
                    $data['agentgroups'][] = [
                        'usergroup_id' => (int) $perm['usergroup_id'],
                        'perm_name'    => $perm['name'],
                    ];
                } else {
                    $data['usergroups'][] = [
                        'usergroup_id' => (int) $perm['usergroup_id'],
                        'perm_name'    => $perm['name'],
                    ];
                }
            } elseif ($perm['person_id']) {
                $data['agents'][] = [
                    'agent_id'  => (int) $perm['person_id'],
                    'perm_name' => $perm['name'],
                ];
            }
        }

        return $data;
    }

    /**
     * Get the default ticket department for a given context (ticket, chat).
     *
     * @param string $context
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Entity\Department *
     */
    public function getDefaultDepartment($context)
    {
        switch ($context) {
            case 'ticket':
                $opt        = 'core_tickets.default_department';
                $checkField = 'is_tickets_enabled';
                break;
            case 'chat':
                $opt        = 'core.chat.default_department';
                $checkField = 'is_chat_enabled';
                break;
            default:
                throw new \InvalidArgumentException("Unknown context `$context`");
        }

        $depId = App::getSetting($opt);
        $dep   = null;
        if ($depId) {
            /** @var \Application\DeskPRO\Entity\Department $dep */
            $dep = $this->find($depId);
        }

        if (!$dep) {
            // There should always be a correct default set, but this is
            // error handling in case
            $depId = App::getDb()->fetchColumn("
                SELECT d.id
                FROM departments d
                LEFT JOIN departments AS subdep ON (subdep.parent_id = d.id)
                WHERE subdep.id IS NULL AND d.$checkField = 1
                ORDER BY d.display_order ASC
                LIMIT 1
            ");

            if ($depId) {
                $dep = $this->find($depId);
            }
        }

        return $dep;
    }

    /**
     * @param $context
     *
     * @throws \InvalidArgumentException
     *
     * @return \Application\DeskPRO\Entity\Department
     */
    public function getChildDepartments($context)
    {
        switch ($context) {
            case 'ticket':
                $checkField = 'AND d.is_tickets_enabled = 1';
                break;
            case 'chat':
                $checkField = 'AND d.is_chat_enabled = 1';
                break;
            case 'both':
                $checkField = '';
                break;
            default:
                throw new \InvalidArgumentException("Unknown context `$context`");
        }

        return $this->getEntityManager()->createQuery("
            SELECT d
            FROM DeskPRO:Department d
            LEFT JOIN d.children c 
            WHERE c.id IS NULL
                $checkField
        ")->execute();
    }
}

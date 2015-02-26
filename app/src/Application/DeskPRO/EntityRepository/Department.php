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
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Department as DepartmentEntity;

class Department extends AbstractCategoryRepository
{
    public function getAll()
    {
        return $this->getRootNodes();
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */

    public function getTicketDepartments()
    {
        return $this->_em->createQuery("
            SELECT d
            FROM DeskPRO:Department d
            WHERE d.is_tickets_enabled = true
            ORDER BY d.display_order ASC
        ")->execute();
    }

    /**
     * @return \Application\DeskPRO\Entity\Department[]
     */

    public function getChatDepartments()
    {
        return $this->_em->createQuery(
            "
            SELECT d
            FROM DeskPRO:Department d
            WHERE d.is_chat_enabled = true
            ORDER BY d.display_order ASC
            "
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
            "SELECT usergroup_id, person_id, name FROM department_permissions WHERE department_id = ?",
            array($dep->id)
        );

        $data = array(
            'usergroups'  => array(),
            'agentgroups' => array(),
            'agents'      => array()
        );

        foreach ($perms as $perm) {

            if ($perm['usergroup_id']) {

                if (App::getContainer()->getDataService('Usergroup')->get($perm['usergroup_id'])->is_agent_group) {

                    $data['agentgroups'][] = array(
                        'usergroup_id' => (int)$perm['usergroup_id'],
                        'perm_name'    => $perm['name'],
                    );

                } else {

                    $data['usergroups'][] = array(
                        'usergroup_id' => (int)$perm['usergroup_id'],
                        'perm_name'    => $perm['name'],
                    );
                }
            } elseif ($perm['person_id']) {

                $data['agents'][] = array(
                    'agent_id'  => (int)$perm['person_id'],
                    'perm_name' => $perm['name']
                );
            }
        }

        return $data;
    }


    /**
     * Get the default ticket department for a given context (ticket, chat)
     *
     * @param  string                                 $context
     * @return \Application\DeskPRO\Entity\Department *
     * @throws \InvalidArgumentException
     */

    public function getDefaultDepartment($context)
    {
        switch ($context) {
            case 'ticket':
                $opt = 'core.tickets.default_department';
                $check_field = 'is_tickets_enabled';
                break;
            case 'chat':
                $opt = 'core.chat.default_department';
                $check_field = 'is_chat_enabled';
                break;
            default:
                throw new \InvalidArgumentException("Unknown context `$context`");
        }

        $dep_id = App::getSetting($opt);
        $dep = null;
        if ($dep_id) {
            $dep = $this->find($dep_id);
        }

        if (!$dep) {
            // There should always be a correct default set, but this is
            // error handling in case
            $dep_id = App::getDb()->fetchColumn("
                SELECT d.id
                FROM departments d
                LEFT JOIN departments AS subdep ON (subdep.parent_id = d.id)
                WHERE subdep.id IS NULL AND d.$check_field = 1
                ORDER BY d.display_order ASC
                LIMIT 1
            ");

            if ($dep_id) {
                $dep = $this->find($dep_id);
            }
        }

        return $dep;
    }

    /**
     * @param $context
     *
     * @return \Application\DeskPRO\Entity\Department
     * @throws \InvalidArgumentException
     */

    public function getChildDepartments($context)
    {
        switch ($context) {
            case 'ticket':
                $check_field = 'is_tickets_enabled';
                break;
            case 'chat':
                $check_field = 'is_chat_enabled';
                break;
            default:
                throw new \InvalidArgumentException("Unknown context `$context`");
        }

        return $this->getEntityManager()->createQuery("
            SELECT d
            FROM DeskPRO:Department d
            WHERE d.parent IS NOT NULL
                AND d.$check_field = 1
        ")->execute();
    }
}

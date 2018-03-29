<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People\Agents;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Usergroup;
use DeskPRO\Bundle\AuditBundle\Log\AuditLog;
use DeskPRO\Bundle\AuditBundle\Log\AuditLogService;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;

class AgentDelete
{
    /**
     * @var Person
     */
    private $agent;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $db;

    /**
     * @var AuditLogService
     */
    private $auditService;

    /**
     * @param Person          $agent
     * @param EntityManager   $em
     * @param AuditLogService $auditService
     */
    public function __construct(Person $agent, EntityManager $em, $auditService)
    {
        if (!$agent->is_agent) {
            throw new \InvalidArgumentException();
        }

        $this->agent        = $agent;
        $this->em           = $em;
        $this->db           = $em->getConnection();
        $this->auditService = $auditService;
    }

    /**
     * Makes the agent account a user account instead.
     */
    public function deleteToUser()
    {
        $this->agent->is_agent              = false;
        $this->agent->can_agent             = false;
        $this->agent->can_admin             = false;
        $this->agent->can_billing           = false;
        $this->agent->can_reports           = false;
        $this->agent->was_agent             = true;
        $this->agent->is_deleted            = false;
        $this->agent->is_disabled           = false;
        $this->agent->override_display_name = ''; // only settable for agents currently
        $this->em->persist($this->agent);

        $this->db->beginTransaction();
        try {
            $this->em->flush();

            // Specific department permissions are agent-only feature, remove those
            // Users get them from their usergroups
            $this->db->executeUpdate('
                DELETE FROM department_permissions
                WHERE person_id = ?
            ', [$this->agent->id]);

            /** @var \Application\DeskPRO\EntityRepository\Usergroup $userGroupRepository */
            $userGroupRepository = $this->em->getRepository(Usergroup::class);
            // Agent groups
            $agentGroups = $userGroupRepository->getAgentUsergroups();
            if ($agentGroups) {
                $agentGroupIds = Arrays::flattenToIndex($agentGroups, 'id');
                $this->db->executeUpdate('
                    DELETE FROM person2usergroups
                    WHERE person_id = ? AND usergroup_id IN (?)
                ', [$this->agent->id, $agentGroupIds], [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY]);
            }

            // Assigned tickets
            $this->db->executeUpdate('
                UPDATE tickets SET agent_id = NULL
                WHERE agent_id = ?
            ', [$this->agent->id]);
            $this->db->executeUpdate('
                UPDATE tickets_search_active SET agent_id = NULL
                WHERE agent_id = ?
            ', [$this->agent->id]);

            // Filters
            $this->db->executeUpdate('
                DELETE FROM ticket_filters
                WHERE person_id = ?
            ', [$this->agent->id]);

            // Subscriptions
            $this->db->executeUpdate('
                DELETE FROM ticket_filter_subscriptions
                WHERE person_id = ?
            ', [$this->agent->id]);

            // Agent team
            $this->db->delete('agent_team_members', ['person_id' => $this->agent->getId()]);

            // Permission overrides
            $this->db->delete('permissions', ['person_id' => $this->agent->getId()]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }

        $auditLog = new AuditLog();
        $auditLog
            ->setAction('agent.downgrade')
            ->setPerformerId(App::getCurrentPerson() ? App::getCurrentPerson()->getId() : 0)
            ->setDescription(sprintf('Agent with email: %s downgraded to user', $this->agent->getEmail()))
            ->setDateCreated(new \DateTime())
            ->setObjectType(TypeUtils::getBaseTypeName($this->agent))
            ->setObjectId($this->agent->getId())
            ->setObjectName(sprintf('Agent "%s" downgraded', $this->agent->getEmail()))
            ->setPerformerName(App::getCurrentPerson() ? App::getCurrentPerson()->getDisplayName() : '');
        $this->auditService->write($auditLog);

        $this->clearSessions();

        return true;
    }

    /**
     * Marks the agent account as deleted.
     */
    public function softDelete()
    {
        $this->agent->is_deleted = true;
        $this->agent->can_admin  = false; // to be safe

        // Remove their permissions
        $this->db->delete('department_permissions', ['person_id' => $this->agent->getId()]);
        $this->db->delete('permissions', ['person_id' => $this->agent->getId()]);
        $this->db->delete('agent_team_members', ['person_id' => $this->agent->getId()]);
        $this->db->delete('ticket_filter_subscriptions', ['person_id' => $this->agent->getId()]);

        // Any open tickets should be unassigned
        $this->db->executeUpdate("
            UPDATE tickets SET agent_id = NULL
            WHERE agent_id = ? AND status IN ('awaiting_agent')
        ", [$this->agent->id]);
        $this->db->executeUpdate("
            UPDATE tickets_search_active SET agent_id = NULL
            WHERE agent_id = ? AND status IN ('awaiting_agent')
        ", [$this->agent->id]);

        $this->em->persist($this->agent);
        $this->em->flush();

        $this->clearSessions();

        return true;
    }

    /**
     * Clears any sessions the agent has open.
     *
     * This is needed because they might have an active agent session right now,
     * but if they actually do anything (even ajax polling) will result in some exceptions
     * because they arent actually agents anymore.
     */
    private function clearSessions()
    {
        $this->db->delete('sessions', ['person_id' => $this->agent->getId()]);
    }
}

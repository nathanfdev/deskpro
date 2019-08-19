<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverCriteria as ApproverCriteriaObj;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class ApproverCriteriaValidator
 *
 * @package DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval
 */
class ApproverCriteriaValidator extends ConstraintValidator
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ApproverCriteriaValidator constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param ApproverCriteriaObj $value
     * @param ApproverCriteria|Constraint $constraint
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($value, Constraint $constraint)
    {
        $context = $this->context;

        if (!($value instanceof ApproverCriteriaObj)) {
            $context
                ->buildViolation($constraint->invalidObjectMessage)
                ->setCode(ApproverCriteria::APPROVER_CRITERIA)
                ->addViolation()
            ;
        }

        // Reduce criteria to flags for validation
        $objectFlags = [
            (bool) count($value->getAgents()),
            $value->isAllAgents(),
            (bool) count($value->getUsers()),
            $value->isAllUsers(),
            $value->isOrganizationManagers(),
            (bool) count($value->getTeams()),
            (bool) count($value->getDepartments()),
        ];

        // Must have at least one criteria
        if ((count(array_unique($objectFlags)) === 1) && current($objectFlags) === false) {
            $context
                ->buildViolation($constraint->mustProvideAtLeastOneCriteriaMessage)
                ->setCode(ApproverCriteria::APPROVER_CRITERIA)
                ->addViolation()
            ;
        }

        // Validate agent IDs
        if (!$this->isAgentListValid($value->getAgents())) {
            $context
                ->buildViolation($constraint->invalidAgentListMessage)
                ->setCode(ApproverCriteria::APPROVER_CRITERIA)
                ->addViolation()
            ;
        }

        // Validate user IDs
        if (!$this->isUserListValid($value->getUsers())) {
            $context
                ->buildViolation($constraint->invalidUserListMessage)
                ->setCode(ApproverCriteria::APPROVER_CRITERIA)
                ->addViolation()
            ;
        }

        // Validate team IDs
        if (!$this->isTeamListValid($value->getTeams())) {
            $context
                ->buildViolation($constraint->invalidTeamListMessage)
                ->setCode(ApproverCriteria::APPROVER_CRITERIA)
                ->addViolation()
            ;
        }

        // Validate department IDs
        if (!$this->isDepartmentListValid($value->getDepartments())) {
            $context
                ->buildViolation($constraint->invalidDepartmentListMessage)
                ->setCode(ApproverCriteria::APPROVER_CRITERIA)
                ->addViolation()
            ;
        }
    }

    /**
     * @param array $agentIds
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function isAgentListValid(array $agentIds)
    {
        if (!count($agentIds)) {
            return true;
        }

        $agentCount = (int) $this->em->createQueryBuilder()
            ->select('COUNT(p)')
            ->from(Person::class, 'p')
            ->andWhere('p.id IN (:agentIds)')
            ->andWhere('p.is_agent = TRUE')
            ->andWhere('p.is_deleted = FALSE')
            ->setParameter('agentIds', $agentIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (count($agentIds) === $agentCount);
    }

    /**
     * @param array $userIds
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function isUserListValid(array $userIds)
    {
        if (!count($userIds)) {
            return true;
        }

        $userCount = (int) $this->em->createQueryBuilder()
            ->select('COUNT(p)')
            ->from(Person::class, 'p')
            ->andWhere('p.id IN (:userIds)')
            ->andWhere('p.is_user = TRUE')
            ->andWhere('p.is_agent = FALSE')
            ->andWhere('p.is_deleted = FALSE')
            ->setParameter('userIds', $userIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (count($userIds) === $userCount);
    }

    /**
     * @param array $teamIds
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function isTeamListValid(array $teamIds)
    {
        if (!count($teamIds)) {
            return true;
        }

        $teamCount = (int) $this->em->createQueryBuilder()
            ->select('COUNT(t)')
            ->from(AgentTeam::class, 't')
            ->andWhere('t.id IN (:teamIds)')
            ->setParameter('teamIds', $teamIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (count($teamIds) === $teamCount);
    }

    /**
     * @param array $departmentIds
     * @return bool
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function isDepartmentListValid(array $departmentIds)
    {
        if (!count($departmentIds)) {
            return true;
        }

        $departmentCount = (int) $this->em->createQueryBuilder()
            ->select('COUNT(d)')
            ->from(Department::class, 'd')
            ->andWhere('d.id IN (:departmentIds)')
            ->setParameter('departmentIds', $departmentIds, Connection::PARAM_INT_ARRAY)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (count($departmentIds) === $departmentCount);
    }
}

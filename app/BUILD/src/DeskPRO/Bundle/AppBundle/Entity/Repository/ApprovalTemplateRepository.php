<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalTemplate;

/**
 * Class ApprovalTemplateRepository
 *
 * @package DeskPRO\Bundle\AppBundle\Entity\Repository
 */
class ApprovalTemplateRepository extends AbstractEntityRepository
{
    /**
     * Find only templates where the agent is not allowed to choose approvers
     *
     * @return ApprovalTemplate|ApprovalTemplate[]
     */
    public function findByAgentNotAllowedToSelectApprovers()
    {
        /** @var ApprovalTemplate[] $templates */
        $templates = $this
            ->createQueryBuilder('at')
            ->orderBy('at.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return array_filter($templates, function (ApprovalTemplate $template) {
            return !($template->getApproverCriteria() && $template->getApproverCriteria()->canChooseApprovers());
        });
    }
}

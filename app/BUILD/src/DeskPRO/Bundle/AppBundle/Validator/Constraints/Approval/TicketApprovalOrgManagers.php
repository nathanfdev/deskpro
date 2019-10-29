<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Approval;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketApprovalOrgManagers.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION", "CLASS"})
 */
class TicketApprovalOrgManagers extends Constraint
{
    const USER_HAS_NO_ORG     = 'user_has_no_org';
    const ORG_HAS_NO_MANAGERS = 'org_has_no_managers';

    public $userHasNoOrgMessage     = 'Cannot submit request as this user does not belong to an organization.';
    public $orgHasNoManagersMessage = 'Cannot submit request as this user\'s organization has no defined manager.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}

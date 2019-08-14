<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Approval;

use DeskPRO\Bundle\AppBundle\Entity\AbstractApproval;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApprovalType
 *
 * @ORM\Entity
 * @ORM\Table(name="approval_templates")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 *
 * @JMS\ExclusionPolicy("all")
 *
 * @category Entities
 */
class ApprovalTemplate extends AbstractApproval
{
    /**
     * @var ApproverCriteria
     *
     * @ORM\Embedded(
     *     class="DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverCriteria",
     *     columnPrefix="approver_criteria_",
     * )
     *
     * @JMS\Expose
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Entity\Approval\ApproverCriteria")
     */
    private $approverCriteria;

    /**
     * @return ApproverCriteria
     */
    public function getApproverCriteria()
    {
        return $this->approverCriteria;
    }

    /**
     * @param ApproverCriteria $approverCriteria
     * @return self
     */
    public function setApproverCriteria(ApproverCriteria $approverCriteria)
    {
        $this->approverCriteria = $approverCriteria;

        return $this;
    }
}

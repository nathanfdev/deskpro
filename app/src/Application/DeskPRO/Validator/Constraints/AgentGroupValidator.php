<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @category Validator
 */

namespace Application\DeskPRO\Validator\Constraints;

use Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService;
use Application\DeskPRO\Entity\Usergroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AgentGroupValidator extends ConstraintValidator
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\SystemServices\UsergroupDataService
     */
    private $usergroup_data;


    /**
     * @param UsergroupDataService $usergroup_data
     */
    public function __construct(UsergroupDataService $usergroup_data)
    {
        $this->usergroup_data = $usergroup_data;
    }


    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (is_object($value)) {
            if (!($value instanceof Usergroup)) {
                $this->context->addViolation($constraint->typeMessage, array('{{type}}' => get_class($value)));
            } else {
                if ($constraint->checkRepos) {
                    if (!$value->id || !$this->usergroup_data->getAgentGroup($value->id)) {
                        $this->context->addViolation($constraint->message);
                    }
                }
            }
        } else {
            if (!$constraint->acceptId) {
                $this->context->addViolation($constraint->message);
            }

            if (!$this->usergroup_data->getAgentGroup($value)) {
                $this->context->addViolation($constraint->message);
            }
        }
    }


    /**
     * @return string
     */
    public static function getAlias()
    {
        return 'AgentGroup';
    }
}

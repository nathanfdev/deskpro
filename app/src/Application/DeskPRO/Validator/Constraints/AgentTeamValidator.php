<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Application\DeskPRO\Entity\AgentTeam;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AgentTeamValidator extends ConstraintValidator
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService
	 */
	private $agent_data;


	/**
	 * @param AgentDataService $agent_data
	 */
	public function __construct(AgentDataService $agent_data)
	{
		$this->agent_data = $agent_data;
	}


	/**
	 * @param mixed $value
	 * @param Constraint $constraint
	 */
	public function validate($value, Constraint $constraint)
	{
		if (is_object($value)) {
			if (!($value instanceof AgentTeam)) {
				$this->context->addViolation($constraint->typeMessage, array('{{type}}' => get_class($value)));
			} else {
				if ($constraint->checkRepos) {
					if (!$value->id || !$this->agent_data->getTeam($value->id)) {
						$this->context->addViolation($constraint->message);
					}
				}
			}
		} else {
			if (!$constraint->acceptId) {
				$this->context->addViolation($constraint->message);
			}

			if (!$this->agent_data->getAgentGroup($value)) {
				$this->context->addViolation($constraint->message);
			}
		}
	}


	/**
	 * @return string
	 */
	public static function getAlias()
	{
		return 'AgentTeam';
	}
}
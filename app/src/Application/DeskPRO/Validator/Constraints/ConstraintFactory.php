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

namespace Application\DeskPRO\Validator\Constraints;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

/**
 * Central factory for custom DeskPRO validators.
 *
 * === Adding a custom validator ===
 * 1. Create MyNameConstraint.php and MyNameValidator.php in this directory.
 * 2. MyNameConstraint is a typical constraint, but make sure it has validatedBy with a unique name
 * 3. MyNameValidator is again a typical validator, but make sure it has a getAlias method that
 *    returns the same name you defined in MyNameConstraint::validatedBy
 * 4. Add getMyNameValidator to this factory class
 * 5. Edit the DI config at /app/sys/config/config.php and add the MyNameValidator class
 *    to the to "Validators and Constraints" array
 */
class ConstraintFactory
{
    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    private $container;


    /**
     * @param DeskproContainer $container
     */
    public function __construct(DeskproContainer $container)
    {
        $this->container = $container;
    }


    /**
     * @return AgentTeamValidator
     */
    public function getAgentTeamValidator()
    {
        return new AgentTeamValidator($this->container->getAgentData());
    }


    /**
     * @return AgentGroupValidator
     */
    public function getAgentGroupValidator()
    {
        return new AgentGroupValidator($this->container->getDataService('Usergroup'));
    }
}

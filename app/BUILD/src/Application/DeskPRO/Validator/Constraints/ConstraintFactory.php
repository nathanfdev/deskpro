<?php

/**
 * DeskPRO.
 *
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

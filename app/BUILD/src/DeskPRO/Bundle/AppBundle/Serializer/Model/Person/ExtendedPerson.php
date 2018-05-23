<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Person;

use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ExtendedPerson.
 */
class ExtendedPerson extends BasePerson
{
    /**
     * Is this person a user?
     *
     * @var bool
     */
    protected $isUser;

    /**
     * Was this person an agent?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $wasAgent;

    /**
     * Is person allowed to use agent interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canAgent;

    /**
     * Is person allowed to use admin interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canAdmin;

    /**
     * Is person allowed to use billing interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canBilling;

    /**
     * Is person allowed to use reports interface.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $canReports;

    /**
     * PersonEntity constructor.
     *
     * @param PersonEntity $person
     * @param Avatar       $avatar
     */
    public function __construct(PersonEntity $person, Avatar $avatar)
    {
        parent::__construct($person, $avatar);

        $this->isUser     = $person->isUser();
        $this->wasAgent   = $person->wasAgent();
        $this->canAgent   = $person->canAgent();
        $this->canAdmin   = $person->canAdmin();
        $this->canBilling = $person->getRealCanBilling();
        $this->canReports = $person->can_reports;
    }
}

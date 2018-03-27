<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Person;

use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetPerson.
 */
class WidgetPerson
{
    /**
     * The unique ID of person.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Person display name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $display_name;

    /**
     * True if person is agent.
     *
     * @var bool
     */
    private $is_agent;

    /**
     * Person`s avatar.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Content\Avatar")
     *
     * @var Avatar
     */
    private $avatar;

    /**
     * PersonEntity constructor.
     *
     * @param PersonEntity $person
     * @param Avatar       $avatar
     */
    public function __construct(PersonEntity $person, Avatar $avatar)
    {
        $this->id           = $person->getId();
        $this->display_name = $person->getDisplayNameUser();
        $this->is_agent     = $person->isAgent();
        $this->avatar       = $avatar;
    }
}

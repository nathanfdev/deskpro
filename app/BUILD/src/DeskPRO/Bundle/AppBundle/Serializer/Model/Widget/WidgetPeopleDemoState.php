<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Widget;

use Application\DeskPRO\Entity\Person;
use JMS\Serializer\Annotation as JMS;

/**
 * Class WidgetPeopleDemoState.
 */
class WidgetPeopleDemoState
{
    const PERSON_AVATAR_SYS_PREFIX = 'widget_live_demo_avatar_';

    const AGENTS_COUNT = 3;
    const USERS_COUNT  = 3;

    /**
     * @var Person[]
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\Person>")
     */
    private $agents;

    /**
     * @var Person[]
     *
     * @JMS\Type("array<Application\DeskPRO\Entity\Person>")
     */
    private $users;

    /**
     * Constructor.
     *
     * @param array $agents
     * @param array $users
     */
    public function __construct(array $agents, array $users)
    {
        $this->agents = $agents;
        $this->users  = $users;
    }
}

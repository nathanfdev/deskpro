<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ApiPerson;

/**
 * Class PersonHandler.
 */
class PersonHandler extends AbstractEntityHandler
{
    /**
     * @var AgentDataService
     */
    private $agentDataService;

    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * Constructor.
     *
     * @param AvatarResolver   $avatarResolver
     * @param AgentDataService $agentDataService
     */
    public function __construct(AvatarResolver $avatarResolver, AgentDataService $agentDataService)
    {
        $this->avatarResolver   = $avatarResolver;
        $this->agentDataService = $agentDataService;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return Person::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Person $entity
     */
    protected function createModel($entity)
    {
        $api_person = new ApiPerson($entity);
        $api_person
            ->setAvatar($this->avatarResolver->getAvatarModel($entity))
            ->setOnline($this->agentDataService->isAgentOnline($entity))
        ;

        $last_seen = $this->agentDataService->getLastSeen($entity);
        if ($last_seen) {
            $api_person->setLastSeen(new \DateTime($last_seen));
        }

        return $api_person;
    }
}

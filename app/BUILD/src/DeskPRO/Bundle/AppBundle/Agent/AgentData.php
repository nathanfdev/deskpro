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

namespace DeskPRO\Bundle\AppBundle\Agent;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use Doctrine\ORM\EntityManager;

/**
 * Class AgentData.
 */
class AgentData
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $cachedNames;

    /**
     * @var array
     */
    private $cachedSelectbox;

    /**
     * Constructor.
     *
     * @param AvatarResolver $avatarResolver
     * @param EntityManager  $em
     */
    public function __construct(AvatarResolver $avatarResolver, EntityManager $em)
    {
        $this->avatarResolver = $avatarResolver;
        $this->em             = $em;
    }

    /**
     * @return array
     */
    public function getAgentNames()
    {
        if (!$this->cachedNames) {
            /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
            $personRepo        = $this->em->getRepository(Person::class);
            $this->cachedNames = $personRepo->getAgentNames();
        }

        return $this->cachedNames;
    }

    /**
     * @return array
     */
    public function getSelectboxList()
    {
        if (!$this->cachedSelectbox) {
            $names   = $this->getAgentNames();
            $avatars = $this->avatarResolver->getAvatars(array_keys($names), '{{size}}');

            $encodedTag = urlencode('{{size}}');

            foreach ($names as $agentId => $agentName) {
                $pictureUrl = $avatars[$agentId];

                $this->cachedSelectbox[$agentId] = [
                    'id'             => $agentId,
                    'display_name'   => $agentName,
                    'picture_url_15' => $pictureUrl ? str_replace($encodedTag, 15, $pictureUrl) : null,
                    'picture_url_20' => $pictureUrl ? str_replace($encodedTag, 20, $pictureUrl) : null,
                ];
            }
        }

        return $this->cachedSelectbox;
    }
}

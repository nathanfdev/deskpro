<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use Symfony\Component\Form\DataTransformerInterface;

class AgentTeamProjectMemberTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TaskProject
     */
    private $project;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     * @param TaskProject   $project
     */
    public function __construct(EntityManager $entityManager, TaskProject $project = null)
    {
        $this->entityManager = $entityManager;
        $this->project       = $project;
    }

    /**
     * Transform a Project Member into a team.
     *
     * @param mixed $memberObject
     */
    public function transform($memberObject)
    {
        if (!is_null($memberObject) && ($memberObject instanceof ProjectMember)) {
            return $memberObject->getTeam()->getId();
        }

        return;
    }

    /**
     * Transform a team entity into a Project Member.
     *
     * @param string $dept
     *
     * @return LabelTask|null|object
     */
    public function reverseTransform($team)
    {
        if (!$this->project) {
            return;
        }

        if (!$team instanceof AgentTeam) {
            $team = $this->entityManager->getRepository('DeskPRO:AgentTeam')
                ->find($team);
        }
        $member = $this->entityManager->getRepository('App:ProjectMember')
            ->findOneBy(array('team' => $team, 'project' => $this->project));

        if (!$member) {
            $member = new ProjectMember();
            $member->setTeam($team);
            $member->setProject($this->project);
        }

        return $team;
    }
}

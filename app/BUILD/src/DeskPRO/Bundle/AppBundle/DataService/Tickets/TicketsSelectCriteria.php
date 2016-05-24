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
namespace DeskPRO\Bundle\AppBundle\DataService\Tickets;

use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Model\TicketGrouping;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\AgentTeam\AgentTeamTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CustomData\CustomDataTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Department\DepartmentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Organization\OrganizationTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Person\PersonTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\PersonEmail\PersonEmailTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Problem\ProblemTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketDateCreated\TicketDateCreatedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketFlagged\TicketFlaggedTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLabel\TicketLabelTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketLanguage\TicketLanguageTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketStatus\TicketStatusTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketUrgency\TicketUrgencyTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\ORM\EntityRepository;

/**
 * TicketsSelectCriteria.
 *
 * This class is responsible for mapping of tickets search API GET parameters into Term used by the Term Engine. It
 * doesn't implement CriteriaInterface, because tickets search is a special case and happens via the Term Engine, not
 * via Doctrine.
 */
class TicketsSelectCriteria
{
    /**
     * @var EntityRepository
     */
    private $filterRepository;

    /**
     * TicketsSelectCriteria constructor.
     *
     * @param EntityRepository $filterRepository
     */
    public function __construct(EntityRepository $filterRepository)
    {
        $this->filterRepository = $filterRepository;
    }

    /**
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return CompositeTerm
     */
    public function createTerm(array $parameters)
    {
        $composite = new CompositeTerm([], TermInterface::OP_AND);

        foreach ($parameters as $param => $value) {
            if (TicketGrouping::isCustom($param, '_')) {
                $composite->addTerm(
                    new CustomDataTerm(
                        [
                            'field_id'          => TicketGrouping::getCustomFieldIdFromName($param, '_'),
                            'custom_data_value' => $value,
                        ]
                    )
                );
                continue;
            }

            switch ($param) {
                case 'agent':
                    $composite->addTerm(new AgentTerm(['agent_ids' => [$value]]));
                    break;
                case 'agent_team':
                    $composite->addTerm(new AgentTeamTerm(['agent_team_ids' => [$value]]));
                    break;
                case 'department':
                    $composite->addTerm(new DepartmentTerm(['department_ids' => [$value]]));
                    break;
                case 'email':
                    $composite->addTerm(new PersonEmailTerm(['email' => [$value]]));
                    break;
                case 'filter':
                    /** @var TicketFilter $filter */
                    if ($filter = $this->filterRepository->find($value)) {
                        $composite->addTerm($filter->getTerm());
                    }
                    break;
                case 'from':
                    $composite
                        ->addTerm(
                            new TicketDateCreatedTerm(
                                ['date' => new \DateTime(str_replace(' ', '+', $value))],
                                TermInterface::OP_GTE
                            )
                        );
                    break;
                case 'labels':
                    $composite->addTerm(new TicketLabelTerm(['label' => $value[0], TermInterface::OP_IS]));
                    break;
                case 'label':
                    $composite->addTerm(new TicketLabelTerm(['label' => $value[0], TermInterface::OP_IS]));
                    break;
                case 'language':
                    $composite->addTerm(new TicketLanguageTerm(['language' => $value[0], TermInterface::OP_IS]));
                    break;
                case 'organization':
                    $composite->addTerm(new OrganizationTerm(['organization' => $value]));
                    break;
                case 'person':
                    $composite->addTerm(new PersonTerm(['person_ids' => [$value]]));
                    break;
                case 'problem':
                    $composite->addTerm(new ProblemTerm(['problem' => $value]));
                    break;
                case 'star':
                    $composite->addTerm(new TicketFlaggedTerm(['flag' => TicketFlagged::$colorMap[$value]]));
                    break;
                case 'status':
                    $composite->addTerm(new TicketStatusTerm(['status' => $value]));
                    break;
                case 'not-status':
                    $composite->addTerm(new TicketStatusTerm(['status' => $value], TermInterface::OP_NOT));
                    break;
                case 'urgency':
                    $composite->addTerm(new TicketUrgencyTerm(['num' => [$value]]));
                    break;
                default:
                    throw new \Exception("Unknown ticket filtering option '$param'");
            }
        }

        return $composite;
    }
}

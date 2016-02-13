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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Problem;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\ProblemType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketProblemController.
 *
 * @ApiModes("all")
 * @Route("/ticket_problems")
 */
class TicketProblemsController extends CrudController
{
    public static $entity   = Problem::class;
    public static $type     = ProblemType::class;
    public static $listSort = 'created';

    /**
     * @ApiDoc(
     *      description="Get tickets associated with the given problem",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/{id}/tickets")
     */
    public function getTicketsAction(Request $request, $id)
    {
        return TicketsController::subRequestSearch($this->get('kernel'), $request, ['problem' => $id]);
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if (!is_null($is_open = $request->get('is_open'))) {
            $qb->andWhere("{$alias}.is_open = :is_open");
            $qb->setParameters(compact('is_open'));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        $problem = new Problem();
        $problem->setCreator($this->getUser());

        return $problem;
    }
}

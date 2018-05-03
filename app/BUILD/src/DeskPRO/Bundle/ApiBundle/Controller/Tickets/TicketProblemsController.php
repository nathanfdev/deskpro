<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Problem;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\ProblemType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketProblemController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_problems")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\Problem")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\ProblemType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Problem"
 *      }
 *     }
 * )
 */
class TicketProblemsController extends CrudController
{
    public static $entity   = Problem::class;
    public static $type     = ProblemType::class;
    public static $listSort = 'created';

    /**
     * @ApiDoc(
     *     section="Tickets",
     *     description="Get tickets associated with the given problem",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "dataType"="integer", "description"="problem id"}
     *     },
     *     statusCodes={
     *         200="Returned if success"
     *     },
     *     output="array<DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\Ticket>"
     * )
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     *
     * @Rest\Get("/{id}/tickets")
     */
    public function getTicketsAction(Request $request, $id)
    {
        return TicketsController::subRequestSearch($this->getKernel(), $request, ['problem' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $isOpen = $request->get('is_open');
        if (!is_null($isOpen)) {
            $qb->andWhere("{$alias}.is_open = :is_open");
            $qb->setParameter('is_open', $isOpen);
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

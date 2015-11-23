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
namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Labels\LabelsHelper;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonType;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PeopleController.
 *
 * @Route("/people")
 */
class PeopleController extends CrudController
{
    use LabelsHelper;

    public static $entity = Person::class;
    public static $type   = PersonType::class;

    /**
     * @ApiDoc(
     *      description="Get tickets of the given person",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/{id}/tickets")
     */
    public function getTicketsAction($id)
    {
        return TicketsController::subRequestSearch($this->get('kernel'), ['person' => $id]);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        if ($is_agent = $request->get('is_agent')) {
            $qb->andWhere("$alias.is_agent = :is_agent");
            $qb->setParameter('is_agent', (int) $is_agent);
        }

        if ($request->get('not_me')) {
            $user = $this->getUser();
            $qb->andWhere("$alias.id != :id");
            $qb->setParameter('id', $user->getId());
        }
    }
}

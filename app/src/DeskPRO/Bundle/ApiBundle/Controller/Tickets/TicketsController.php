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

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Labels\LabelsHelper;
use DeskPRO\Bundle\AppBundle\DataService\Tickets\TicketsSelectCriteria;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketType;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\DbalTermEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TicketsController.
 *
 * @Route("/tickets")
 */
class TicketsController extends CrudController
{
    use LabelsHelper;

    public static $entity = Ticket::class;
    public static $type   = TicketType::class;

    /**
     * @param HttpKernelInterface $kernel
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch($kernel, array $params)
    {
        $request = new Request();
        $request->attributes->set(
            '_controller',
            'ApiBundle:Tickets\Tickets:list'
        );
        $request->query->add($params);
        $response = $kernel->handle(
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        return $response;
    }

    /**
     * @ApiDoc(
     *      description="Get a list of tickets",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("", name="api_tickets")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        // if the "ids" param is provided, then just use it to select tickets
        if ($ids = $request->query->get('ids')) {
            $currentPage = $request->query->getInt('page', 1);
            $maxPerPage  = $request->query->getInt('count', min(count($ids), self::$listMaxResults));
            $ids         = !empty($ids) ? explode(',', $ids) : [];
            $total       = count($ids);
        }

        // otherwise search for IDs using term engine and return Pagerfanta instance
        else {
            $params = $request->query->all();
            if (array_key_exists('count', $params)) {
                unset($params['count']);
            }
            if (array_key_exists('page', $params)) {
                unset($params['page']);
            }
            $term = TicketsSelectCriteria::createTerm($params);

            /** @var DbalTermEngine $engine */
            $engine        = $this->get('term_engine.dbal.engine');
            $context       = new TermEngineContext($this->getUser());
            $currentPage   = $request->query->getInt('page', 1);
            $maxPerPage    = $request->query->getInt('count', self::$listPerPage);
            $tickets_query = $engine->evaluate($term, $context);
            $total         = $tickets_query->fetchCount();
            $tickets_query->setCount($maxPerPage);
            $tickets_query->setPage($currentPage);
            $ids = $tickets_query->fetchIds();
        }

        $tickets      = $this->selectTickets($ids);
        $pagerAdapter = new FixedAdapter($total, $tickets);
        $pager        = new Pagerfanta($pagerAdapter);
        $pager->setMaxPerPage($maxPerPage);
        $pager->setCurrentPage($currentPage);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @param array $ids
     *
     * @return Ticket[]
     */
    protected function selectTickets($ids = [])
    {
        $em  = $this->getManager();
        $ids = array_map(function ($id) { return (int) $id; }, $ids);

        $query = $em
            ->createQuery('SELECT t from DeskPRO:Ticket t WHERE t.id IN (?0)')
            ->setParameters([$ids]);

        return $query->getResult();
    }
}

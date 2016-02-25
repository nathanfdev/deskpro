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
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets\NewFilters;

use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\TicketsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\TicketsSettings;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TicketFiltersController.
 *
 * @ApiModes("all")
 */
class TicketFiltersController extends CrudController
{
    public static $entity = TicketFilter::class;

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(array_merge($params, $masterRequest->query->all()), null, [
            '_controller' => 'ApiBundle:Tickets\NewFilters\TicketFilters:list',
        ]);
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * @ApiDoc(
     *      description="Reorder filters.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Post("/ticket_filters/display_order")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postReorderAction(Request $request)
    {
        $data = $request->request->all();

        if (!is_array($data) || !isset($data['display_order'])) {
            throw new NotFoundHttpException();
        }

        $results = [];
        foreach ($data['display_order'] as $order => $filter_id) {
            $filter = $this->getRepository('App:TicketFilter')->find($filter_id);
            if (!$filter) {
                continue;
            }

            $filter->setDisplayOrder($order);
            $this->getManager()->persist($filter);
            $results[$order] = $filter_id;
        }

        $this->getManager()->flush();

        return View::create($this->dataSerialize($results));
    }

//    /**
//     * @Put("/ticket_filters/{id}", name="put_ticket_filters")
//     *
//     * @ApiDoc(
//     *      description="Modify filter grouping",
//     *      requirements={
//     *          {
//     *              "name"="id",
//     *              "requirement"="\d+",
//     *              "description"="the id of the filter",
//     *              "dataType"="integer"
//     *          },
//     *          {
//     *              "name"="group_by",
//     *              "description"="new filter grouping",
//     *              "dataType"="string"
//     *          }
//     *      },
//     *      statusCodes={
//     *          204="Updated",
//     *          404="Not Found",
//     *          400="Bad Request"
//     *      }
//     * )
//     *
//     * @Put("/ticket_filters/{id}")
//     *
//     * @param Request      $request
//     * @param TicketFilter $filter
//     *
//     * @return View
//     */
//    public function putAction(Request $request, TicketFilter $filter)
//    {
//        $content = json_decode($request->getContent(), true);
//
//        // Remove existing setting if got no or empty group_by
//        if (!array_key_exists('group_by', $content) || !$content['group_by']) {
//            $this->removeFilterGroupByPersonSetting($filter);
//
//            return new Response(null, Response::HTTP_NO_CONTENT);
//        }
//
//        // Save the group_by in filter setting
//        $setting = $this->findOrCreateFilterGroupByPersonSetting($filter);
//        $setting->setValue($content['group_by']);
//        $this->getManager()->persist($setting);
//        $this->getManager()->flush();
//
//        return new Response(null, Response::HTTP_NO_CONTENT);
//    }

    /**
     * @ApiDoc(
     *      description="Get filter's tickets. See /tickets endpoint docs for the parameter details.",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/new/ticket_filters/{id}/tickets")
     *
     * @param Request      $request
     * @param TicketFilter $filter
     *
     * @return Response
     */
    public function getFilterTicketsAction(Request $request, TicketFilter $filter)
    {
        return TicketsController::subRequestSearch($this->get('kernel'), $request, [
            'filter' => $filter->getId(),
        ]);
    }

    /**
     * @param TicketFilter $filter
     *
     * @return PersonSetting
     */
    private function findOrCreateFilterGroupByPersonSetting(TicketFilter $filter)
    {
        $settingName = TicketsSettings::FILTER_GROUPING_PREFIX.$filter->getId();
        $person      = $this->getUser();

        $person_setting = $this->getManager()->find(PersonSetting::class, [
            'person' => $person,
            'name'   => $settingName,
        ]);

        if (!$person_setting) {
            $person_setting = new PersonSetting($person, $settingName);
        }

        return $person_setting;
    }

    /**
     * @param TicketFilter $filter
     */
    private function removeFilterGroupByPersonSetting(TicketFilter $filter)
    {
        $settingName = TicketsSettings::FILTER_GROUPING_PREFIX.$filter->getId();
        $person      = $this->getUser();

        $person_setting = $this->getManager()->find(PersonSetting::class, [
            'person' => $person,
            'name'   => $settingName,
        ]);

        if ($person_setting) {
            $this->getManager()->remove($person_setting);
            $this->getManager()->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $filter_set = $request->query->getInt('filter_set');
        if ($filter_set) {
            $qb
                ->andWhere('e.filter_set = :filter_set')
                ->setParameter('filter_set', $filter_set)
            ;
        }
    }
}

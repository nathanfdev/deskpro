<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Application\DeskPRO\Entity\Job;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class JobsController.
 *
 * @ApiDoc(target="all", section="Jobs", output="Application\DeskPRO\Entity\Job")
 *
 * @ApiModes("all")
 * @Rest\Route("/jobs")
 * @ApiUserContext("admin")
 */
class JobsController extends CrudController
{
    public static $entity = Job::class;

    public static $exposeOnly = ['list', 'get'];

    /**
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("")
     *
     * @Rest\View(serializerGroups={"DEFAULT"})
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        return parent::listAction($request);
    }

    /**
     * @ApiDoc(
     *      description="Get a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}", requirements={"id"="\d+"})
     *
     * @Rest\View(serializerGroups={"DEFAULT", "details"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        return parent::getAction($request, $id);
    }
}

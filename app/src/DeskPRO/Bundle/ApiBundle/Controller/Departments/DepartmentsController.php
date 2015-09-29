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
namespace DeskPRO\Bundle\ApiBundle\Controller\Departments;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DepartmentsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="get a list of departments",
     *      parameters={
     *          {
     *              "name"="page",
     *              "requirement"="\d+",
     *              "description"="the page you are requesting",
     *              "dataType"="integer",
     *              "required"=false
     *          },
     *          {
     *              "name"="count",
     *              "requirement"="\d+",
     *              "description"="results per page",
     *              "dataType"="integer",
     *              "required"=false
     *          }
     *      },
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     *
     * @Get("/departments", name="api_departments")
     *
     * @return View
     */
    public function cgetAction()
    {
        $repo = $this->getEm()->getRepository('DeskPRO:Department');

        return View::create(
            $this->DataSerialize($repo->findAll()),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a department",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the department",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="Application\DeskPRO\Entity\Department"
     * )
     *
     * @Get("/departments/{id}", name="api_departments_get")
     *
     *
     * @param int $id
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function getAction($id)
    {
        $repo     = $this->getEm()->getRepository('DeskPRO:Department');
        $findings = $repo->findBy(['id' => $id]);

        if (!$findings || count($findings) < 1) {
            throw $this->createNotFoundException();
        }
        $department = $findings[0];

        return View::create(
            $this->DataSerialize($department),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get agents belongs to department",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the department",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="Application\DeskPRO\Entity\Department"
     * )
     * @Get("/departments/{id}/agents", name="api_departments_get_agents")
     *
     *
     * @param int $id
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function getAgentsAction($id)
    {
        $repo     = $this->getEm()->getRepository('DeskPRO:Department');
        $findings = $repo->findBy(['id' => $id]);

        if (!$findings || count($findings) < 1) {
            throw $this->createNotFoundException();
        }
        /** @var Department $department */
        $department = $findings[0];

        return View::create(
            $this->DataSerialize($department->getPersonList()),
            Response::HTTP_OK
        );
    }

    // A bit of comfort.
    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}

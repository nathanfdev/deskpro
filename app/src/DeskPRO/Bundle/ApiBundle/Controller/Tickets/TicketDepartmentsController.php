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

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\DataService\DepartmentDataService;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TicketDepartmentsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="Get departments list",
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
     * @Get("/ticket_departments", name="api_departments")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        if ($request->query->getBoolean('my', false)) {
            /** @var DepartmentDataService $departments_data_service */
            $departments_data_service = $this->get('data.departments');
            $departments              = $departments_data_service->getChatDepartmentsForPerson($this->getUser());
        } else {
            $query = $request->query->all();

            if (!empty($query['ids'])) {
                $departments = $this->selectDepartments(explode(',', $query['ids']));
            } else {
                $departments = $this->getDoctrine()->getManager()->createQueryBuilder()
                    ->select('d')->from('DeskPRO:Department', 'd')->getQuery();
            }

            $departments = $departments->getResult();
        }
        /* @var Department[] $departments */

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new ArrayAdapter($departments));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get a department",
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
     * @Get("/ticket_departments/{id}", name="api_departments_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $department = $this->getDepartment($id);

        if (empty($department)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($department),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Get agents belongs to department",
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
     * @Get("/ticket_departments/{id}/agents", name="api_departments_get_agents")
     *
     * @param int $id
     *
     * @throws NotFoundHttpException
     *
     * @return View
     */
    public function getAgentsAction($id)
    {
        $department = $this->findOr404(Department::class, $id);

        return View::create(
            $this->dataSerialize($department->getPersonList()),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="Create a new department",
     *      input={"class"="department", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="Application\DeskPRO\Entity\Department"
     * )
     * @Post("/ticket_departments", name="api_departments_post")
     *
     * @param Request $request
     *
     * @throws WrappedApiErrorException
     * @throws InvalidFormException
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $department = new Department($this->getUser());

        return $this->handleFormSubmission($request, $department);
    }

    /**
     * @APIDoc(
     *      description="Update a department",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the department",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="department", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/ticket_departments/{id}", name="api_departments_put")
     *
     * @param Request $request
     * @param $id
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    public function putAction(Request $request, $id)
    {
        $department = $this->getDepartment($id);

        return $this->handleFormSubmission($request, $department);
    }

    /**
     * @APIDoc(
     *      description="Delete a department",
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
     *      }
     * )
     * @Delete("/ticket_departments/{id}", name="api_departments_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $department = $this->getDepartment($id);
        $this->getDoctrine()->getManager()->remove($department);
        $this->getDoctrine()->getManager()->flush();

        return View::create(
            array(),
            Response::HTTP_OK
        );
    }

    /**
     * @param int $id
     *
     * @return Department
     */
    protected function getDepartment($id)
    {
        $id         = (int) $id;
        $department = $this->getDoctrine()->getManager()->getRepository('DeskPRO:Department')->find($id);

        if (!$department) {
            throw $this->createNotFoundException();
        }

        return $department;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request    $request
     * @param Department $department
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, Department $department)
    {
        $status = $department->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'department', $department)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($department);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_departments_get', array('id' => $department->getId()));

            return View::create(
                $this->dataSerialize($department),
                $status,
                array(
                    'Location' => $location,
                )
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Get specific departments.
     *
     * @param $departmentIds
     *
     * @return mixed
     */
    protected function selectDepartments($departmentIds)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $departmentIds = array_map(function ($value) {
            return (int) $value;
        }, $departmentIds);

        $query = $entityManager->createQueryBuilder()->select('d')->from('DeskPRO:Department', 'd')
            ->where('d.id IN (:departmentIds)')
            ->setParameter('departmentIds', $departmentIds);

        return $query->getQuery();
    }
}

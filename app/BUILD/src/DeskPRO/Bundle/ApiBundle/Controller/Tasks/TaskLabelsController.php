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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tasks;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskLabelsController.
 *
 * @ApiModes("all")
 */
class TaskLabelsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of task_labels",
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
     * @Get("/task_labels", name="api_task_labels")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $task_labels = $this->getDoctrine()->getManager()->createQueryBuilder()->select('l')->from('App:LabelTask', 'l')
            ->orderBy('l.label', 'ASC');

        $page  = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);
        $group = $request->query->get('group', false);

        if (!empty($group)) {
            $task_labels = $task_labels->groupBy('l.label');
        }

        $pager = new Pagerfanta(new DoctrineORMAdapter($task_labels));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="get a label",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the label",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\LabelTask"
     * )
     * @Get("/task_labels/{id}", name="api_task_labels_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $label = $this->getLabel($id);

        if (empty($label)) {
            throw $this->createNotFoundException();
        }

        return View::create(
            $this->dataSerialize($label),
            Response::HTTP_OK
        );
    }

    /**
     * @ApiDoc(
     *      description="create a new label",
     *      input={"class"="task_label", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\LabelTask"
     * )
     * @Post("/task_labels", name="api_task_labels_post")
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
        $label = new LabelTask($this->getUser());

        return $this->handleFormSubmission($request, $label);
    }

    /**
     * @APIDoc(
     *      description="update a label",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the label",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="task_label", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/task_labels/{id}", name="api_task_labels_put")
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
        $label = $this->getLabel($id);

        return $this->handleFormSubmission($request, $label);
    }

    /**
     * @APIDoc(
     *      description="delete a label",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the task",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Delete("/task_labels/{id}", name="api_task_labels_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $label = $this->getLabel($id);
        $this->getDoctrine()->getManager()->remove($label);
        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @param int $id
     *
     * @return LabelTask
     */
    protected function getLabel($id)
    {
        $id    = (int) $id;
        $label = $this->getDoctrine()->getManager()->getRepository('App:LabelTask')->find($id);

        if (!$label) {
            throw $this->createNotFoundException();
        }

        return $label;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request   $request
     * @param LabelTask $label
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, LabelTask $label)
    {
        $status = $label->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task_label', $label)->getForm();

        $submitted = $request->request->all();

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($label);
            $this->getDoctrine()->getManager()->flush();

            $location = $this->generateUrl('api_task_labels_get', ['id' => $label->getId()]);

            return View::create(
                $this->dataSerialize($label),
                $status,
                [
                    'Location' => $location,
                ]
            );
        }

        throw new InvalidFormException($form);
    }
}

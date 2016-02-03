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

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Exception\WrappedApiErrorException;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiTags;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use Doctrine\DBAL\DBALException;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TaskLinkedItemsController.
 *
 * @ApiModes("all")
 * @ApiTags("agent.tasks.task_linked_items")
 */
class TaskLinkedItemsController extends BaseController implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *      description="get a list of links",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get("/task_links", name="api_task_links")
     *
     * @param Request $request
     *
     * @return View
     */
    public function cgetAction(Request $request)
    {
        $query = $request->query->all();

        if (!empty($query['ids'])) {
            $taskLinks = $this->selectLinks(explode(',', $query['ids']));
            $taskLinks = $taskLinks->getResult();
        } else {
            $taskLinks = $this->getDoctrine()->getManager()->getRepository('App:TaskLinkedItem')->findAll();
        }

        return View::create($this->dataSerialize($taskLinks), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="get a link",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the link",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem"
     * )
     * @Get("/task_links/{id}", name="api_task_links_get")
     *
     * @param int $id
     *
     * @return View
     */
    public function getAction($id)
    {
        $link = $this->getLink($id);
        if (empty($link)) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($link), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="create a new link",
     *      input={"class"="task_link", "name"=""},
     *      statusCodes={
     *          201="Created",
     *          400="Bad Request"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem"
     * )
     * @Post("/task_links", name="api_task_links_post")
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
        $link = new TaskLinkedItem($this->getUser());

        return $this->handleFormSubmission($request, $link);
    }

    /**
     * @APIDoc(
     *      description="update a link",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="the id of the link",
     *              "dataType"="integer"
     *          }
     *      },
     *      input={"class"="task_link", "name"=""},
     *      statusCodes={
     *          204="Updated",
     *          400="Bad Request",
     *          404="Not Found"
     *      }
     * )
     * @Put("/task_links/{id}", name="api_task_links_put")
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
        $link = $this->getLink($id);

        return $this->handleFormSubmission($request, $link);
    }

    /**
     * @APIDoc(
     *      description="delete a link",
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
     * @Delete("/task_links/{id}", name="api_task_links_delete")
     *
     * @param $id
     *
     * @return View
     */
    public function deleteAction($id)
    {
        $link = $this->getLink($id);
        $this->getDoctrine()->getManager()->remove($link);
        $this->getDoctrine()->getManager()->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @param int $id
     *
     * @return TaskLinkedItem
     */
    protected function getLink($id)
    {
        $id   = (int) $id;
        $link = $this->getDoctrine()->getManager()->getRepository('App:TaskLinkedItem')->find($id);

        if (!$link) {
            throw $this->createNotFoundException();
        }

        return $link;
    }

    /**
     * Will be abstracted for use by other controllers.
     *
     * @param Request        $request
     * @param TaskLinkedItem $link
     *
     * @throws WrappedApiErrorException
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, TaskLinkedItem $link)
    {
        $status = $link->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task_link', $link)->getForm();

        $submitted = $request->request->all();
        $submitted = $this->cleanLinkTypes($submitted);

        $form->submit($submitted, $request->getMethod() !== 'PUT');

        if ($form->isValid()) {
            // Try and gracefully handle integrity constraint failures
            try {
                $this->getDoctrine()->getManager()->persist($link);
                $this->getDoctrine()->getManager()->flush();
            } catch (DBALException $e) {
                throw new InvalidFormException($form);
            }

            $location = $this->generateUrl('api_task_links_get', ['id' => $link->getId()]);

            return View::create(
                $this->dataSerialize($link),
                $status,
                [
                    'Location' => $location,
                ]
            );
        }

        throw new InvalidFormException($form);
    }

    /**
     * Cleans up the submitted array so that we only have one ticket, article or chat.
     *
     * @param array $submitted
     *
     * @return array
     */
    private function cleanLinkTypes(array $submitted)
    {
        $types   = ['article', 'ticket', 'chat'];
        $cleaned = false;

        foreach ($types as $type) {
            if (false === $cleaned && !empty($submitted[$type])) {
                $cleaned = true;
            } else {
                $submitted[$type] = '';
            }
        }

        return $submitted;
    }

    /**
     * Get a Doctrine Query for getting certain links.
     *
     * @param $linkIds
     *
     * @return \Doctrine\ORM\Query
     */
    protected function selectLinks($linkIds)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $linkIds = array_map(function ($value) {
            return (int) $value;
        }, $linkIds);

        $query = $entityManager->createQueryBuilder()->select('l')->from('App:TaskLinkedItem', 'l')
            ->where('l.id IN (:linkIds)')
            ->setParameter('linkIds', $linkIds);

        return $query->getQuery();
    }
}

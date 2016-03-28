<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedItem;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use Doctrine\DBAL\DBALException;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class TaskLinkedItemsController.
 *
 * @ApiModes("all")
 */
class TaskLinkedItemsController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Tasks",
     *     description="get a list of links by the type",
     *     requirements={
     *         {"name"="type", "requirement"="\w", "dataType"="string", "description"="link type"},
     *     },
     *     filters={
     *         {"name"="ids", "pattern"="(\d+,)+", "dataType"="string", "description"="filter with given comma separated ids list"},
     *     },
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="You provided wrong type or not provided it at all"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedItem"
     * )
     * @Annotations\Get("/task_links", name="api_task_links")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $query = $request->query->all();
        if (!array_key_exists('type', $query)) {
            throw new BadRequestHttpException('You must specify "type" parameter');
        }
        $type = 'App:TaskLinkedItem\TaskLinked'.ucfirst($query['type']);
        if (!empty($query['ids'])) {
            $taskLinks = $this->selectLinks(explode(',', $query['ids']), $type);
            $taskLinks = $taskLinks->getResult();
        } else {
            $taskLinks = $this->getDoctrine()->getManager()->getRepository($type)->findAll();
        }

        return View::create($this->wrap($taskLinks), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *     section="Tasks",
     *     description="get a link",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the link", "dataType"="integer"},
     *     },
     *     statusCodes={
     *         200="Everything is OK",
     *         404="We can find linked item you ask"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedItem"
     * )
     * @Annotations\Get("/task_links/{id}", name="api_task_links_get")
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

        return View::create($this->wrap($link), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *     section="Tasks",
     *     description="create a new link",
     *     input={"class"="task_link", "name"=""},
     *     statusCodes={
     *         201="We have created new link for you",
     *         400="Request malformed have you, possibly type forgot you provide to"
     *     },
     *     output="DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinkedItem"
     * )
     * @Annotations\Post("/task_links/{type}", name="api_task_links_post")
     *
     * @param Request $request
     * @param string  $type
     *
     * @return View
     */
    public function postAction(Request $request, $type)
    {
        if (!$type) {
            throw new BadRequestHttpException('You must specify "type" parameter');
        }
        $class = 'DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem\TaskLinked'.ucfirst($type);
        echo $class;
        $link = new $class();

        return $this->handleFormSubmission($request, $link, $type);
    }

    /**
     * @ApiDoc(
     *     section="Tasks",
     *     description="update a link",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the link", "dataType"="integer"}
     *     },
     *     input={"class"="task_link", "name"=""},
     *     statusCodes={
     *         204="We have updated task link successfully",
     *         400="Wrong request parameters",
     *         404="This is not the task link you are looking for"
     *     }
     * )
     * @Annotations\Put("/task_links/{type}/{id}", name="api_task_links_put")
     *
     * @param Request $request
     * @param         $id
     * @param string  $type
     *
     * @return View
     */
    public function putAction(Request $request, $id, $type)
    {
        $link = $this->getLink($id);

        return $this->handleFormSubmission($request, $link, $type);
    }

    /**
     * @ApiDoc(
     *     section="Tasks",
     *     description="delete a link",
     *     requirements={
     *         {"name"="id", "requirement"="\d+", "description"="the id of the task", "dataType"="integer"}
     *     },
     *     statusCodes={
     *         200="The task link was destroyed",
     *         404="This is not the task link you are looking for"
     *     }
     * )
     * @Annotations\Delete("/task_links/{id}", name="api_task_links_delete")
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
        $link = $this->getDoctrine()->getManager()->getRepository('App:TaskLinkedItem\TaskLinkedItem')->find($id);

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
     * @param string         $type
     *
     * @return View
     */
    protected function handleFormSubmission(Request $request, TaskLinkedItem $link, $type)
    {
        $status = $link->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamedBuilder(null, 'task_link_'.$type, $link)->getForm();

        $submitted = $request->request->all();
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

            return View::create($this->wrap($link), $status, ['Location' => $location]);
        }
        throw new InvalidFormException($form);
    }

    /**
     * Get a Doctrine Query for getting certain links.
     *
     * @param array  $linkIds
     * @param string $type
     *
     * @return \Doctrine\ORM\Query
     */
    protected function selectLinks(array $linkIds, $type)
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Clean the IDs
        $linkIds = array_map(
            function ($value) {
                return (int) $value;
            },
            $linkIds
        );

        $query = $entityManager->createQueryBuilder()->select('l')->from($type, 'l')
            ->where('l.id IN (:linkIds)')
            ->setParameter('linkIds', $linkIds);

        return $query->getQuery();
    }
}

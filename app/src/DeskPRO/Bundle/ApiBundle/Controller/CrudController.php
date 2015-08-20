<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;

/**
 * Class CrudController
 *
 * Base REST CRUD controller
 *
 * @todo After fixing Nelmio API doc generator, check how it handles @ApiDoc annotations in parent classes
 * @todo Write general actions docs
 * @todo Location header
 * @todo More user friendly validation errors output
 * @todo Allow partial updates (?)
 */
class CrudController extends BaseController
{
    static $entity;
    static $type;
    static $listSort = 'id';
    static $listOrder = 'desc';

    /**
     * @ApiDoc(
     *      description="Get an entity",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the entity",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          404="Not Found"
     *      }
     * )
     * @Get("/{id}", requirements={"id"="\d+"})
     */
    public function getAction($id)
    {
        $entity = $this->getDoctrine()->getRepository(static::$entity)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($entity), Response::HTTP_OK);
    }

    /**
     * @Get("")
     */
    public function listAction(Request $request)
    {
        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('e')
            ->from(static::$entity, 'e')
            ->orderBy('e.' . static::$listSort, static::$listOrder);

        $page = $request->query->get('page', 1);
        $count = $request->query->get('count', 10);

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($count);
        $pager->setCurrentPage($page);

        return View::create(
            $this->dataSerialize($pager),
            Response::HTTP_OK
        );
    }

    /**
     * @Post("")
     */
    public function postAction(Request $request)
    {
        return $this->handleForm(new static::$entity, $request);
    }

    /**
     * @Put("/{id}", requirements={"id"="\d+"})
     */
    public function putAction($id, Request $request)
    {
        return $this->handleForm($this->findOr404(static::$entity, $id), $request);
    }

    /**
     * @Delete("/{id}", requirements={"id"="\d+"})
     */
    public function deleteAction($id)
    {
        $entity = $this->findOr404(static::$entity, $id);

        $em = $this->getManager();
        $em->remove($entity);
        $em->flush();

        return View::create([], Response::HTTP_OK);
    }

    /**
     * @throws InvalidFormException
     * @param object $model
     * @param Request $request
     * @return View
     */
    private function handleForm($model, Request $request)
    {
        $status = $model->getId() ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm(new static::$type, $model);
        $form->submit(
            json_decode($request->getContent()),
            true
        );

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($model);
            $em->flush();

            return View::create($this->dataSerialize($model), $status);
        }

        throw new InvalidFormException($form);
    }
}

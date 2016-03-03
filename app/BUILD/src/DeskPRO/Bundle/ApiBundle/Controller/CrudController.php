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
namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CrudController.
 *
 * Base REST CRUD controller
 *
 * @todo Location header
 */
abstract class CrudController extends BaseController
{
    public static $entity;
    public static $type;

    /**
     * @var array|null Array of exposed action names e.g. ['get', 'list'], if not defined all are exposed
     */
    public static $exposeOnly = null;

    /**
     * @var array|null Map of sortable entity fields: [request_param_name => entity_filed_name]
     */
    public static $sortOptions = null;

    public static $listSort       = 'id';
    public static $listOrder      = 'desc';
    public static $listPaginate   = true;
    public static $listPerPage    = 10;
    public static $listMaxResults = 200;

    /**
     * @ApiDoc(
     *      description="Get a resource",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          403="Denied",
     *          404="Not Found"
     *      }
     * )
     * @Get("/{id}", requirements={"id"="\d+"})
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        $this->checkExposed(__METHOD__);

        if (!$entity = $this->findEntity($id, $request)) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($entity), Response::HTTP_OK);
    }

    /**
     * Entities list.
     *
     * Selects entities based on the provided "ids" parameter or returns paginated list of no IDs provided
     *
     * @ApiDoc(
     *      description="Get collection of resources",
     *      requirements={
     *          {
     *              "name"="ids",
     *              "requirement"="[\d,]+",
     *              "description"="(Optional) Comma separated list of IDs",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          403="Denied",
     *          404="Not Found"
     *      }
     * )
     * @Get("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->checkExposed(__METHOD__);

        /** @var \Doctrine\ORM\QueryBuilder $qb */
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('e')
            ->from(static::$entity, 'e')
        ;

        $this->applyListFilters($qb, 'e', $request);
        $this->applySorting($qb, 'e', $request);

        $ids = $request->get('ids');
        if ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_map(function ($id) { return (int) $id; }, $ids);
            if (count($ids) > static::$listMaxResults) {
                throw $this->createBadRequestException('You can select maximum '.static::$listMaxResults.' entities');
            }

            $qb
                ->andWhere('e.id IN (:ids)')
                ->setParameters(compact('ids'))
            ;
        }

        // return QueryBuilder result or Pagerfanta depending on if pagination is enabled for the controller
        if (static::$listPaginate) {
            $page  = $request->query->get('page', 1);
            $count = $request->query->get('count', static::$listPerPage);
            if ($count > static::$listMaxResults) {
                throw $this->createBadRequestException(
                    'You can select maximum '.static::$listMaxResults.' entities');
            }
            $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
            $pager->setMaxPerPage($count);
            $pager->setCurrentPage($page);
            $result = $pager;
        } else {
            $result = $qb->getQuery()->getResult();
        }

        return View::create($this->dataSerialize($result), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="Create a new resource",
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          403="Denied"
     *      }
     * )
     * @Post("")
     */
    public function postAction(Request $request)
    {
        $this->checkExposed(__METHOD__);

        return $this->handleForm($this->instantiateEntity($request), $request);
    }

    /**
     * @ApiDoc(
     *      description="Update an existing resource",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          403="Denied"
     *      }
     * )
     * @Put("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        $this->checkExposed(__METHOD__);

        return $this->handleForm($entity = $this->findEntity($id, $request), $request);
    }

    /**
     * @ApiDoc(
     *      description="Delete a resource",
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          400="Bad Request",
     *          403="Denied"
     *      }
     * )
     * @Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function deleteAction($id, Request $request)
    {
        $this->checkExposed(__METHOD__);

        $entity = $this->findEntity($id, $request);
        $this->deleteEntity($entity);

        return View::create([], Response::HTTP_OK);
    }

    /**
     * Applies list action filters to a QueryBuilder instance.
     *
     * Does nothing by default, may be redefined in children to implement list filtering.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
    }

    /**
     * Apply 'sort' and 'order' depending on static::$sortOptions.
     *
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param Request      $request
     */
    protected function applySorting(QueryBuilder $qb, $alias, Request $request)
    {
        $sort  = static::$listSort;
        $order = static::$listOrder;

        if (is_array(static::$sortOptions)) {
            $sortParam = strtolower($request->get('sort'));
            if ($sortParam && !array_key_exists($sortParam, static::$sortOptions)) {
                throw $this->createBadRequestException('Unknown sort field');
            }
            $sort = isset(static::$sortOptions[$sortParam])
                  ? static::$sortOptions[$sortParam]
                  : static::$listSort;

            $order = strtolower($request->get('order'));
            if ($order && !in_array($order, ['asc', 'desc'])) {
                throw $this->createBadRequestException('Unknown order value');
            }
        }

        $qb->orderBy($alias.'.'.$sort, $order);
    }

    /**
     * @param Request $request
     *
     * @return object
     */
    protected function instantiateEntity(Request $request)
    {
        return new static::$entity();
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return object
     */
    protected function findEntity($id, Request $request)
    {
        return $this->findOr404(static::$entity, $id);
    }

    /**
     * Persist model.
     *
     * This method is called on a valid entity to persist it. May be overwritten in child controllers.
     *
     * @param object $model
     *
     * @return object The passed model
     */
    protected function persistModel($model)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($model);
        $em->flush();

        return $model;
    }

    /**
     * @param object $entity
     */
    protected function deleteEntity($entity)
    {
        $em = $this->getManager();
        $em->remove($entity);
        $em->flush();
    }

    /**
     * @param object  $model
     * @param Request $request
     * @param array   $options
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $partial_update = $model && $model->getId();
        $status         = $partial_update ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        /** @var \Symfony\Component\Form\Form $form */
        $form = $this->createForm(
            class_exists(static::$type) ? new static::$type() : static::$type,
            $model,
            $options
        );

        $decoded = json_decode(
            $request->getContent(),
            true // convert to assoc arrays instead of stdClass instances
        );

        // we use POST request for creating and updating entities (including partial updates)
        // so $clearMissing should depends on $model id (switch for POST and PATCH request)

        // we can't always use $clearMissing = false (for partial updates) because of:
        // https://github.com/symfony/symfony/pull/10567
        // https://github.com/symfony/symfony/issues/11493

        // in this case form ViolationMapper should applies entity validation errors on the submitted form

        $form->submit($decoded, !$partial_update);
        if ($form->isValid()) {
            return View::create($this->dataSerialize($this->persistModel($model)), $status);
        }

        throw new InvalidFormException($form);
    }

    /**
     * @param string $actionMethodName
     */
    private function checkExposed($actionMethodName)
    {
        // return of $exposeOnly config is not used
        if (!is_array(static::$exposeOnly)) {
            return;
        }

        // remove class name if __METHOD__ was passed
        if (strpos($actionMethodName, '::')) {
            $actionMethodName = explode('::', $actionMethodName)[1];
        }

        // remove 'Action' postfix to get the short action name in case if __METHOD__ or __FUNCTION__ is passed
        $action = strpos($actionMethodName, 'Action') === strlen($actionMethodName) - strlen('Action')
                ? substr($actionMethodName, 0, strlen($actionMethodName) - strlen('Action'))
                : $actionMethodName;

        if (!in_array($action, static::$exposeOnly)) {
            throw $this->createAccessDeniedException('Action is restricted');
        }
    }
}

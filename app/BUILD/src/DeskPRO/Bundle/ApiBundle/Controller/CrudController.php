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

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

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

    // this used only for moving period
    public static $serializeMethod = 'dataSerialize';

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
     * Get resource with provided id.
     *
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

        return View::create($this->wrap($entity), Response::HTTP_OK);
    }

    /**
     * Entities list.
     *
     * Selects entities based on the provided "ids" parameter or returns paginated list of no IDs provided.
     * Look carefully at filters section to have a great filtering, grouping or sorting power
     *
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
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

        return View::create($this->wrap($result), Response::HTTP_OK);
    }

    /**
     * You can create new resource. Just provide well formed request.
     * Look into requirements for details.
     *
     * **We will ship resource representation as soon as it will be created.**
     *
     * @ApiDoc(
     *      description="Create a new resource",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->checkExposed(__METHOD__);

        return $this->handleForm($this->instantiateEntity($request), $request);
    }

    /**
     * Update the resource with specified ID.
     * Look carefully in requirements section to form request well.
     *
     * @ApiDoc(
     *      description="Update an existing resource",
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
     *          204="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
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

        return $this->handleForm($this->findEntity($id, $request), $request);
    }

    /**
     * Obviously it's an ability to erase what you've done.
     * Be careful there is no CTRL+Z shortcut.
     *
     * @ApiDoc(
     *      description="Delete a resource",
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
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
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
            $sortParam = strtolower($request->get('order_by'));
            if ($sortParam && !array_key_exists($sortParam, static::$sortOptions)) {
                throw $this->createBadRequestException('Unknown sort field');
            }
            $sort = isset(static::$sortOptions[$sortParam])
                  ? static::$sortOptions[$sortParam]
                  : static::$listSort;

            $order = strtolower($request->get('order_dir'));
            if ($order && !in_array($order, ['asc', 'desc'])) {
                throw $this->createBadRequestException('Unknown order value');
            }
        }

        $qb->orderBy($alias.'.'.$sort, $order);
        $qb->orderBy($alias.'.id', $order);
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

        $form    = $this->createForm(static::$type, $model, $options);
        $decoded = $this->getRequestContent($request);

        // we use POST request for creating and updating entities (including partial updates)
        // so $clearMissing should depends on $model id (switch for POST and PATCH request)

        // we can't always use $clearMissing = false (for partial updates) because of:
        // https://github.com/symfony/symfony/pull/10567
        // https://github.com/symfony/symfony/issues/11493

        // in this case form ViolationMapper should applies entity validation errors on the submitted form

        $form->submit($decoded, !$partial_update);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        return View::create($this->wrap($this->persistModel($model)), $status);
    }

    /**
     * It's useful for replacing content.
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function getRequestContent(Request $request)
    {
        return json_decode(
            $request->getContent(),
            true // convert to assoc arrays instead of stdClass instances
        );
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

        $action = TypeUtils::cleanAction($actionMethodName);

        if (!in_array($action, static::$exposeOnly)) {
            throw new MethodNotAllowedHttpException(static::$exposeOnly, sprintf('Action [ %s ] is not allowed', strtoupper($action)));
        }
    }
}

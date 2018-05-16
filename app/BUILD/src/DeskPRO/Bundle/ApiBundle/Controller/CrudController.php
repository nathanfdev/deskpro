<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Doctrine\RequestHelper\DateHelper;
use DeskPRO\Bundle\AppBundle\CountBadge\AbstractCount;
use DeskPRO\Bundle\AppBundle\CountBadge\Count;
use DeskPRO\Bundle\AppBundle\CountBadge\CountMap;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\Serializer\OffsetList;
use DeskPRO\Component\Pagerfanta\LimitedPager;
use DeskPRO\Component\Util\ControllerUtils;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Class CrudController.
 *
 * Base REST CRUD controller
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
     * @var array|null Map of sortable entity fields: [request_param_name => entity_filed_spec]. You can configure
     *                 sorting by a relational field if provide ['join' => 'agent', 'as' => 'a', 'sort' => 'a.id']
     *                 as a sorting option spec
     */
    public static $sortOptions = ['id' => 'id'];

    public static $listSort       = 'id';
    public static $listOrder      = 'desc';
    public static $listPaginate   = true;
    public static $listPerPage    = 10;
    public static $listMaxResults = 200;
    public static $listLimit      = 0;

    /**
     * Enable the option to force partial updates for POST requests (i.e. for new entities).
     *
     * @var bool
     */
    public static $forcePartialUpdate = false;

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
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAction(Request $request, $id)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, $this->getPermissionGroupEntityContext($id, $request));

        return View::create($this->wrap($this->findEntity($id, $request)), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *      description="Count list",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *         200="Returned if successful request",
     *         400="Returned if you filter set was malformed"
     *      },
     *      output="DeskPRO\Bundle\AppBundle\CountBadge\Count"
     * )
     *
     * @Rest\Get("/counts")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function countAction(Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $qb = $this->getManager()->createQueryBuilder();
        $qb->from(static::$entity, 'e');

        $this->applyListFilters($qb, 'e', $request);
        // reset group by if it was set in applyListFilters()
        $qb->resetDQLPart('groupBy');

        $totalCount = $qb->select('count(distinct e.id) as value')->getQuery()->getSingleScalarResult();
        $groupBy    = $request->get('group_by');

        if (!$groupBy) {
            $count = Count::fromValue($totalCount);
        } else {
            $this->applyListGroupBy($qb, 'e', $groupBy, $request);
            if (!$qb->getDQLPart('groupBy')) {
                throw $this->createBadRequestException('Unknown group_by option');
            }

            $result = $qb->getQuery()->getArrayResult();

            if ($request->query->getBoolean('index_group_by', false)) {
                $count = CountMap::fromGroupedBy($groupBy);
            } else {
                $count = Count::fromGroupedBy($groupBy);
            }

            $this->addGroupByNestedCounts($count, $result);
            $count->setCount($totalCount);
        }

        return new View($this->wrap($count));
    }

    /**
     * @ApiDoc(
     *      description="Get collection of resources",
     *      tags={"CRUD"="#ffa500"},
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      }
     * )
     * @Rest\Get("")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function listAction(Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW_LIST, $this->getPermissionGroupContext($request));

        $qb = $this->getManager()->createQueryBuilder();
        $qb->select('e');
        $qb->from(static::$entity, 'e');

        $this->applyListFilters($qb, 'e', $request);
        $this->applySorting($qb, 'e', $request);

        $ids = $request->get('ids');
        if ($ids) {
            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            $ids = array_map(function ($id) {
                return (int) $id;
            }, $ids);
            if (count($ids) > static::$listMaxResults) {
                throw $this->createBadRequestException('You can select maximum '.static::$listMaxResults.' entities');
            }

            $qb->andWhere('e.id IN (:ids)');
            $qb->setParameter('ids', $ids);
        }

        $limit = (int) $request->query->getInt('limit', static::$listLimit);
        if ($limit && $limit < 0) {
            throw $this->createBadRequestException('You must select a limit of at least 1');
        }

        // return QueryBuilder result or Pagerfanta depending on if pagination is enabled for the controller
        if (static::$listPaginate) {
            $page   = (int) $request->query->getInt('page', 1);
            $offset = (int) $request->query->getInt('offset');
            $count  = (int) $request->query->getInt('count', static::$listPerPage);

            if ($count > static::$listMaxResults) {
                throw $this->createBadRequestException('You can select maximum '.static::$listMaxResults.' entities');
            } elseif ($count <= 0) {
                throw $this->createBadRequestException('You must select at least 1 entity');
            }

            if ($offset) {
                $result = new OffsetList($qb, $count, $offset);
            } else {
                if ($limit) {
                    // adding limit to the initial qb will
                    // make the initial COUNT have a limit, which
                    // might speed it up a bit
                    $qb->setMaxResults($limit);

                    $pagerAdapter = new DoctrineORMAdapter($qb);
                    $pager        = new LimitedPager($pagerAdapter, $limit);
                } else {
                    $pagerAdapter = new DoctrineORMAdapter($qb);
                    $pager        = new Pagerfanta($pagerAdapter);
                }

                $pager->setMaxPerPage($count);
                $pager->setCurrentPage($page);

                $result = $pager;
            }
        } else {
            if ($limit) {
                $qb->setMaxResults($limit);
            }

            $result = $qb->getQuery()->getResult();
        }

        return View::create($this->wrap($result), Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/csv")
     *
     * @param Request $request
     *
     * @return \FOS\RestBundle\View\View
     */
    public function csvAction(Request $request)
    {
        return $this->listAction($request);
    }

    /**
     * @ApiDoc(
     *      description="Create a new resource",
     *      tags={"CRUD"="#ffa500"},
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Post("")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postAction(Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::CREATE, $this->getPermissionGroupContext($request));

        return $this->handleForm($this->instantiateEntity($request), $request);
    }

    /**
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
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::MODIFY, $this->getPermissionGroupEntityContext($id, $request));

        return $this->handleForm($this->findEntity($id, $request), $request);
    }

    /**
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
     * @Rest\Delete("/{id}", requirements={"id"="\d+"})
     *
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function deleteAction($id, Request $request)
    {
        $this->checkExposed(__METHOD__);
        $this->denyAccessUnlessGranted(PermissionGroupVoter::DELETE, $this->getPermissionGroupEntityContext($id, $request));

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
     * @param QueryBuilder $qb
     * @param string       $alias
     * @param string       $groupBy
     * @param Request      $request
     */
    protected function applyListGroupBy(QueryBuilder $qb, $alias, $groupBy, Request $request)
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
        $sortOptions = static::$sortOptions ? static::$sortOptions : [];
        $sortOptions = array_merge($sortOptions, ['id' => 'id']);
        $sortParam   = strtolower($request->get('order_by'));
        if ($sortParam && !array_key_exists($sortParam, $sortOptions)) {
            throw $this->createBadRequestException('Unknown sort field');
        }

        $orderBy = isset($sortOptions[$sortParam]) ? $sortOptions[$sortParam] : static::$listSort;

        // Add corresponding select and join if $orderBy is a relational field
        if (is_array($orderBy)) {
            $qb->leftJoin("{$alias}.{$orderBy['join']}", $orderBy['as']);
            $orderBy = $orderBy['sort'];
        }

        // prefix with main entity alias if sorting by non-relational field
        $orderBy = strpos($orderBy, '.') ? $orderBy : $alias.'.'.$orderBy;

        $orderDir = strtolower($request->get('order_dir', static::$listOrder));
        if (!in_array($orderDir, ['asc', 'desc'])) {
            throw $this->createBadRequestException('Unknown order value');
        }

        $qb->orderBy($orderBy, $orderDir);
    }

    /**
     * @param AbstractCount $count
     * @param array         $result
     */
    protected function addGroupByNestedCounts(AbstractCount $count, array $result)
    {
        foreach ($result as $group) {
            if (isset($group['date_title'])) {
                $group['title'] = DateHelper::$datePeriodLabels[$group['date_title']];
            }

            $count->addNested(
                $group['value'],
                $group['group_name'],
                $count->getGroupedBy(),
                $group['title'],
                true
            );
        }
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
     * @param object        $model
     * @param FormInterface $form
     *
     * @return object The passed model
     */
    protected function persistModel($model, FormInterface $form = null)
    {
        $em = $this->getManager();
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
        $isModify = $model && $model->getId();
        $status   = $isModify ? Response::HTTP_NO_CONTENT : Response::HTTP_CREATED;

        // the trigger for POST/PATCH requests
        // if 'partial update' is disabled (for new entities by default) then **ALL** form fields will be submitted
        // (event they are not in the request) and the form will show all validation errors,
        // else the form will apply just fields from the request and skip failed validation of unsubmitted ones

        // you can force enable `partial updates` for new entities using `$forcePartialUpdate` option

        $partialUpdate = $isModify;
        if (static::$forcePartialUpdate) {
            $partialUpdate = true;
        }

        // we use POST request for creating and updating entities (including partial updates)
        // so $clearMissing should depends on $model id (switch for POST and PATCH request)

        // we can't always use $clearMissing = false (for partial updates) because of:
        // https://github.com/symfony/symfony/pull/10567
        // https://github.com/symfony/symfony/issues/11493

        // in this case form ViolationMapper should apply entity validation errors on the submitted form

        $form = $this->createForm(static::$type, $model, $options);
        $form->submit($request->request->all(), !$partialUpdate);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->additionalValidation($model, $request);

        $this->persistModel($model, $form);

        $view = View::create(!$isModify ? $this->wrap($model) : null, $status);
        if ($this->isExposed('get')) {
            $view->setLocation($this->getLocationUrl($model, $request));
        }

        return $view;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     */
    protected function getPermissionGroupContext(Request $request)
    {
        return new PermissionGroupContext(static::$entity);
    }

    /**
     * @param $model
     * @param Request $request
     */
    protected function additionalValidation($model, Request $request)
    {
    }

    /**
     * @param int     $id
     * @param Request $request
     *
     * @return object
     */
    protected function getPermissionGroupEntityContext($id, Request $request)
    {
        return new PermissionGroupContext($this->findEntity($id, $request));
    }

    /**
     * @param object  $entity
     * @param Request $request
     * @param array   $params
     *
     * @return string
     */
    protected function getLocationUrl($entity, Request $request, array $params = [])
    {
        $route = preg_replace('/_post$/', '_get', $request->get('_route'));

        return $this->generateUrl($route, array_merge(['id' => $entity->getId()], $params));
    }

    /**
     * @param string $actionMethodName
     *
     * @return bool
     */
    protected function isExposed($actionMethodName)
    {
        if (!is_array(static::$exposeOnly)) {
            return true;
        }

        return in_array(ControllerUtils::cleanAction($actionMethodName), static::$exposeOnly);
    }

    /**
     * @param string $actionMethodName
     */
    protected function checkExposed($actionMethodName)
    {
        if (!$this->isExposed($actionMethodName)) {
            throw new MethodNotAllowedHttpException(static::$exposeOnly);
        }
    }
}

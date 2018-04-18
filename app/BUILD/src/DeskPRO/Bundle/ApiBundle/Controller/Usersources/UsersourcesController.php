<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Usersources;

use Application\DeskPRO\Entity\Usersource;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Usersource\UsersourceType;
use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsersourcesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_sources/{context}", requirements={"context": "(agent|user)"})
 * @ApiDoc(target="all", section="Usersources", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Usersource")
 * @ApiDoc(
 *     target="putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Usersource\UsersourceType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\Usersource",
 *          "context"="user"
 *      }
 *     }
 * )
 * @ApiUserContext("open", admin={"put"})
 */
class UsersourcesController extends CrudController
{
    public static $exposeOnly = ['get', 'put', 'list', 'count'];
    public static $entity     = Usersource::class;
    public static $type       = UsersourceType::class;
    public static $listSort   = 'display_order';
    public static $listOrder  = 'asc';

    /**
     * @ApiDoc(
     *      description="Get a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+|app-\d+|deskpro",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}", requirements={"id"="\d+|app-\d+|deskpro"})
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

    /**
     * @ApiDoc(
     *      description="Update an existing resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+|app-\d+|deskpro",
     *              "description"="The id of the resource",
     *              "dataType"="integer"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="\d+|app-\d+|deskpro"})
     *
     * @param int     $id
     * @param Request $request
     * @SerializerView(serializeNull=true)
     *
     * @return View
     */
    public function putAction($id, Request $request)
    {
        return parent::putAction($id, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere("$alias.type = :context");
        $qb->setParameter('context', $request->attributes->get('context'));

        if ($request->get('type') === 'callback') {
            $qb->andWhere("$alias.source_type IN (:callback_sources)");
            $qb->setParameter('callback_sources', Usersource::$callbackAdapters);
        }

        $isEnabled = $request->get('is_enabled', true);
        if (null !== $isEnabled) {
            $qb->andWhere("$alias.is_enabled = :is_enabled");
            $qb->setParameter('is_enabled', $isEnabled);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'context' => $request->attributes->get('context'),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function getLocationUrl($entity, Request $request, array $params = [])
    {
        $params = array_merge($params, [
            'context' => $request->attributes->get('context'),
        ]);

        return parent::getLocationUrl($entity, $request, $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function denyAccessUnlessGranted($attributes, $object = null, $message = 'Access Denied.')
    {
        // open endpoint, skip security checks
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        $context = $request->attributes->get('context');

        if ($id === 'deskpro') {
            if (!$entity = $this->getManager()->getRepository(Usersource::class)->findOneBy(['app' => null, 'type' => $context])) {
                throw $this->createNotFoundException($this->createEntityNotFoundExceptionMessage(Usersource::class, $id));
            }

            return $entity;
        } elseif (preg_match('/^app-(\d+)$/', $id, $matches)) {
            list(, $appId) = $matches;

            if (!$entity = $this->getManager()->getRepository(Usersource::class)->findOneBy(['app' => $appId, 'type' => $context])) {
                throw $this->createNotFoundException($this->createEntityNotFoundExceptionMessage(Usersource::class, $id));
            }

            return $entity;
        }

        return parent::findEntity($id, $request);
    }
}

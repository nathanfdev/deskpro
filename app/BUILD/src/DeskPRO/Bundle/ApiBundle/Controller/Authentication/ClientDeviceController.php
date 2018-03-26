<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ClientDevice;
use DeskPRO\Bundle\AppBundle\Form\Type\ClientDeviceType;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Component\Util\TypeUtils;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @ApiModes("all")
 * @Rest\Route("/client_devices/{app_type}", requirements={"app_type"="mobile"})
 * @ApiDoc(target="all", section="Client Devices", output="Application\DeskPRO\Entity\ClientDevice")
 * @ApiDoc(
 *     target="registerAction,postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\ClientDeviceType",
 *      "options"={
 *          "app_type"="mobile",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class ClientDeviceController extends CrudController
{
    public static $entity       = ClientDevice::class;
    public static $type         = ClientDeviceType::class;
    public static $listOrder    = 'desc';
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *      description="Updates a given device or create it if it does not exist yet.",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+",
     *              "description"="The id or device_id of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          201="Returned in case of successful resource creation",
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     *
     * NOTE: This is first in this controller because it needs to be evaluated first
     * because we don't want 'register' to be interpretted as a string device_id itself
     *
     * @Rest\Put("/register/{id}", requirements={"id"="\d+|.*?"})
     *
     * {@inheritdoc}
     */
    public function registerAction($id, Request $request)
    {
        try {
            $entity   = $this->findEntity($id, $request);
            $response = $this->putAction($entity->getId(), $request);

            if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
                return $this->wrap($entity);
            } else {
                return $response;
            }
        } catch (NotFoundHttpException $e) {
            return $this->postAction($request);
        }
    }

    /**
     * @ApiDoc(
     *      description="Get a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+|.*?",
     *              "description"="The id or device_id of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          200="We will return such status in case we found your entity",
     *          404="Not Found error will returned in case we can't find entity with specified ID"
     *      }
     * )
     * @Rest\Get("/{id}", requirements={"id"="\d+|.*?"})
     *
     * {@inheritdoc}
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
     *              "requirement"="\d+",
     *              "description"="The id or device_id of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          204="Returned in case of successful resource modify",
     *          400="We will return this in case your request was malformed",
     *      }
     * )
     * @Rest\Put("/{id}", requirements={"id"="\d+|.*?"})
     *
     * {@inheritdoc}
     */
    public function putAction($id, Request $request)
    {
        return parent::putAction($id, $request);
    }

    /**
     * @ApiDoc(
     *      description="Delete a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+|.*?",
     *              "description"="The id or device_id of the resource",
     *              "dataType"="integer|string"
     *          }
     *      },
     *      statusCodes={
     *          200="Returned if everything is ok and there is no such resource anymore",
     *          404="Well, looks like either resource already deleted either it doesn't exists at all"
     *      }
     * )
     * @Rest\Delete("/{id}", requirements={"id"="\d+|.*?"})
     *
     * {@inheritdoc}
     */
    public function deleteAction($id, Request $request)
    {
        return parent::deleteAction($id, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        // Always show only our own
        $qb
            ->andWhere($alias.'.person = :forPerson')
            ->setParameter('forPerson', $this->get('security.token_storage')->getToken()->getUser())
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLocationUrl($entity, Request $request, array $params = [])
    {
        $route     = preg_replace('/_post$/', '_get', $request->get('_route'));
        $setParams = [
            'id'       => $entity->getId(),
            'app_type' => $request->attributes->get('app_type'),
        ];

        return $this->generateUrl($route, array_merge($setParams, $params));
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        $appType = $request->attributes->get('app_type');
        $repos   = $this->getManager()->getRepository(self::$entity);
        $entity  = null;

        // try to find by device id
        $criteria = [
            'device_id' => $id,
            'app_type'  => $appType,
        ];

        /** @var ClientDevice $entity */
        $entity = $repos->findOneBy($criteria);

        // try to find by id
        if (!$entity && TypeUtils::isIntLike($id)) {
            $entity = $repos->find($id);

            if ($entity && $appType && $entity->getAppType() !== $appType) {
                throw $this->createNotFoundException('Not Found');
            }
        }

        if (!$entity) {
            throw $this->createNotFoundException('Not Found');
        }

        $this->denyAccessUnlessGranted(PermissionGroupVoter::VIEW, new PermissionGroupContext($entity));

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options['app_type'] = $request->attributes->get('app_type');
        $options['person']   = $this->get('security.token_storage')->getToken()->getUser();

        if ($model && $model->getDeviceId()) {
            $options['device_id'] = $model->getDeviceId();
        } elseif ($request->attributes->get('id') && !$request->request->get('device_id')) {
            $options['device_id'] = $request->attributes->get('id');
        }

        return parent::handleForm($model, $request, $options);
    }
}

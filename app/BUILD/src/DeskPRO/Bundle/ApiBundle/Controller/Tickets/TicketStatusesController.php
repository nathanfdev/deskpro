<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketStatusesType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketStatusesNewController.
 *
 * @ApiModes("all")
 * @Rest\Route("/ticket_statuses")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Entity\TicketStatus")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketStatusesType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketStatus"
 *      }
 *     }
 * )
 */
class TicketStatusesController extends CrudController
{
    public static $exposeOnly   = ['get', 'list', 'count', 'delete', 'post', 'put'];
    public static $entity       = TicketStatus::class;
    public static $type         = TicketStatusesType::class;
    public static $listOrder    = 'asc';
    public static $listPaginate = false;

    /**
     * @ApiDoc(
     *      description="Get a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+|.*?",
     *              "description"="The id of the resource",
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
     *      description="Delete a resource",
     *      tags={"CRUD"="#ffa500"},
     *      requirements={
     *          {
     *              "name"="id",
     *              "requirement"="\d+|.*?",
     *              "description"="The id of the resource",
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
     * @param int     $id
     * @param Request $request
     *
     * @return View
     */
    public function deleteAction($id, Request $request)
    {
        return parent::deleteAction($id, $request);
    }

    /**
     * {@inheritdoc}
     */
    protected function findOr404($class, $id, $message = null)
    {
        if (!is_numeric($id)) {
            $parts = explode('.', $id);
            if (count($parts) !== 2 || !$parts[1]) {
                throw $this->createNotFoundException($message ?: $this->createEntityNotFoundExceptionMessage($class, $id));
            }
            $id = $parts[1];
        }

        return parent::findOr404($class, $id, $message);
    }

    /**
     * {@inheritdoc}
     */
    protected function deleteEntity($entity)
    {
        if ($entity->getSysId()) {
            throw $this->createBadRequestException('Can\'t delete status with sys_id set');
        }

        return parent::deleteEntity($entity);
    }

    /**
     * {@inheritdoc}
     */
    public function createForm($type, $data = null, array $options = [])
    {
        // In case of creation new TicketStatus - pass null for form as data to create entity in form itself
        if ($data && !$data->getId()) {
            $data = null;
        }

        return parent::createForm($type, $data, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function instantiateEntity(Request $request)
    {
        return new static::$entity('');
    }
}

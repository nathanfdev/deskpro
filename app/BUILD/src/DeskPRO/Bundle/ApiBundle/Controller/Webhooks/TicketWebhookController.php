<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;

use DeskPRO\Bundle\AppBundle\Webhooks\IDGenerator;
use FOS\RestBundle\Controller\Annotations as Rest;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Symfony\Component\Form\FormInterface;

use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ApiModes("all")
 * @Rest\Route("/webhooks/tickets")
 * @ApiDoc(target="all", section="Webhooks", output="DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook")
 * @ApiDoc(
 *     target="postAction, putAction",
 *     input={
 *      "class"="\DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook"
 *      }
 *     }
 * )
 */
class TicketWebhookController extends CrudController
{
    public static $exposeOnly = ['list', 'get', 'post', 'delete', 'put'];
    public static $entity     = TicketWebhook::class;
    public static $type     = TicketWebhookFormType::class;

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
        if ($model instanceof TicketWebhook && !$isModify) {
            $authID = IDGenerator::newID();
            $model->setAuthId($authID);
        }

        return parent::handleForm($model, $request, $options);
    }
}

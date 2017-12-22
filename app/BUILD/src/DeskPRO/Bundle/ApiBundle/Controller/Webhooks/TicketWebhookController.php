<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
 *     target="postAction",
 *     input={
 *      "class"="\DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook",
 *          "terms"="Application\DeskPRO\Tickets\Triggers\TriggerTerms"
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

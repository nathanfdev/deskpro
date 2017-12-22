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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks;
use DeskPRO\Bundle\AppBundle\Webhooks\Converters;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Rest\Route("/webhooks/{webhook}/invocation")
 * @ApiDoc(target="all", section="Webhooks")
 * @ApiUserContext("open")
 * @ApiModes("all")
 */
class WebhookInvocationController extends BaseController
{
    /**
     * @Rest\Post("")
     *
     * @param $webhook
     * @param Request $request
     *
     * @return View
     */
    public function postAction($webhook, Request $request)
    {
        /** @var Webhooks\Repository $repository */
        $repository    = $this->getDoctrine()->getRepository(Webhooks\TicketWebhook::class);
        $webhookEntity = $repository->findOneByAuthId($webhook);

        if (is_null($webhookEntity)) {
            throw new NotFoundHttpException('could not find webhook');
        }
        $webhookRequest = Converters::toWebhookRequest($request);

        $container = $this->getContainer();
        /** @var \DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookExecutor $executor */
        $executor = $container->get(\DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookExecutor::class);
        $executor->execute($webhookEntity, $webhookRequest);

        return View::create([], Response::HTTP_NO_CONTENT);
    }
}

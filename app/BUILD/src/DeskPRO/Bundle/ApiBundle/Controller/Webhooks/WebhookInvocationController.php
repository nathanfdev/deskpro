<?php

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
     * @Rest\Get("")
     *
     * @param $webhook
     * @param Request $request
     *
     * @return View
     */
    public function invokekAction($webhook, Request $request)
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
        $stats = $executor->execute($webhookEntity, $webhookRequest);

        return View::create([
            "data" => [
                "count" => count($stats->getMatchedByTriggers()),
                "tickedIds" => $stats->getMatchedByTriggers()
            ]
        ], Response::HTTP_OK);
    }
}

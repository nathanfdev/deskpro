<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\ExternalEvents;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent\PopupType;
use DeskPRO\Bundle\AppBundle\Form\Type\ExternalEvent\WebhookType;
use DeskPRO\Bundle\AppBundle\Model\PopupModel;
use DeskPRO\Bundle\AppBundle\Notification\Event\ExternalEvent\PopupEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Guzzle\Http\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PopupController.
 *
 * @ApiDoc(target="all", section="ExternalEvents", output="Application\DeskPRO\Entity\Popup")
 *
 * @ApiModes("all")
 * @Rest\Route("/external_events/popup")
 * @ApiUserContext("admin")
 */
class PopupController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="External events",
     *     resourceDescription="Operations about external events",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/raise")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function raiseAction(Request $request)
    {
        $model = new PopupModel();
        $form  = $this->container->get('form.factory')->create(PopupType::class, $model);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            $event = new PopupEvent(
                $model->getUuid(),
                PopupEvent::ACTION_TYPE_RAISE,
                $model->getEventData()
            );
            $this->getContainer()->get('event_dispatcher')->dispatch(
                PopupEvent::EVENT_NAME,
                $event
            );
        } else {
            throw new InvalidFormException($form);
        }

        return View::create($this->wrap($model), Response::HTTP_OK);
    }

    /**
     * @ApiDoc(
     *     section="External events",
     *     resourceDescription="Webhook callback",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Post("/webhook")
     *
     * @param Request $request
     *
     * @throws \Exception
     *
     * @return View
     */
    public function webhookAction(Request $request)
    {
        $form = $this->container->get('form.factory')->create(WebhookType::class);
        $data = $request->request->all();
        $form->submit($data);
        if ($form->isValid()) {
            $httpClient = new Client();
            $httpClient->send([
                $httpClient->createRequest($data['type'], $data['url'], ['X-Request-Performer: DeskPRO'], $data),
            ]);
        }
    }

    /**
     * @ApiDoc(
     *     section="External events",
     *     resourceDescription="Operations about external events",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     }
     * )
     *
     * @Rest\Delete("/dismiss/{uuid}")
     *
     * @param string $uuid
     *
     * @throws \Exception
     *
     * @return View
     */
    public function dismissAction($uuid)
    {
        $this->getContainer()->get('event_dispatcher')->dispatch(
            PopupEvent::EVENT_NAME,
            new PopupEvent($uuid, PopupEvent::ACTION_TYPE_DISMISS, [])
        );

        return View::create([], Response::HTTP_NO_CONTENT);
    }
}

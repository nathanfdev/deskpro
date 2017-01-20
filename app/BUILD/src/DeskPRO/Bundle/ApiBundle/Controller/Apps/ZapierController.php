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

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Form\Type\ZapierHookType;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ZapierController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/zapier")
 */
class ZapierController extends CrudController
{
    public static $entity = ZapierHook::class;
    public static $type   = ZapierHookType::class;

    /**
     * Gather specific info about authentication.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="ping and if it not the case install Zapier app",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     * )
     * @Rest\Get("/ping")
     */
    public function pingAction()
    {
        return View::create([], 200);

//        $manager = $this->get('deskpro.apps.manager');

//        $package = $manager->getPackage('deskpro_zapier');

//        if ($manager->getPackageApps('deskpro_zapier')) {
//            return View::create([], 204);
//        }

//        $context = new AppManipulatorContext([], 'Zapier');

//        /** @var \Application\Deskpro\App\AppManipulator $appManipulator */
//        $appManipulator = $this->getContainer()->getSystemService('app_manipulator');
//        if ($appManipulator->installInstance($package, $context)) {
//            return View::create([], 204);
//        }
//        return $this->createBadRequestException('Could not install app');
    }

    /**
     * Allow Zapier to subscribe to hooks.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="Called by Zapier when subscribing to hooks",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     * )
     * @Rest\Post("/hooks")
     *
     * @param Request $request
     *
     * @return View|BadRequestHttpException
     */
    public function postHooksAction(Request $request)
    {
        $zapierHook = new ZapierHook();

        $this->handleForm($zapierHook, $request);

        $httpClient = new HttpClient(['timeout' => 30]);

        $serializationContext = new SideloadSerializationContext();

        $options = [];
        switch ($zapierHook->getEvent()) {
            case 'ticket_created':
                $ticket = $this->getRepository(Ticket::class)->findOneBy([]);
                $body   = $this->get('serializer')->toArray($ticket, $serializationContext);
                break;
            case 'new_ticket_reply':
                $ticket = $this->getRepository(TicketMessage::class)->findOneBy([]);
                $body   = $this->get('serializer')->toArray($ticket, $serializationContext);
                break;
            default:
                return $this->createBadRequestException('Unknown event');
        }
        $options['body'] = \GuzzleHttp\json_encode($body);
        $httpClient->request('POST', $zapierHook->getTargetUrl(), $options);

        return View::create(['id' => $zapierHook->getId()]);
    }

    /**
     * Allow Zapier to unsubscribe to hooks.
     *
     * @ApiDoc(
     *     section="Apps",
     *     description="Called by Zapier when unsubscribing to hooks",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     * )
     * @Rest\Post("/delete_hooks/{id}")
     *
     * @param int $id
     *
     * @return View|NotFoundHttpException
     */
    public function deleteHooksAction($id)
    {
        $zapierHook = $this->getRepository(ZapierHook::class)->find($id);

        if (!$zapierHook) {
            return $this->createNotFoundException();
        }

        $this->getManager()->remove($zapierHook);
        $this->getManager()->flush();

        return View::create([], 200);
    }
}

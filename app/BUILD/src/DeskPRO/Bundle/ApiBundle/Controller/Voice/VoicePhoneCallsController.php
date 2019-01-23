<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\TicketLog;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallLog;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupContext;
use DeskPRO\Bundle\AppBundle\Security\Voter\PermissionGroups\PermissionGroupVoter;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoicePhoneCallsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voice_phone_calls")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall")
 */
class VoicePhoneCallsController extends CrudController
{
    public static $entity     = VoicePhoneCall::class;
    public static $exposeOnly = ['get', 'list', 'count'];

    /**
     * Delete a call record from blob storage.
     *
     * @ApiDoc(
     *     description="Delete phone call record",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true,
     *     noOutput=true
     * )
     *
     * @Rest\Delete("/{phoneCall}/record", name="voice_phone_call_delete_record")
     *
     * @param VoicePhoneCall $phoneCall
     *
     * @throws \Exception
     *
     * @return View
     */
    public function deletePhoneCallRecordAction(VoicePhoneCall $phoneCall)
    {
        $em        = $this->getManager();
        $attribute = $em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);

        if (!$attribute) {
            throw $this->createNotFoundException('Ticket not found');
        }

        /** @var TicketMessage $message */
        $message = $attribute->getMessage();
        $ticket  = $message->getTicket();

        $this->denyAccessUnlessGranted(PermissionGroupVoter::DELETE, new PermissionGroupContext($ticket, $message));

        $recording = $phoneCall->getRecording();
        if ($recording) {
            $phoneCall->setRecording(null);
            $em->persist($phoneCall);

            $ticketLog = new TicketLog();
            $ticketLog
                ->setTicket($ticket)
                ->setPerson($this->getUser())
                ->setIdObject($phoneCall->getId())
                ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED)
                ->setDetailItem('filesize', sprintf('%.2f', $recording->getFilesize() / 1024))
            ;

            $em->persist($ticketLog);

            $callLog = new VoicePhoneCallLog();
            $callLog
                ->setPhoneCall($phoneCall)
                ->setPerson($this->getUser())
                ->setActionType(VoicePhoneCallLog::ACTION_RECORDING_DELETED)
            ;

            $em->persist($callLog);

            $this->get('blob.storage')->deleteBlobRecord($recording);
            $em->flush();
        }

        $serializedData = $this->get('serializer')->toArray(
            new ApiWrapper($phoneCall),
            new SideloadSerializationContext()
        );

        $this->get('event_dispatcher')->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent(
                'agent.voice.recording_status',
                ['data' => $serializedData]
            )
        );

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}

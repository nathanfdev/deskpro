<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Voice;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicemailRecord;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class VoicemailRecordsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/voicemail_records")
 * @Feature("voice")
 * @ApiDoc(target="all", section="Voice Channel", output="DeskPRO\Bundle\AppBundle\Entity\VoicemailRecord")
 */
class VoicemailRecordsController extends CrudController
{
    use TicketSaveTrait;

    public static $entity     = VoicemailRecord::class;
    public static $exposeOnly = ['get', 'list', 'count', 'delete'];

    /**
     * @ApiDoc(
     *     description="Mark voicemail record as listened to",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/{record}/mark_listened")
     *
     * @param VoicemailRecord $record
     *
     * @throws \Exception
     *
     * @return View
     */
    public function markListenedToAction(VoicemailRecord $record)
    {
        if ($record->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Unable to update voicemail record');
        }

        $record->setIsListened(true);

        $em = $this->getManager();
        $em->persist($record);
        $em->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @ApiDoc(
     *     description="Create voicemail ticket",
     *     statusCodes={
     *         204="Returned if everything is ok"
     *     },
     *     noInput=true
     * )
     *
     * @Rest\Put("/{record}/create_ticket")
     *
     * @param VoicemailRecord $record
     *
     * @throws \Exception
     *
     * @return View
     */
    public function createTicketAction(VoicemailRecord $record)
    {
        if ($record->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Unable to create voicemail ticket');
        }

        $phoneCall = $record->getPhoneCall();
        if (!$phoneCall) {
            throw $this->createBadRequestException('Voicemail phone call not found');
        }

        $em = $this->getManager();

        // check if ticket is already created for this phone call
        $messageAttribute = $em->getRepository(TicketMessageVoicePhoneCall::class)->findOneBy([
            'phoneCall' => $phoneCall,
        ]);
        if ($messageAttribute) {
            // ticket is already created, just return it
            $ticket = $messageAttribute->getMessage()->getTicket();
        } else {
            // create a new ticket based on the voicemail message
            $ticketMessageCall = new TicketMessageVoicePhoneCall();
            $ticketMessageCall->setPhoneCall($phoneCall);

            $ticketMessage = new TicketMessage();
            $ticketMessage->setPerson($phoneCall->getPerson());
            $ticketMessage->addAttribute($ticketMessageCall);
            $ticketMessage->setMessage('Call from '.$phoneCall->getExternalNumber());
            $ticketMessage->setAsAgentNote(true);

            $ticket = new Ticket();
            $ticket->disableAutoTicketProcess();
            $ticket->setSubject('Voicemail from '.$phoneCall->getExternalNumber());
            $ticket->setPerson($phoneCall->getPerson());
            $ticket->setAgent($this->getUser());
            $ticket->setProperty('voice_phone_number', $phoneCall->getExternalNumber());
            $ticket->addMessage($ticketMessage);

            $this->saveTicket($ticket);
        }

        // mark the voicemail record as deleted because it's not needed anymore
        $record->setIsDeleted(true);

        $em = $this->getManager();
        $em->persist($record);
        $em->flush();

        return new View($this->wrap($ticket));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        $qb->andWhere("$alias.agent = :agent");
        $qb->setParameter('agent', $this->getUser());

        $noBlob = $request->get('no_blob', 0);
        if ($noBlob != -1) {
            $qb->join("$alias.phoneCall", 'p');
            if ($noBlob) {
                $qb->andWhere('p.recording IS NULL');
            } else {
                $qb->andWhere('p.recording > 0');
            }
        }

        $isDeleted = $request->get('is_deleted', 0);
        if ($isDeleted != -1) {
            $qb->andWhere("$alias.isDeleted = :is_deleted");
            $qb->setParameter('is_deleted', (bool) $isDeleted);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param VoicemailRecord $entity
     *
     * @throws \Exception
     */
    protected function deleteEntity($entity)
    {
        if ($entity->getAgent() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Unable to delete voicemail record');
        }

        $entity->setIsDeleted(true);

        $em = $this->getManager();
        $em->persist($entity);
        $em->flush();
    }
}

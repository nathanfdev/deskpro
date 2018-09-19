<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Blob as BlobRepository;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Traits\ApiKeyAwareTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketAwarePersistModelTrait;
use DeskPRO\Bundle\ApiBundle\Traits\Tickets\TicketSaveTrait;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketMessageType;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketMessageController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/messages")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketMessage")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketMessageType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TicketMessage",
 *          "ticket"="Application\DeskPRO\Entity\Ticket",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "admin_api_key_request"=true
 *      }
 *     }
 * )
 */
class TicketMessagesController extends AbstractTicketsCrudSubController
{
    use TicketSaveTrait, TicketAwarePersistModelTrait, ApiKeyAwareTrait;

    public static $entity         = TicketMessage::class;
    public static $type           = TicketMessageType::class;
    public static $parentProperty = 'ticket';
    public static $listSort       = 'id';
    public static $listOrder      = 'asc';
    public static $sortOptions    = [
        'date_created' => 'date_created',
        'date'         => 'date_created', // alias
    ];

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket'                 => $this->findParentOr404(),
            'person'                 => $this->getUser(),
            'has_attachments'        => true,
            'with_ticket_validation' => $request->get('with_ticket_validation'),
            'allow_set_status'       => true,
            'allow_apply_macros'     => true,
            'admin_api_key_request'  => $this->isAdminApiKeyRequest(),
        ]);

        return parent::handleForm($model, $request, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketMessage $entity
     */
    protected function deleteEntity($entity)
    {
        $ticket = $entity->getTicket();
        $ticket->disableAutoTicketProcess();
        $ticket->removeMessage($entity);
        foreach ($entity->getAttachments() as $attachment) {
            $this->deleteAttachment($attachment);
        }
        $this->saveTicket($ticket);
    }

    /**
     * @param TicketAttachment $attachment
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    protected function deleteAttachment(TicketAttachment $attachment)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');

        $em->remove($attachment);
        $attachment->getMessage()->getAttachments()->removeElement($attachment);
        $em->flush();

        $blob         = $attachment->getBlob();
        $originalBlob = $blob->getOriginalBlob();
        /** @var BlobRepository $blobRepository */
        $blobRepository = $em->getRepository(Blob::class);
        $blobs          = $blobRepository->findBy(['original_blob' => $originalBlob ?: $blob]);

        if ($originalBlob) {
            array_push($blobs, $originalBlob);
        }
        array_push($blobs, $blob);

        foreach ($blobs as $foundBlob) {
            $this->get('deskpro.blob_storage')->deleteBlobRecord($foundBlob);
        }

        $em->getConnection()->delete('tickets_attachments', ['id' => $attachment->getId()]);
    }

    /**
     * @ApiDoc(
     *      description="Get collection of resources",
     *      filters={
     *          {"name"="page", "pattern"="\d", "description"="Which page to display", "dataType"="integer"},
     *          {"name"="count", "pattern"="\d", "description"="Resource per page count", "dataType"="integer"},
     *          {"name"="limit", "pattern"="\d", "description"="Max number of resources to return", "dataType"="integer"},
     *          {"name"="ids", "pattern"="[\d,]+", "description"="Comma separated list of IDs", "dataType"="string"},
     *      },
     *      statusCodes={
     *          200="Returned if your request was successful",
     *          400="An error will occur if you provide wrong filters set",
     *      },
     *      output="array<Application\DeskPRO\Entity\TicketAttachment>"
     * )
     *
     * @Rest\Get("/{id}/attachments")
     *
     * @param Request $request
     * @param int     $id
     *
     * @return View
     */
    public function getAttachmentsAction(Request $request, $id)
    {
        return TicketAttachmentsController::subRequestSearch($this->getKernel(), $request, [
            'message'  => $this->findEntity($id, $request)->getId(),
            'parentId' => $request->get(static::$parentParameter),
        ]);
    }
}

<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\TicketAttachment;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class TicketAttachmentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/attachments")
 * @ApiDoc(target="all", section="Tickets", output="Application\DeskPRO\Entity\TicketAttachment")
 */
class TicketAttachmentsController extends AbstractTicketsCrudSubController
{
    public static $entity         = TicketAttachment::class;
    public static $parentProperty = 'ticket';
    public static $exposeOnly     = ['list'];
    public static $listOrder      = 'asc';

    /**
     * @param HttpKernelInterface $kernel
     * @param Request             $masterRequest
     * @param array               $params
     *
     * @return Response
     */
    public static function subRequestSearch(HttpKernelInterface $kernel, Request $masterRequest, array $params)
    {
        $request = $masterRequest->duplicate(
            array_merge($params, $masterRequest->query->all()),
            null,
            ['_controller' => 'ApiBundle:Tickets\TicketAttachments:list']
        );
        $request->query->add($params);

        return $kernel->handle($request, HttpKernelInterface::SUB_REQUEST);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyListFilters(QueryBuilder $qb, $alias, Request $request)
    {
        parent::applyListFilters($qb, $alias, $request);

        if (null !== $request->get('message')) {
            $message = (int) $request->get('message');
            $qb->andWhere("$alias.message = :message_id");
            $qb->setParameter('message_id', $message);
        }
    }
}

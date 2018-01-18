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

use Application\DeskPRO\Entity\TaskComment;
use Application\DeskPRO\Entity\TicketTrigger;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\Form\Type\Task\TaskCommentType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class WebhookTriggersController.
 *
 * @ApiModes("all")
 * @Rest\Route("/webhooks/{parentId}/triggers")
 * @ApiDoc(target="all", section="Tasks", output="Application\DeskPRO\Entity\TaskComment")
 *
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Task\TaskCommentType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TaskComment",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "task"="Application\DeskPRO\Entity\Task"
 *      }
 *     }
 * )
 */
class WebhookTriggersController extends CrudSubController
{
    public static $entity         = TicketTrigger::class;
    public static $type           = TriggerFormType::class;
    public static $listPaginate   = true;
    public static $listOrder      = 'DESC';
    public static $listSort       = 'date_created';
    public static $parentProperty = 'task';

    /** @var TicketWebhook */
    private $parent;

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $isModify = $model && $model->getId();

        $options[TriggerFormType::OPTION_DEFAULT_TITLE] = 'Webhook trigger';
        $options[TriggerFormType::OPTION_ENABLE_WEBHOOK_PROPS] = true;

        // save first the model, then update the relationship, no consistency though
        $view = parent::handleForm($model, $request, $options);
        if (!$isModify) {
            /** @var TicketWebhook $parent */
            $parent = $request->attributes->get('parent');
            $parent->getTriggers()->add($model);
            $this->persistModel($model);
        }

        return $view;
    }

    protected function instantiateEntity(Request $request)
    {
        $parent = $this->findParentOr404();
        $request->attributes->set('parent', $parent);

        return new static::$entity();
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntity($id, Request $request)
    {
        $parentId = $request->get(static::$parentParameter);
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('w, t')
            ->from(TicketWebhook::class, 'w')
            ->innerjoin('w.triggers', 't')
            ->where('t.id = :tid')
            ->andWhere('w.id = :wid')
            ->setParameter('tid', $id)
            ->setParameter('wid', $parentId)
        ;
        /** @var TicketWebhook $parent */
        $parent = $qb->getQuery()->getOneOrNullResult();

        if (!$parent || 1 !== $parent->getTriggers()->count()) {
            throw $this->createNotFoundException($this->createEntityNotFoundExceptionMessage(static::$entity, $id));
        }

        $entity = $parent->getTriggers()->offsetGet(0);
        $request->attributes->set('parent', $parent);
        return $entity;
    }

    /**
     * @return object
     */
    protected function findParentOr404()
    {
        $request     = $this->container->get('request_stack')->getCurrentRequest();
        $parentId    = $request->get(static::$parentParameter);
        return $this->findOr404(TicketWebhook::class, $parentId);
    }

    protected function deleteEntity( $entity )
    {
        $request     = $this->container->get('request_stack')->getCurrentRequest();
        /** @var TicketWebhook $parent */
        $parent = $request->attributes->get('parent');

        if (! $parent) {
            throw new \RuntimeException('excepted');
        }

        $parent->getTriggers()->removeElement($entity);
        $em = $this->getManager();
        $em->remove($entity);
        $em->flush();
    }
}

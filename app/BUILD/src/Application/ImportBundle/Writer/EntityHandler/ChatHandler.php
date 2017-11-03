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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use Application\ImportBundle\Model;

/**
 * Class ChatHandler.
 */
class ChatHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Chat::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Chat $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\ChatConversation $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getChatMapper(), $model);
        $entity
            ->setSubject($model->getSubject())
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setAgent($this->helpers->getPersonHelper()->findOrCreatePerson($model->getAgent(), true))
            ->setStatus(Entity\ChatConversation::STATUS_ENDED)
            ->setDateEnded($model->getDateEnded() ?: new \DateTime())
            ->setEndedBy($model->getEndedBy())
            ->setRatingOverall($model->getRatingOverall())
            ->setRatingComment($model->getRatingComment())
            ->setDepartment($this->mappers->getDepartmentMapper()->getDefaultChatDepartment())
        ;

        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }

        $this->helpers->getCustomDataHelper()->updateCustomData($this->mappers->getChatCustomDefMapper(), $model, $entity);
        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelChatConversation::class);

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);

        // persist others related entities which contains own oids
        foreach ($model->getMessages() as $messageModel) {
            $this->createOrUpdateChatMessage($messageModel, $entity);
        }
    }

    /**
     * @param Model\ChatMessage       $model
     * @param Entity\ChatConversation $chatEntity
     *
     * @return Entity\TicketMessage
     */
    private function createOrUpdateChatMessage(Model\ChatMessage $model, Entity\ChatConversation $chatEntity)
    {
        /** @var Entity\ChatMessage $messageEntity */
        $messageEntity = $this->findOrCreateEntity($this->mappers->getChatMessageMapper(), $model);
        $messageEntity->setAuthor($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()));
        $messageEntity->setContent($model->getContent());
        $messageEntity->setIsHtml(true);

        // simplify edge case when an agent can use the chat widget
        // because we sometimes can just rely on the author role
        if (!$messageEntity->getAuthor() || !$messageEntity->getAuthor()->isAgent()) {
            $messageEntity->setIsUser(true);
            $messageEntity->setOrigin('user');
            $messageEntity->setMetadata([
                'is_html'         => true,
                'is_user_message' => true,
            ]);
        } else {
            $messageEntity->setOrigin('agent');
        }

        if ($model->getDateCreated()) {
            $messageEntity->setDateCreated($model->getDateCreated());
        }
        if (!$chatEntity->getMessages()->contains($messageEntity)) {
            $chatEntity->addMessage($messageEntity);
        }

        // persist basic entity
        $this->persister->persistAndFlush($messageEntity, $model);
    }
}

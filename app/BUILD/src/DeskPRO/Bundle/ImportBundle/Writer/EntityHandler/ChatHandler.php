<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;

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

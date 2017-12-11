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
 * DeskPRO ticket importer.
 *
 * Class Ticket
 */
class TicketHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Ticket::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\Ticket $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var Entity\Ticket $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getTicketMapper(), $model);
        $entity
            ->disableAutoTicketProcess()
            ->setSubject($model->getSubject())
            ->setStatus($model->getStatus())
            ->setLanguage($this->helpers->getLanguageHelper()->findOrCreateLanguage($model->getLanguage()))
            ->setDateResolved($model->getDateResolved())
            ->setDateArchived($model->getDateArchived())
        ;

        if ($model->getBrand()) {
            // overwrite custom ticket brand from the model
            $brandName = $model->getBrand();
        }
        if ($model->getRef()) {
            $entity->setRef($model->getRef());
        }
        if ($model->getDepartment()) {
            $entity->setDepartment($this->helpers->getDepartmentHelper()->findOrCreateDepartment(
                'ticket',
                $model->getDepartment(),
                $brandName
            ));
        }
        if ($model->getUrgency()) {
            $entity->setUrgency($model->getUrgency());
        }
        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }
        if ($model->getOrganization()) {
            $entity->setOrganization($this->helpers->getOrganizationHelper()->findOrCreateOrganization($model->getOrganization()));
        }

        // update ticket person
        if ($model->getPerson()) {
            $entity->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()));
        }

        if (!$entity->getPerson()) {
            $this->logger->warning("Unable to create a ticket {$model->getOid()} without person.");

            return;
        }

        // update ticket agent
        if ($model->getAgent()) {
            $agentEntity = $this->helpers->getPersonHelper()->findOrCreatePerson($model->getAgent(), true);
            if ($agentEntity && $agentEntity->isAgent()) {
                $entity->setAgent($agentEntity);
            } else {
                $this->logger->warning(sprintf('Unable to set ticket agent, `%s` is not an agent', $model->getAgent()));
                $entity->setAgent(null);
            }
        } else {
            $entity->setAgent(null);
        }

        $this->helpers->getCustomDataHelper()->updateCustomData($this->mappers->getTicketCustomDefMapper(), $model, $entity);
        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelTicket::class);

        // update ticket participants
        foreach ($model->getParticipants() as $participantEmailOrId) {
            $participantPerson = $this->helpers->getPersonHelper()->findOrCreatePerson($participantEmailOrId);
            $participant       = $entity->getParticipants()->filter(function (Entity\TicketParticipant $participant) use ($participantPerson) {
                return $participant->getPerson() === $participantPerson;
            })->first();

            if (!$participant) {
                $participant = new Entity\TicketParticipant();
                $participant->setPerson($participantPerson);

                $entity->addParticipant($participant);
            }
        }
        foreach ($entity->getParticipants() as $participant) {
            if (!$participant->getPerson()) {
                continue;
            }

            // get all person emails
            $participantEmails   = $participant->getPerson()->getEmailAddresses();
            $participantEmails[] = $participant->getPerson()->getPrimaryEmailAddress();

            // get person oid
            /** @var Entity\ImportMap $personImportMap */
            $personImportMap = $this->mappers->getImportMapMapper()->findOneBy([
                'new_id'   => $participant->getPerson()->getId(),
                'typename' => $this->mappers->getImportMapMapper()->getImportMapKey(Model\Person::class),
            ]);

            if ($personImportMap) {
                $participantEmails[] = $personImportMap->getOldId();
            }

            // compare person emails and remove deleted participants
            if (!count(array_intersect($participantEmails, $model->getParticipants()))) {
                $entity->getParticipants()->removeElement($participant);
            }
        }

        // update ticket category
        if ($model->getCategory()) {
            $entity->setCategory($this->helpers->getCategoryHelper()->findOrCreateCategory(
                $this->mappers->getTicketCategoryMapper(),
                $model->getCategory(),
                $brandName
            ));
        }

        // update ticket product
        if ($model->getProduct()) {
            $entity->setProduct($this->helpers->getCategoryHelper()->findOrCreateCategory(
                $this->mappers->getTicketProductMapper(),
                $model->getProduct(),
                $brandName
            ));
        }

        // update ticket workflow
        if ($model->getWorkflow()) {
            $entity->setWorkflow($this->findOrCreateWorkflow($model->getWorkflow()));
        }

        // update ticket priority
        if ($model->getPriority()) {
            $entity->setPriority($this->findOrCreatePriority($model->getPriority()));
        }

        if ($brandName) {
            // set specific brand for multi-brand helpdesks
            $brand = $this->helpers->getBrandHelper()->findOrCreateBrand($brandName);
            if ($brand) {
                $entity->setBrand($brand);
            }
        }

        // ensure that the ticket has brand and department
        // they are optional so set default ones if they are empty
        if (!$entity->getBrand()) {
            // if department is assigned and has a brand
            // then set the first brand from the department
            if ($entity->getDepartment() && $entity->getDepartment()->getBrands()->count()) {
                $entity->setBrand($entity->getDepartment()->getBrands()->first());
            } else {
                $entity->setBrand($this->mappers->getBrandMapper()->getDefaultBrand());
            }
        }

        // ensure ticket's department and brand are related
        $ticketBrand      = $entity->getBrand();
        $ticketDepartment = $entity->getDepartment();
        if (!$ticketDepartment || ($ticketBrand && !$ticketBrand->getDepartments()->contains($ticketDepartment))) {
            $entity->setDepartment($this->mappers->getBrandMapper()->getDefaultDepartment($ticketBrand));
        }

        // ensure we are using leaf department
        $ticketDepartment = $entity->getDepartment();
        if ($ticketDepartment && !$ticketDepartment->isLeaf()) {
            foreach ($ticketDepartment->getAllChildren() as $childDepartment) {
                if ($childDepartment->isLeaf()) {
                    $entity->setDepartment($childDepartment);
                    break;
                }
            }
        }

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);

        // persist others related entities which contains own oids
        foreach ($model->getMessages() as $messageModel) {
            $this->createOrUpdateTicketMessage($messageModel, $entity);
        }

        // set on hold status after message updates otherwise it will be overwritten
        $entity->setIsHold($model->isHold());
        $this->persister->persistAndFlush($entity, $model);

        // write ticket logs
        foreach ($model->getLogs() as $logModel) {
            /** @var Entity\TicketLog $logEntity */
            $logEntity = $this->findOrCreateEntity($this->mappers->getTicketLogMapper(), $logModel);
            $logEntity->setTicket($entity);
            $logEntity->setActionType($logModel->getActionType());
            $logEntity->setDetails($logModel->getDetails());
            if ($logModel->getDateCreated()) {
                $logEntity->setDateCreated($logModel->getDateCreated());
            }

            $this->persister->persistAndFlush($logEntity, $logModel);
        }

        $logEntity = new Entity\TicketLog();
        $logEntity->setTicket($entity);
        $logEntity->setActionType('free');
        $logEntity->setDetails([
            'message' => sprintf('Imported (old ticket ID #%s)', $model->getOid()),
        ]);

        $this->persister->persistAndFlush($logEntity);
    }

    /**
     * @param Model\TicketMessage $model
     * @param Entity\Ticket       $ticketEntity
     *
     * @return Entity\TicketMessage
     */
    private function createOrUpdateTicketMessage(Model\TicketMessage $model, Entity\Ticket $ticketEntity)
    {
        /** @var Entity\TicketMessage $messageEntity */
        $messageEntity = $this->findOrCreateEntity($this->mappers->getTicketMessageMapper(), $model);
        $messageEntity
            ->setTicket($ticketEntity)
            ->setAsAgentNote($model->isNote())
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
        ;

        if (!$messageEntity->getPerson()) {
            $this->logger->warning("Unable to create a ticket message {$model->getOid()} without person.");

            return;
        }

        if ($model->getDateCreated()) {
            $messageEntity->setDateCreated($model->getDateCreated());
        }

        // update message content
        if ($model->getFormat() === 'text') {
            $messageEntity->setMessageText($model->getMessage());
        } else {
            $messageEntity->setMessageHtml($model->getMessage());
        }

        $ticketEntity->addMessage($messageEntity);

        // persist basic entity
        $this->persister->persistAndFlush($messageEntity, $model);

        // persist others related entities which contains own oids
        foreach ($model->getAttachments() as $attachmentModel) {
            $this->helpers->getAttachmentHelper()->createOrUpdateAttachment(
                $this->mappers->getTicketAttachmentMapper(), $attachmentModel, $messageEntity
            );
        }
    }

    /**
     * @param string $title
     *
     * @return Entity\TicketWorkflow|null
     */
    public function findOrCreateWorkflow($title)
    {
        $entity = null;
        if ($title) {
            $entity = $this->mappers->getTicketWorkflowMapper()->findOneBy(['title' => $title]);
            if ($entity) {
                $this->logger->debug("Found existing ticket workflow `$title`");
            } else {
                $this->logger->notice("Create a new ticket workflow `$title`");

                $entity = new Entity\TicketWorkflow();
                $entity->setTitle($title);

                $this->persister->persistAndFlush($entity);
            }
        }

        return $entity;
    }

    /**
     * @param string $title
     *
     * @return Entity\TicketPriority|null
     */
    public function findOrCreatePriority($title)
    {
        $entity = null;
        if ($title) {
            $entity = $this->mappers->getTicketPriorityMapper()->findOneBy(['title' => $title]);
            if ($entity) {
                $this->logger->debug("Found existing ticket priority `$title`");
            } else {
                $this->logger->notice("Create a new ticket priority `$title`");

                $entity = new Entity\TicketPriority();
                $entity->setTitle($title);

                $this->persister->persistAndFlush($entity);
            }
        }

        return $entity;
    }
}

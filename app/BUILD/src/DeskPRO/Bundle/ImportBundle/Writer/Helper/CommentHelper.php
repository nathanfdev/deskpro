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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CommentHelper.
 */
class CommentHelper
{
    /**
     * @var CreateEntityHelper
     */
    private $createEntityHelper;

    /**
     * @var PersonHelper
     */
    private $personHelper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param CreateEntityHelper $createEntityHelper
     * @param PersonHelper       $personHelper
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(CreateEntityHelper $createEntityHelper, PersonHelper $personHelper, EntityPersister $persister, LoggerInterface $logger)
    {
        $this->createEntityHelper = $createEntityHelper;
        $this->personHelper       = $personHelper;
        $this->persister          = $persister;
        $this->logger             = $logger;
    }

    /**
     * Persist comment.
     *
     * @param MapperInterface $mapper
     * @param Model\Comment   $model
     * @param mixed           $entity
     */
    public function createOrUpdateComment(MapperInterface $mapper, Model\Comment $model, $entity)
    {
        /** @var Entity\CommentAbstract $comment */
        $comment = $this->createEntityHelper->findOrCreateEntity($mapper, $model);
        $comment->setContent($model->getContent());
        $comment->setStatus($model->getStatus());

        if ($model->getDateCreated()) {
            $comment->setDateCreated($model->getDateCreated());
        }
        if ($model->getPerson()) {
            $comment->setPerson($this->personHelper->findOrCreatePerson($model->getPerson()));
        }

        if (!$entity->getComments()->contains($comment)) {
            $entity->addComment($comment);
        }

        $this->persister->persistAndFlush($comment, $model);
    }
}

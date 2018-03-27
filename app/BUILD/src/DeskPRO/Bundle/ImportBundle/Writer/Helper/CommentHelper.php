<?php

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

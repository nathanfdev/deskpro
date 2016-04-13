<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\ApiTag;

use Application\DeskPRO\Entity\ApiKey;
use DeskPRO\Bundle\AppBundle\ApiTag\Model\HierarchyCreator;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TagsManipulator.
 */
class TagsManipulator
{
    /** @var EntityManager */
    private $em;

    /**
     * @var TagsCollector
     */
    private $tagsCollector;

    /**
     * @var HierarchyCreator
     */
    private $hierarchyCreator;

    /**
     * TagsManipulator constructor.
     *
     * @param EntityManager $em
     * @param TagsCollector $tagsCollector
     */
    public function __construct(EntityManager $em, TagsCollector $tagsCollector)
    {
        $this->em               = $em;
        $this->tagsCollector    = $tagsCollector;
        $this->hierarchyCreator = $tagsCollector->getHierarchyCreator();
    }

    /**
     * @param $keyId
     *
     * @return array
     */
    public function gatherTagsForKey($keyId)
    {
        $gatheredTags = [];

        foreach ($this->getKey($keyId)->getActions() as $action) {
            /* @var ApiKeyAction $action */
            $gatheredTags[] = $action->getAction();
        }

        return $gatheredTags;
    }

    /**
     * @param int    $key
     * @param string $action
     * @param int    $value
     */
    public function updateTags($key, $action, $value)
    {
        $key = $this->getKey($key);

        $this->tagsCollector->collectTags();
        $this->tagsCollector->createTagsHierarchy();

        $originalAction = $action;

        $parts = explode('.', $originalAction);

        $i   = 0;
        $tag = null;
        while ($part = array_shift($parts)) {
            if ($part !== '*') {
                $tag = $this->hierarchyCreator->findTag($part, $originalAction, $i++);
            }
        }

        if ($tag && $tag->hasNodes()) {
            $deletePattern = "$originalAction%";
            $action .= $action !== '*' ? '.*' : '';
        } else {
            $deletePattern = $originalAction;
        }

        $this->deleteOld($key, $deletePattern);

        $prefix = '';
        if ($value === 0) {
            return;
        } elseif ($value < 0) {
            $prefix = '-';
        }

        $apiAction = new ApiKeyAction();
        $apiAction->setAction($prefix.$action)->setKey($key);

        $this->em->persist($apiAction);
        $this->em->flush($apiAction);
    }

    /**
     * @param $key
     *
     * @return ApiKey|null|object
     */
    private function getKey($key)
    {
        $key = $this->em->find(ApiKey::class, $key);
        if (!$key) {
            throw new NotFoundHttpException(sprintf('ApiKey with id [ %d ] was not found', (int) $key));
        }

        return $key;
    }

    /**
     * @param ApiKey $key
     * @param string $pattern
     */
    private function deleteOld(ApiKey $key, $pattern)
    {
        $qb = $this->em->getRepository(ApiKeyAction::class)->createQueryBuilder('aka');
        $qb->delete()
           ->where($qb->expr()->like('aka.action', $qb->expr()->literal($pattern)))
           ->orWhere($qb->expr()->like('aka.action', $qb->expr()->literal('-'.$pattern)))
           ->andWhere('aka.key = :key')
           ->setParameters(['key' => $key]);
        $qb->getQuery()->execute();
    }
}

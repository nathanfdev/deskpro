<?php

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
     * @param string $tags
     */
    public function updateTags($key, $tags)
    {
        $key = $this->getKey($key);

        $actions = explode(',', $tags);
        $actions = array_map(
            function ($item) {
                return trim($item) ?: false;
            },
            $actions
        );
        $actions = array_filter($actions, 'boolval');

        $tags      = $this->tagsCollector->getTagsHierarchyForApi($actions);
        $root      = array_shift($tags);
        $flattened = [];
        $this->flatten($root, $flattened);
        $this->deleteOld($key);
        foreach (array_keys($flattened) as $action) {
            $apiAction = new ApiKeyAction();
            $apiAction->setAction($action)->setKey($key);
            $this->em->persist($apiAction);
        }
        $this->em->flush();
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\ApiTag\Model\Tag $hierarchy
     * @param array                                      $flattened
     */
    private function flatten($hierarchy, array &$flattened)
    {
        if (!$hierarchy->getParent() && $hierarchy->getValue()) {
            //this is root
            $path             = ($hierarchy->getValue() < 0 ? '-' : '').$hierarchy->getPath();
            $flattened[$path] = true;
        }

        if ($hierarchy->hasNodes()) {
            foreach ($hierarchy->getNodes() as $node) {
                if ($node->getValue() === $node->getParent()->getValue() && !$node->hasNodes()) {
                    continue;
                } elseif ($node->getValue() !== $node->getParent()->getValue()) {
                    $path = ($node->getValue() < 0 ? '-' : '').$node->getPath();
                    if ($node->hasNodes()) {
                        $path .= '.*';
                    }
                    $flattened[$path] = true; // (sic!)
                }
                $this->flatten($node, $flattened);
            }
        } elseif ($hierarchy->getParent() && $hierarchy->getValue() !== $hierarchy->getParent()->getValue()) {
            $path             = ($hierarchy->getValue() < 0 ? '-' : '').$hierarchy->getPath();
            $flattened[$path] = true; // (sic!)
        }
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
     */
    private function deleteOld(ApiKey $key)
    {
        $qb = $this->em->getRepository(ApiKeyAction::class)->createQueryBuilder('aka');
        $qb->delete()
           ->andWhere('aka.key = :key')
           ->setParameters(['key' => $key]);
        $qb->getQuery()->execute();
    }
}

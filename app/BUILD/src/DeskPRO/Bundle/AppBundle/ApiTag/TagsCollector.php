<?php

namespace DeskPRO\Bundle\AppBundle\ApiTag;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use DeskPRO\Bundle\AppBundle\ApiTag\Model\HierarchyCreator;
use DeskPRO\Bundle\AppBundle\ApiTag\Model\Tag;
use DeskPRO\Bundle\AppBundle\Security\Authorization\ActionPermissionsHelper;
use DeskPRO\Bundle\AppBundle\Util\ApiControllersFinder;

/**
 * Class TagsCollector.
 */
class TagsCollector
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var ActionPermissionsHelper
     */
    protected $helper;

    /**
     * @var MetadataFactory
     */
    protected $factory;

    /**
     * @var ApiControllersFinder
     */
    protected $finder;

    /**
     * @param MetadataFactory      $factory
     * @param ApiControllersFinder $finder
     */
    public function __construct(
        MetadataFactory $factory,
        ApiControllersFinder $finder
    ) {
        $this->factory          = $factory;
        $this->finder           = $finder;
        $this->hierarchyCreator = new HierarchyCreator();
    }

    /**
     * @return HierarchyCreator
     */
    public function getHierarchyCreator()
    {
        return $this->hierarchyCreator;
    }

    public function resetHierarchy()
    {
        $this->hierarchyCreator = new HierarchyCreator();
    }

    /**
     * @param array|null $tags
     *
     * @return array
     */
    public function createTagsHierarchy($tags = null)
    {
        if ($tags === null) {
            $this->collectTags();
            $tags = $this->getTags();
        }

        foreach ($tags as $tagPath) {
            $tagPathReplaced = str_replace('-', '', $tagPath);
            $tags            = explode('.', $tagPathReplaced);
            $this->hierarchyCreator->processTags($tags, $tagPathReplaced);
        }

        return $this->hierarchyCreator->getHierarchy();
    }

    /**
     * @param bool|false $force_reload
     */
    public function collectTags($force_reload = false)
    {
        if ($force_reload || !$this->tags) {
            foreach ($this->finder->getClasses() as $class) {
                try {
                    $classMetadata = $this->factory->getMetadataForClass($class, $force_reload);
                    $metadata[]    = $classMetadata;
                    /** @var \Metadata\ClassMetadata $metadatum */
                    foreach ($classMetadata->methodMetadata as $metadatum) {
                        /* @var MethodMetadata $metadatum */
                        $this->tags = array_merge($this->tags, $metadatum->getTags());
                    }
                } catch (AbstractClassException $e) {
                    // There is nothing to do. Or just output it
                } catch (\ReflectionException $e) {
                    // TODO: we should dive into FQCN to know why it return directories as FQCN.
                }
            }

            $this->tags = array_unique($this->tags);
        }
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return array
     */
    public function getTagsHierarchy(array $tags = null)
    {
        $this->createTagsHierarchy($tags);

        return $this->hierarchyCreator->getHierarchy();
    }

    /**
     * @param $gatheredTags
     *
     * @return array
     */
    public function getTagsHierarchyForApi($gatheredTags)
    {
        $root = new Tag(0, '*');
        $root->replaceNodes($this->getTagsHierarchy());
        $this->populateByGathered($gatheredTags, $root);

        return [$root];
    }

    /**
     * @param $tags
     * @param $hierarchy
     *
     * @return mixed
     */
    public function populateByGathered($tags, $hierarchy)
    {
        foreach ($tags as $tagPath) {
            $current = $hierarchy;
            $allowed = strpos($tagPath, '-') === false;
            $tagPath = str_replace('-', '', $tagPath);
            $parts   = explode('.', $tagPath);

            $i = 0;

            while ($part = array_shift($parts)) {
                if ($part === '*') {
                    $this->processRecursive($current, $allowed);
                } elseif ($tag = $this->hierarchyCreator->findTag($part, $tagPath, $i)) {
                    $current = $tag;
                    if (!$parts) {
                        $current->setValue($allowed && $current->getValue() >= 0 ? 1 : -1);
                    }
                } else {
                    // no such tag
                    continue 2;
                }
                ++$i;
            }
        }

        return $hierarchy;
    }

    /**
     * @param \DeskPRO\Bundle\AppBundle\ApiTag\Model\Tag $hierarchy
     * @param bool                                       $allowed
     */
    protected function processRecursive($hierarchy, $allowed)
    {
        $hierarchy->setValue($allowed && $hierarchy->getValue() >= 0 ? 1 : -1);

        if ($hierarchy->hasNodes()) {
            foreach ($hierarchy->getNodes() as $child) {
                $this->processRecursive($child, $allowed);
            }
        }
    }
}

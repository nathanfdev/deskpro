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

    public function getHierarchyCreator()
    {
        return $this->hierarchyCreator;
    }

    /**
     * @return array
     */
    public function getTagsHierarchy()
    {
        $this->tags = array_unique($this->tags);

        foreach ($this->tags as $tagPath) {
            $tagPathReplaced = str_replace('-', '', $tagPath);
            $tags            = explode('.', $tagPathReplaced);
            $this->hierarchyCreator->processTags($tags, $tagPathReplaced);
        }

        $tagsHierarchy = $this->hierarchyCreator->getHierarchy();

        return $tagsHierarchy;
    }

    /**
     * @param bool|false $force_reload
     */
    public function collectTags($force_reload = false)
    {
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
    }

    /**
     * @param array $gatheredTags
     *
     * @return array
     */
    public function getTagsHierarchyForApi(array $gatheredTags)
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
    protected function populateByGathered($tags, $hierarchy)
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

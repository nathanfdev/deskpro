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

namespace DeskPRO\Bundle\AppBundle\ApiTag\Model;

/**
 * Class HierarchyCreator.
 */
class HierarchyCreator
{
    /**
     * @var Tag[]
     */
    private $tags = [];

    /**
     * @param array     $tags
     * @param string    $fullPath
     * @param int       $level
     * @param bool|null $permission
     *
     * @return Tag[]
     */
    public function processTags($tags, $fullPath, $level = 0, $permission = null)
    {
        $tagName = array_shift($tags);
        if ($tags) {
            $tag = $this->createTag($tagName, $fullPath, $level, $permission);
            $tag->setNodes(array_merge($tag->getNodes(), $this->processTags($tags, $fullPath, ++$level, $permission)));

            return [$tag];
        } else {
            return [$this->createTag($tagName, $fullPath, $level, $permission)];
        }
    }

    /**
     * @param string    $tagName
     * @param string    $fullPath
     * @param int       $level
     * @param bool|null $permission
     *
     * @return Tag
     */
    private function createTag($tagName, $fullPath, $level, $permission)
    {
        static $i;

        if (!$tag = $this->findTag($tagName, $fullPath, $level)) {
            ++$i;
            $tag = new Tag($i, $tagName);
            $tag->setValue($permission);
            $key              = $this->getKey($tagName, $fullPath, $level);
            $this->tags[$key] = $tag;

            return $this->tags[$key];
        }

        return $tag;
    }

    public function findTag($tagName, $fullPath, $level)
    {
        $key = $this->getKey($tagName, $fullPath, $level);
        if (isset($this->tags[$key])) {
            return $this->tags[$key];
        }

        return false;
    }

    private function getKey($tagName, $fullPath, $level)
    {
        $parts    = explode('.', $fullPath);
        $newParts = array_merge(array_slice($parts, 0, $level), [$tagName, $level]);
        $key      = implode('-', $newParts);

        return $key;
    }

    /**
     * @return Tag[]
     */
    public function getHierarchy()
    {
        return $this->array_values_recursive(
            array_filter(
                $this->tags,
                function ($item) {
                    /* @var Tag $item */
                    return !$item->hasParent();
                }
            )
        );
    }

    /**
     * @param Tag[] $tags
     *
     * @return array
     */
    private function array_values_recursive($tags)
    {
        foreach ($tags as $tag) {
            if ($tag->hasNodes()) {
                $tag->replaceNodes($this->array_values_recursive($tag->getNodes()));
            }
        }

        return array_values($tags);
    }
}

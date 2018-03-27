<?php

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

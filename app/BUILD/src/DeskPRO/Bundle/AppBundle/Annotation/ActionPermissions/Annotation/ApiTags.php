<?php

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation;

/**
 * Class ApiTags.
 *
 * @Annotation
 */
class ApiTags
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @param $tags
     */
    public function __construct($tags)
    {
        if (isset($tags['value']) && is_array($tags['value'])) {
            $tags = $tags['value'];
        }
        $this->tags = array_values($tags);
    }

    /**
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }
}

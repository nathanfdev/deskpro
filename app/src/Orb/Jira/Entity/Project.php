<?php

namespace Orb\Jira\Entity;

use Orb\Jira\Entity;

/**
 * Jira Project wrapper
 * Emulates a Jira Proect.
 *
 * @author Abhinav Kumar <work@abhinavkumar.in>
 */
class Project extends Entity
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $params = array())
    {
        if (!isset($params['id']) || !isset($params['name'])) {
            return false;
        }

        $this->_id	= $params['id'];

        $this->setName($params['name']);

        if (isset($params['description'])) {
            $this->setDescription($params['description']);
        }

        if (isset($params['key'])) {
            $this->setKey($params['key']);
        }

        if (isset($params['avatarUrls']) && count($params['avatarUrls'])) {
            foreach ($params['avatarUrls'] as $size => $url) {
                $this->_avatars[$size] = $url;
            }
        }
    }
}

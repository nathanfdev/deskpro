<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata;

use Metadata\MethodMetadata as BaseMethodMetadata;

class MethodMetadata extends BaseMethodMetadata
{
    protected $modes;

    protected $tags;

    public function setModes($modes)
    {
        $this->modes = $modes;

        return $this;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    public function getModes()
    {
        return $this->modes;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function serialize()
    {
        return serialize(array(
            $this->class,
            $this->name,
            $this->modes,
            $this->tags,
        ));
    }

    public function unserialize($str)
    {
        list($this->class, $this->name, $this->modes, $this->tags) = unserialize($str);
        $this->reflection                                          = new \ReflectionMethod($this->class, $this->name);
        $this->reflection->setAccessible(true);
    }
}

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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\SystemBundle\Entity\Storage;

use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class KeyValueEntry.
 *
 * @ORM\Entity
 * @ORM\Table(name="system_storage_key_value")
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 */
class KeyValueEntry
{
    use NotifyPropertyChangedTrait;

    /**
     * @var string
     * @ORM\Column(type="string", name="key_name", nullable=false)
     * @ORM\Id()
     */
    private $key;

    /**
     * @var \DateTime
     * @ORM\Column(type="text", nullable=false)
     */
    private $value;

    /**
     * KeyValueEntry constructor.
     *
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return \DateTime
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \DateTime $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}

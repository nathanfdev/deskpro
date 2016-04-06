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

namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\PHP;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\AbstractEvent;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class ErrorEvent.
 *
 * @ORM\Entity
 */
class ErrorEvent extends AbstractEvent
{
    /**
     * @var string
     *
     * @ORM\Column(name="error_type", type="string")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $message;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $file;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    protected $line;

    /**
     * @var string
     *
     * @ORM\Column(type="json_array")
     */
    protected $data;

    /**
     * @param string    $type
     * @param string    $message
     * @param string    $file
     * @param int       $line
     * @param \DateTime $dateCreated
     * @param array     $data
     */
    public function __construct($type, $message, $file, $line, \DateTime $dateCreated = null, $data = [])
    {
        $this->type    = $type;
        $this->message = $message;
        $this->file    = $file;
        $this->line    = $line;
        $this->data    = $data;
        parent::__construct($dateCreated);
    }

    /**
     * {@inheritdoc}
     */
    protected function generateSubjectUniqueId()
    {
        return $this->type.'-'.md5($this->message).'-'.md5($this->file).'-'.$this->line;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectDescription()
    {
        return "PHP error #{$this->type} \"{$this->message}\" in {$this->file} on line {$this->line}";
    }
}

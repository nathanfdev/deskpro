<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Model;

/**
 * Holds some property of a ticket (subject, department, custom field, etc).
 */
class TicketViewProperty
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var bool
     */
    private $alwaysVisible;

    /**
     * @var bool
     */
    private $linkify;

    /**
     * TicketViewProperty constructor.
     *
     * @param int    $id
     * @param string $label
     * @param string $value
     * @param bool   $alwaysVisible
     * @param bool   $linkify
     */
    public function __construct($id, $label, $value, $alwaysVisible = false, $linkify = false)
    {
        if (!$id) {
            throw new \InvalidArgumentException('a TicketViewProperty cannot be instantiated without an ID');
        }

        $this->id            = $id;
        $this->label         = $label;
        $this->value         = $value;
        $this->alwaysVisible = (bool) $alwaysVisible;
        $this->linkify       = (bool) $linkify;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->isAlwaysVisible() ? true : !$this->isEmpty();
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->getValue());
    }

    /**
     * @return bool
     */
    public function isAlwaysVisible()
    {
        return $this->alwaysVisible;
    }

    /**
     * @return bool
     */
    public function isLinkify()
    {
        return $this->linkify;
    }
}

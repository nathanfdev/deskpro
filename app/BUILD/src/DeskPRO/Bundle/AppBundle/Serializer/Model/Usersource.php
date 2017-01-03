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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use JMS\Serializer\Annotation as JMS;
use Orb\Util\Util;

/**
 * Class Usersource.
 */
class Usersource
{
    /**
     * The unique ID.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The title of this usersource.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * "user" or "agent" for now.
     *
     * the interface this usersource applies to
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $sourceType;

    /**
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $displayType;

    /**
     * The order in which to display this source in UserBundle.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $displayOrder;

    /**
     * @JMS\Type("array")
     *
     * @var array
     */
    protected $displayOptions;

    /**
     * True if this usersource is enabled/usable.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isEnabled;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\Usersource $usersource
     * @param string                                 $displayType
     * @param array                                  $displayOptions
     */
    public function __construct(\Application\DeskPRO\Entity\Usersource $usersource, $displayType, array $displayOptions)
    {
        $this->id             = $usersource->getId();
        $this->title          = $usersource->getTitle();
        $this->type           = $usersource->getType();
        $this->sourceType     = strtolower(Util::getBaseClassname($usersource->getSourceType()));
        $this->isEnabled      = $usersource->isEnabled();
        $this->displayOrder   = $usersource->getDisplayOrder();
        $this->displayType    = $displayType;
        $this->displayOptions = $displayOptions;
    }
}

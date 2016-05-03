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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model;

use DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ApiToken.
 */
class ApiToken
{
    /**
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\SerializedName("person_id")
     * @JMS\Groups("token")
     *
     * @var int
     */
    private $person;

    /**
     * @JMS\Type("string")
     * @JMS\Groups("token")
     *
     * @var string
     */
    private $token;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Settings\Model\DiscoverSettings")
     * @JMS\Groups("discover")
     *
     * @var DiscoverSettings
     */
    private $discover;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\ApiToken $apiToken
     * @param DiscoverSettings                     $discover
     */
    public function __construct(\Application\DeskPRO\Entity\ApiToken $apiToken, DiscoverSettings $discover)
    {
        $this->person   = $apiToken->getPerson();
        $this->token    = $apiToken->getKeyString();
        $this->discover = $discover;
    }
}

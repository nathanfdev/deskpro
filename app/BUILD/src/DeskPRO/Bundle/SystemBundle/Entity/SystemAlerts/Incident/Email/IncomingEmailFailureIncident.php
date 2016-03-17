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
namespace DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Email;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class IncomingEmailFailureIncident.
 *
 * @ORM\Entity
 */
class IncomingEmailFailureIncident extends Incident
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date_first_failure;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $date_last_failure;

    /**
     * {@inheritdoc}
     */
    public function getInstructions()
    {
        return 'Check the email stuff. Good luck!';
    }

    /**
     * @return \DateTime
     */
    public function getDateFirstFailure()
    {
        return $this->date_first_failure;
    }

    /**
     * @param \DateTime $date_first_failure
     */
    public function setDateFirstFailure(\DateTime $date_first_failure)
    {
        $this->date_first_failure = $date_first_failure;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastFailure()
    {
        return $this->date_last_failure;
    }

    /**
     * @param \DateTime $date_last_failure
     */
    public function setDateLastFailure(\DateTime $date_last_failure)
    {
        $this->date_last_failure = $date_last_failure;
    }
}

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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\CustomField\Context;

use Application\DeskPRO\Entity\Ticket;

class CustomFieldTicketContext extends CustomFieldContext
{
    /**
     * @var Ticket
     */
    private $ticket;

    public function __construct(Ticket $ticket)
    {
        parent::__construct($ticket, null);
        $this->ticket = $ticket;
    }

    /**
     * @param $owner_class
     *
     * @return Ticket
     */
    public function getOwner($owner_class)
    {
        return $this->ticket;
    }

    /**
     * @param $context_class
     *
     * @return \Application\DeskPRO\Entity\Organization|\Application\DeskPRO\Entity\Person|null
     */
    public function getContext($context_class)
    {
        switch ($context_class) {
            case 'Application\DeskPRO\Entity\Person':
                return $this->ticket->getPerson();
            case 'Application\DeskPRO\Entity\Organization':
                if ($org = $this->ticket->getOrganization()) {
                    return $org;
                }

                return;
        }

        return;
    }
}

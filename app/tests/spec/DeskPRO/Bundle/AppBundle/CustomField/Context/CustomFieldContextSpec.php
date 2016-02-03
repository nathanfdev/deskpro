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
namespace spec\DeskPRO\Bundle\AppBundle\CustomField\Context;

use PhpSpec\ObjectBehavior;

/**
 * @mixin \DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldContext
 */
class CustomFieldContextSpec extends ObjectBehavior
{
    public function it_takes_an_owner_and_a_context()
    {
        // you wont need to use this class (its very vague), but it exists in case the more specific
        // sub classes don't work for you. see the CustomFieldTicketContext.
        $this->beConstructedWith('arbitrary_owner', 'arbitrary_conext');
        $this->getOwner('Application\DeskPRO\Entity\Ticket')->shouldReturn('arbitrary_owner');
        $this->getContext('Application\DeskPRO\Entity\Person')->shouldReturn('arbitrary_conext');
        $this->getContext('Application\DeskPRO\Entity\Organization')->shouldReturn('arbitrary_conext');
    }
}

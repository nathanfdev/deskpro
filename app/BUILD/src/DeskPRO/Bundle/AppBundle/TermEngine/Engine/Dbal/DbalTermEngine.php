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

namespace DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\Dbal\TicketFilter\DbalTicketFilterEngine;
use DeskPRO\Bundle\AppBundle\TermEngine\Engine\TermEngineContext;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;

/**
 * Class DbalTermEngine.
 *
 * This class is a wrapper of DbalTicketFilterEngine allowing convenient interface for Term evaluation. This is rather
 * a fail of OO design, when this lower-level (accordingly to common sense) service uses higher-level
 * DbalTicketFilterEngine. The reason why it exists: can't compile a term not wrapping it in a TicketFilter using
 * existing services.
 *
 * @todo Fix it
 */
class DbalTermEngine
{
    /**
     * @var DbalTicketFilterEngine
     */
    private $ticketFilterEngine;

    /**
     * DbalTermEngine constructor.
     *
     * @param DbalTicketFilterEngine $ticketFilterEngine
     */
    public function __construct(DbalTicketFilterEngine $ticketFilterEngine)
    {
        $this->ticketFilterEngine = $ticketFilterEngine;
    }

    /**
     * @param TermInterface     $term
     * @param TermEngineContext $context
     *
     * @return Query\DbalExecutableQuery
     */
    public function evaluate(TermInterface $term, TermEngineContext $context)
    {
        $filter = new TicketFilter();

        // this is needed to bypass DbalTicketFilterEngine cache
        // @todo generate id from $term to enable caching
        $reflection = new \ReflectionProperty(TicketFilter::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($filter, uniqid());
        $filter->setTerm($term);

        return $this->ticketFilterEngine->evaluate($filter, $context);
    }
}

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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Component\Util\ListUtils;

class CustomFieldsTermsHandler implements TermsHandlerInterface
{
    /**
     * @var CustomField[]
     */
    private $customTicketFields = [];

    /**
     * @var CustomField[]
     */
    private $customPersonFields = [];

    /**
     * Terms constructor.
     *
     * @param CustomField[] $customTicketFields
     * @param CustomField[] $customPersonFields
     */
    public function __construct(array $customTicketFields = [], array $customPersonFields = [])
    {
        $this->customTicketFields = $customTicketFields;
        $this->customPersonFields = $customPersonFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return array_merge(
            ListUtils::map($this->customTicketFields, function ($f) {
                return sprintf(Terms::TICKET_CUSTOM, $f->field);
            }),
            ListUtils::map($this->customPersonFields, function ($f) {
                return sprintf(Terms::PERSON_CUSTOM, $f->field);
            })
        );
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch(array $term, TicketModel $ticketModel, AgentContext $agentContext)
    {
        return false;
    }
}

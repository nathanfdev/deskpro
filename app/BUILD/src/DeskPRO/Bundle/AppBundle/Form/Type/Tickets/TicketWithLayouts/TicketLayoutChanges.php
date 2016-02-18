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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

/**
 * Class TicketLayoutChanges.
 */
class TicketLayoutChanges
{
    /**
     * @var array
     */
    private $fields_requiring_rerender;

    /**
     * @var array
     */
    private $fields_to_remove;

    /**
     * @var array
     */
    private $additional_fields;

    /**
     * Constructor.
     *
     * @param array $fields_requiring_rerender
     * @param array $fields_to_remove
     * @param array $additional_fields
     */
    public function __construct(array $fields_requiring_rerender, array $fields_to_remove, array $additional_fields)
    {
        $this->fields_requiring_rerender = $fields_requiring_rerender;
        $this->fields_to_remove          = $fields_to_remove;
        $this->additional_fields         = $additional_fields;
    }

    /**
     * @return array
     */
    public function getFieldsRequiringRerender()
    {
        return $this->fields_requiring_rerender;
    }

    /**
     * @return array
     */
    public function getFieldsToRemove()
    {
        return $this->fields_to_remove;
    }

    /**
     * @return array
     */
    public function getAdditionalFields()
    {
        return $this->additional_fields;
    }
}

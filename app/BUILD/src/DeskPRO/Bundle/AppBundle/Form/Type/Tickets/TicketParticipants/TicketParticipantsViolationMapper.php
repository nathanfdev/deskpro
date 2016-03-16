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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use DeskPRO\Bundle\AppBundle\Form\FormFields;
use Symfony\Component\Form\FormEvent;

/**
 * We have ticket participant property but we need to split it to separate "followers" and "cc" collections.
 * So we need a custom violation mapper to handle such structure.
 */
class TicketParticipantsViolationMapper
{
    /**
     * @var string
     */
    private $followers_field_name;

    /**
     * @var
     */
    private $cc_field_name;

    /**
     * Constructor.
     *
     * @param string $followers_field_name
     * @param string $cc_field_name
     */
    public function __construct($followers_field_name = FormFields::FOLLOWERS, $cc_field_name = FormFields::CC)
    {
        $this->followers_field_name = $followers_field_name;
        $this->cc_field_name        = $cc_field_name;
    }

    /**
     * @param FormEvent $event
     */
    public function __invoke(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        foreach ($form->getErrors() as $error) {
        }
    }
}
